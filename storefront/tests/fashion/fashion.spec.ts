import { test, expect, type Page } from '@playwright/test';
import fs from 'node:fs/promises';
const local = 'http://127.0.0.1:3000', isolated = 'http://127.0.0.1:3140';
const widths = [360, 393, 430, 768, 1024, 1440, 1920];
const output = 'artifacts/fashion-redesign/after';
async function noOverflow(page: Page) { expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)).toBe(true); }
async function photograph(page: Page, name: string) {
  await page.evaluate(async () => { await document.fonts.ready; document.querySelectorAll<HTMLImageElement>('img').forEach(image => { image.loading = 'eager'; }); });
  await page.locator('img').evaluateAll(images => Promise.all(images.map(image => (image as HTMLImageElement).complete ? Promise.resolve() : new Promise(resolve => { image.addEventListener('load', resolve, { once: true }); image.addEventListener('error', resolve, { once: true }); }))));
  await page.screenshot({ path: `${output}/${name}.png`, fullPage: true });
}
test.beforeAll(async () => { await fs.mkdir(output, { recursive: true }); });

test('real catalog, seven responsive widths and public page screenshots', async ({ page, request }) => {
  // Read-only normal development catalog. All browser writes are rejected here.
  await page.route('**/api/**', route => ['GET', 'HEAD'].includes(route.request().method()) ? route.continue() : route.abort());
  const api = await request.get('http://localhost:8000/api/api-products');
  expect(api.ok()).toBe(true);
  const products = (await api.json()).data;
  expect(products.length).toBeGreaterThan(0);
  const product = products[0];
  const href = `/products/${product.product_name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}?${new URLSearchParams({ id: product.product_id })}`;
  const pages = [['home', '/'], ['catalog', '/products'], ['collections', '/categories'], ['category', `/categories/${encodeURIComponent(product.category)}`], ['product', href], ['login', '/login'], ['register', '/register']];
  const errors: string[] = []; page.on('pageerror', error => errors.push(error.message));
  const observations = [];
  for (const width of widths) {
    await page.setViewportSize({ width, height: 900 });
    for (const [name, path] of pages) {
      const response = await page.goto(local + path, { waitUntil: 'networkidle' });
      expect(response?.status()).toBe(200); await noOverflow(page);
      if (name === 'catalog') {
        await expect(page.locator('.product-card').filter({ hasText: product.product_name })).toHaveCount(1);
        await expect(page.locator('.product-card .price').first()).toContainText(new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(Number(product.offer_price ?? product[product.primary_price])));
      }
      if (name === 'product') {
        await expect(page.getByRole('button', { name: 'Select a size', exact: true })).toBeEnabled();
        await expect(page.locator('.size-choice button')).toHaveCount(1);
        await expect(page.getByRole('button', { name: product.size, exact: true })).toBeVisible();
      }
      await photograph(page, `${name}-${width}`);
      observations.push({ name, width, status: response?.status(), overflow: false });
    }
  }
  await fs.writeFile('artifacts/fashion-redesign/responsive-results.json', JSON.stringify({ observations, scriptErrors: errors }, null, 2));
  expect(errors).toEqual([]);
});

