import { test, expect } from '@playwright/test';
import fs from 'node:fs/promises';

for (const width of [360, 393, 1440]) {
  test(`select-size action guides the customer before adding at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 900 });
    const writes: unknown[] = [];
    let release!: () => void;
    const pending = new Promise<void>(resolve => { release = resolve; });
    // Read real product data, but intercept every write to the normal application.
    await page.route('**/api/**', async route => {
      if (['GET', 'HEAD'].includes(route.request().method())) return route.continue();
      if (new URL(route.request().url()).pathname !== '/api/customer/cart-add') return route.abort();
      writes.push(route.request().postDataJSON());
      await pending;
      return route.fulfill({ json: { message: 'Controlled browser test: no cart data was changed.' } });
    });
    await page.goto('http://127.0.0.1:3000/products', { waitUntil: 'networkidle' });
    await page.getByRole('link', { name: 'View Premium Shirt', exact: true }).click();
    const choose = page.getByRole('button', { name: 'Select a size', exact: true });
    await expect(choose).toBeEnabled();
    await expect(choose).toHaveCSS('cursor', 'pointer');
    await choose.click();
    const medium = page.getByRole('button', { name: 'Medium', exact: true });
    await expect(medium).toBeFocused();
    await expect(medium).toHaveAttribute('aria-pressed', 'false');
    await expect(page.getByRole('status').filter({ hasText: 'Choose a size to continue.' })).toBeVisible();
    expect(writes).toEqual([]);
    await medium.click();
    await expect(medium).toHaveAttribute('aria-pressed', 'true');
    const add = page.getByRole('button', { name: 'Add to bag', exact: true });
    await expect(add).toBeEnabled();
    await add.click();
    await expect.poll(() => writes.length).toBe(1);
    const adding = page.getByRole('button', { name: 'Adding…', exact: true });
    await expect(adding).toBeDisabled();
    await expect(adding).toHaveCSS('cursor', 'wait');
    release();
    await expect(page.locator('.purchase-message')).toContainText('no cart data was changed');
    await fs.mkdir('artifacts/size-selection', { recursive: true });
    await page.screenshot({ path: `artifacts/size-selection/selected-${width}.png` });
  });
}

test('quick view Select a size focuses its own size selector', async ({ page }) => {
  await page.route('**/api/**', route => ['GET', 'HEAD'].includes(route.request().method()) ? route.continue() : route.abort());
  await page.goto('http://127.0.0.1:3000/products', { waitUntil: 'networkidle' });
  await page.getByRole('button', { name: 'Quick view Premium Shirt' }).click();
  const quick = page.getByRole('dialog', { name: 'A closer look' });
  await quick.getByRole('button', { name: 'Select a size' }).click();
  await expect(quick.getByRole('button', { name: 'Medium', exact: true })).toBeFocused();
  await quick.getByRole('button', { name: 'Medium', exact: true }).click();
  await expect(quick.getByRole('button', { name: 'Add to bag' })).toBeEnabled();
});
