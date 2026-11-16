import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import { chromium, expect } from '@playwright/test';

const browser = await chromium.launch();
const origin = 'http://127.0.0.1:3000';
const directory = 'artifacts/product-empty-details';
try {
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, reducedMotion: 'reduce' });
  await page.route('**/api/**', route => ['GET', 'HEAD'].includes(route.request().method()) ? route.continue() : route.abort());
  const response = await page.request.get('http://localhost:8000/api/api-products');
  assert.equal(response.status(), 200);
  const catalog = (await response.json()).data;
  const empty = catalog.find(item => Number(item.stock) === 0 && !item.size?.trim());
  const complete = catalog.find(item => Number(item.stock) > 0 && item.size?.trim() && item.category === empty?.category);
  assert.ok(empty && complete, 'Real products with missing/available size details are required.');
  const href = item => '/products/' + item.product_name.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '?' + new URLSearchParams({ id: item.product_id });
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await fs.mkdir(directory, { recursive: true });
  const results = [];
  for (const width of [360, 393, 430, 768, 1024, 1440, 1920]) {
    await page.setViewportSize({ width, height: 1000 });
    for (const item of [empty, complete]) {
      await page.goto(origin + href(item), { waitUntil: 'networkidle' });
      await expect(page.getByRole('heading', { level: 1, name: item.product_name, exact: true })).toBeVisible();
      const metrics = await page.evaluate(() => {
        const title = document.querySelector('.product-heading').getBoundingClientRect();
        const info = document.querySelector('.product-information').getBoundingClientRect();
        return { gap: info.top - title.bottom, overflow: document.documentElement.scrollWidth > innerWidth + 1 };
      });
      assert.equal(metrics.overflow, false);
      if (width >= 768) assert.ok(metrics.gap >= 0 && metrics.gap <= 32, `Stretched heading: ${item.product_name} at ${width}px (${metrics.gap}px)`);
      if (item === empty) {
        const support = page.getByRole('complementary', { name: 'Product help and alternatives' });
        await expect(support).toBeVisible();
        await expect(support.getByRole('link', { name: /Ask us about sizing/ })).toHaveAttribute('href', '/contact');
        await expect(support.getByRole('link', { name: new RegExp(complete.product_name) })).toHaveCount(1);
        await expect(page.getByRole('spinbutton', { name: 'Product quantity' })).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Unavailable', exact: true })).toBeDisabled();
        assert.equal(await support.locator('.available-alternative').first().evaluate(node => new URL(node.href).searchParams.has('id')), true);
      } else {
        await expect(page.locator('.product-support')).toHaveCount(0);
        await expect(page.getByRole('spinbutton', { name: 'Product quantity' })).toBeVisible();
        await page.getByRole('button', { name: 'Select a size', exact: true }).click();
        await expect(page.locator('.size-choice p')).toHaveText('Choose a size to continue.');
        await page.locator('.size-options button').click();
        await expect(page.getByRole('button', { name: 'Add to bag', exact: true })).toBeEnabled();
      }
      await page.locator('.product-information').screenshot({ path: `${directory}/${item === empty ? 'missing' : 'complete'}-${width}.png`, style: '.site-header,.skip-link,.mobile-nav{visibility:hidden!important}' });
      results.push({ product: item.product_name, width, ...metrics });
    }
  }
  await page.goto(origin + href(empty), { waitUntil: 'networkidle' });
  await page.locator('.available-alternative').first().click();
  await expect(page.getByRole('heading', { name: complete.product_name, exact: true })).toBeVisible();
  assert.deepEqual(errors, []);
  await fs.writeFile(`${directory}/results.json`, JSON.stringify({ results, alternativeNavigation: true, scriptErrors: errors }, null, 2));
  console.log('PASS: compact heading, authentic alternatives, size selection and no overflow at all seven requested widths.');
} finally { await browser.close(); }