test('accessible navigation, real search, filter drawer, quick view and gallery', async ({ page }) => {
  await page.route('**/api/**', route => ['GET', 'HEAD'].includes(route.request().method()) ? route.continue() : route.abort());
  await page.goto(local + '/products', { waitUntil: 'networkidle' });
  const trigger = page.getByRole('button', { name: 'Open navigation' }); await trigger.click();
  const menu = page.getByRole('dialog', { name: 'Explore Vanthara' }); await expect(menu).toBeVisible();
  await menu.locator('summary').filter({ hasText: 'Clothing' }).click();
  await expect(menu.getByRole('link', { name: 'View all Clothing', exact: true })).toBeVisible();
  await page.keyboard.press('Escape'); await expect(menu).not.toBeVisible(); await expect(trigger).toBeFocused();
  await page.getByRole('button', { name: 'Open search' }).click();
  const search = page.getByRole('dialog', { name: 'Find your next piece' }).getByRole('combobox'); await expect(search).toBeFocused(); await search.fill('Premium');
  await expect(page.getByRole('option', { name: 'Premium Shirt' })).toBeVisible();
  await photograph(page, 'search-393'); await page.keyboard.press('Escape');
  await page.getByRole('button', { name: 'Filter', exact: true }).click();
  const filters = page.getByRole('dialog', { name: 'Refine your collection' });
  await filters.getByRole('combobox', { name: 'Size', exact: true }).selectOption('Medium');
  await filters.getByLabel('Minimum price').fill('1000'); await filters.getByLabel('Maximum price').fill('1200');
  await photograph(page, 'filters-393'); await filters.getByRole('button', { name: 'Apply filters' }).click();
  await expect(page).toHaveURL(/size=Medium/); await expect(page.locator('.product-card')).toHaveCount(1);
  await page.getByRole('combobox', { name: 'Sort by' }).selectOption('price-desc'); await expect(page).toHaveURL(/sort=price-desc/);
  await page.getByRole('button', { name: 'Quick view Premium Shirt' }).click();
  const quick = page.getByRole('dialog', { name: 'A closer look' });
  await expect(quick.getByRole('heading', { name: 'Premium Shirt' })).toBeVisible();
  await expect(quick.getByRole('button', { name: 'Select a size' })).toBeEnabled();
  await quick.getByRole('button', { name: 'Medium', exact: true }).click(); await expect(quick.getByRole('button', { name: 'Add to bag' })).toBeEnabled();
  await photograph(page, 'quick-view-393'); await page.keyboard.press('Escape');
  await page.getByRole('link', { name: 'View Premium Shirt', exact: true }).click();
  await expect(page.locator('.gallery-controls')).toContainText('1 / 2');
  await page.locator('.gallery-main').dispatchEvent('pointerdown', { pointerType: 'touch', clientX: 300, clientY: 300 });
  await page.locator('.gallery-main').dispatchEvent('pointerup', { pointerType: 'touch', clientX: 80, clientY: 305 });
  await expect(page.locator('.gallery-controls')).toContainText('2 / 2');
  await page.getByRole('button', { name: 'Enlarge Premium Shirt image' }).click();
  await expect(page.getByRole('dialog', { name: 'Premium Shirt', exact: true })).toBeVisible();
  await photograph(page, 'image-zoom-393'); await page.keyboard.press('ArrowLeft');
  await expect(page.locator('.image-viewer-controls')).toContainText('1 / 2'); await page.keyboard.press('Escape');
  await noOverflow(page);
  await page.setViewportSize({ width: 1440, height: 1000 }); await page.goto(local + '/');
  await page.getByRole('button', { name: 'Collections', exact: true }).click();
  await expect(page.locator('.fashion-mega').getByRole('link', { name: 'Shirt', exact: true })).toBeVisible();
  await photograph(page, 'mega-menu-1440'); await page.keyboard.press('Escape');
});

test('isolated sized product, saved state, cart count and responsive customer pages', async ({ page, request }) => {
  const identity = await request.get('http://127.0.0.1:8138/__storefront-test/identity'); expect((await identity.json()).environment).toBe('isolated-storefront-test');
  await page.route('**/api/**', route => new URL(route.request().url()).origin === isolated ? route.continue() : route.abort());
  await page.goto(isolated + '/login'); await page.getByLabel('Mobile number').fill('9000000011'); await page.getByLabel('Password', { exact: true }).fill('FixturePassword123!'); await page.getByRole('button', { name: 'Sign in', exact: true }).click(); await expect(page).toHaveURL(/\/account$/);
  await page.goto(isolated + '/cart');
  await expect(page.getByRole('heading', { name: 'In your bag', exact: true })).toBeVisible();
  const previousItem = page.locator('.bag-item').filter({ hasText: 'QA Fashion Shirt' });
  if (await previousItem.count()) { await previousItem.getByRole('button', { name: 'Remove item' }).click(); await expect(previousItem).toHaveCount(0); }
  await page.goto(isolated + '/products');
  const shirt = page.locator('.product-card').filter({ hasText: 'QA Fashion Shirt' }); await expect(shirt).toHaveCount(1);
  await shirt.getByRole('link', { name: 'View QA Fashion Shirt', exact: true }).click();
  await expect(page.getByRole('button', { name: 'Select a size' })).toBeEnabled();
  await page.getByRole('button', { name: 'Medium', exact: true }).click();
  await expect(page.locator('.purchase-buy-row .wishlist-toggle')).toBeEnabled();
  const saved = page.getByRole('button', { name: 'Remove QA Fashion Shirt from wishlist' });
  if (await saved.count()) { await saved.click(); await expect(page.getByRole('button', { name: 'Save QA Fashion Shirt to wishlist' })).toBeVisible(); }
  await page.getByRole('button', { name: 'Save QA Fashion Shirt to wishlist' }).click();
  await expect(page.getByRole('button', { name: 'Remove QA Fashion Shirt from wishlist' })).toHaveAttribute('aria-pressed', 'true');
  await page.getByRole('button', { name: 'Add to bag', exact: true }).click();
  await expect(page.locator('.purchase-message')).toContainText('Added to your bag');
  await expect(page.getByRole('link', { name: 'Your shopping bag, 1 items', exact: true })).toBeVisible();
  const errors: string[] = []; page.on('pageerror', e => errors.push(e.message));
  for (const width of widths) {
    await page.setViewportSize({ width, height: 900 });
    for (const path of ['/wishlist', '/cart', '/account', '/orders', '/orders/1', '/checkout', '/returns']) {
      await page.goto(isolated + path, { waitUntil: 'networkidle' }); await noOverflow(page);
      await photograph(page, `customer-${path.replaceAll('/', '-').slice(1)}-${width}`);
    }
  }
  await page.goto(isolated + '/wishlist'); await expect(page.locator('.saved-fashion-card').filter({ hasText: 'QA Fashion Shirt' })).toBeVisible(); await expect(page.getByRole('link', { name: 'Choose size' })).toBeVisible();
  await page.goto(isolated + '/cart'); const item = page.locator('.bag-item').filter({ hasText: 'QA Fashion Shirt' });
  await item.getByRole('spinbutton').fill('2'); await item.getByRole('button', { name: 'Update quantity' }).click(); await expect(item.locator('strong')).toContainText('2,400');
  await expect(page.getByRole('link', { name: 'Your shopping bag, 2 items', exact: true })).toBeVisible();
  await item.getByRole('button', { name: 'Remove item' }).click(); await expect(item).toHaveCount(0);
  await expect(page.getByRole('link', { name: 'Your shopping bag, 0 items', exact: true })).toBeVisible();
  expect(errors).toEqual([]);
});

test('wishlist cannot toggle until the existing saved state has been verified', async ({ page }) => {
  let release!: () => void;
  const ready = new Promise<void>(resolve => { release = resolve; });
  await page.route('**/api/customer/shopping-summary', async route => { await ready; await route.fulfill({ json: { signedIn: false, cartCount: 0, wishlist: [] } }); });
  await page.route('**/api/customer/wishlist', route => route.abort());
  await page.goto(local + '/products', { waitUntil: 'domcontentloaded' });
  const loading = page.getByRole('button', { name: 'Loading wishlist for Premium Shirt' });
  await expect(loading).toBeDisabled();
  release(); await expect(page.getByRole('button', { name: 'Save Premium Shirt to wishlist' })).toBeEnabled();
});
