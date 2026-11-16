import { expect, test } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const root = path.resolve(process.cwd(), '..');
const artifacts = path.resolve('artifacts/catalog-sync');
const secret = 'catalog-test-invalidation-secret-not-for-production';
function admin(action: string, notify = true) {
  return JSON.parse(execFileSync('php', ['tests/Storefront/catalog-fixture.php', action], {
    cwd: root, encoding: 'utf8', windowsHide: true,
    env: { ...process.env, STOREFRONT_TEST_RUN: process.env.STOREFRONT_CATALOG_TEST_RUN || 'catalog-20261116-a', STOREFRONT_CATALOG_REVALIDATE_URL: notify ? 'http://127.0.0.1:3150/api/catalog/revalidate' : '', STOREFRONT_CATALOG_REVALIDATE_SECRET: notify ? secret : '' },
  }).trim());
}
test.beforeAll(() => fs.mkdirSync(artifacts, { recursive: true }));

test('admin create, update, deactivate and new-category changes reach the real storefront', async ({ page, request }) => {
  const errors: string[] = [];
  page.on('pageerror', e => errors.push(e.message));
  page.on('console', e => { if (e.type() === 'error') errors.push(e.text()); });
  const identity = await (await request.get('http://127.0.0.1:8140/__storefront-test/identity')).json();
  expect(identity).toMatchObject({ environment: 'isolated-storefront-test', payments: 'disabled' });
  // Warm all catalog caches before modifying data through the actual Blade controllers.
  for (const url of ['/', '/products', '/categories', '/products?keyword=Admin', '/categories/Admin%20Catalog%20Category']) await page.goto(url);
  const created = admin('create');
  expect(created.products).toHaveLength(1);
  const api = await (await request.get('http://127.0.0.1:8140/api/api-products')).json();
  expect(api).toMatchObject({ status: 'success', data: [{ product_name: 'Admin Catalog Product', stock: 7 }] });
  expect(Number(api.data[0].retail)).toBe(1200);
  expect(typeof api.data[0].product_id).toBe('string');
  const ciphertext = api.data[0].product_id;
  await page.goto('/products');
  let card = page.locator('.product-card').filter({ has: page.getByRole('heading', { name: 'Admin Catalog Product', exact: true }) });
  await expect(card).toHaveCount(1);
  await expect(card).toContainText('1,200.00');
  await expect(card).toContainText('1,500.00');
  await expect(card).toContainText('20% less than MRP');
  const image = card.locator('img');
  await expect(image).toBeVisible();
  await expect.poll(() => image.evaluate((el: HTMLImageElement) => el.complete && el.naturalWidth > 0)).toBe(true);
  await page.screenshot({ path: path.join(artifacts, 'admin-created-shop.png'), fullPage: true });
  const cardHref = await card.getByRole('heading').getByRole('link').getAttribute('href');
  await card.getByRole('heading').getByRole('link').click();
  await expect(page.getByRole('heading', { level: 1, name: 'Admin Catalog Product' })).toBeVisible();
  expect(new URL(page.url()).searchParams.get('id')).toBe(new URL(cardHref!, page.url()).searchParams.get('id'));
  await page.screenshot({ path: path.join(artifacts, 'admin-created-detail.png'), fullPage: true });
  for (const url of ['/', '/categories/Admin%20Catalog%20Category', '/products?keyword=Admin']) {
    await page.goto(url); await expect(page.locator('.product-card h3').filter({ hasText: 'Admin Catalog Product' })).toHaveCount(1);
  }
  admin('feature');
  await page.goto('/');
  await expect(page.locator('.featured-section .product-card')).toHaveCount(1);
  admin('update');  await page.goto('/products');
  card = page.locator('.product-card');
  await expect(card.getByRole('heading')).toHaveText('Updated Catalog Product');
  await expect(card).toContainText('1,100.00');
  await expect(card).toContainText('Currently unavailable');
  expect((await (await request.get('/api/search?q=Updated')).json()).items?.[0]?.name).toBe('Updated Catalog Product');
  admin('toggle');
  for (const url of ['/', '/products', '/products?keyword=Updated', '/categories/Admin%20Catalog%20Category']) {
    await page.goto(url); await expect(page.locator('.product-card')).toHaveCount(0);
  }
  expect((await request.get(`http://127.0.0.1:8140/api/api-products/${encodeURIComponent(ciphertext)}`)).status()).toBe(404);
  admin('category'); admin('no-image');
  await page.goto('/categories');
  await expect(page.getByRole('heading', { name: 'New Catalog Category', exact: true })).toBeVisible();
  await page.goto('/categories/New%20Catalog%20Category');
  await expect(page.locator('.product-card')).toHaveCount(1);
  await expect(page.getByRole('img', { name: 'Image Free Product: image unavailable' })).toBeVisible();
  await expect(page.locator('.product-card')).toContainText('1,200.00');
  await page.setViewportSize({ width: 393, height: 852 });
  await page.screenshot({ path: path.join(artifacts, 'new-category-mobile.png'), fullPage: true });
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
  expect(errors).toEqual([]);
});

test('catalog invalidation requires its private bearer credential', async ({ request }) => {
  for (const authorization of ['', 'Bearer incorrect', `Bearer ${'é'.repeat(45)}`]) {
    const response = await request.post('/api/catalog/revalidate', { headers: authorization ? { Authorization: authorization } : {} });
    expect(response.status()).toBe(401);
  }
  const response = await request.post('/api/catalog/revalidate', { headers: { Authorization: `Bearer ${secret}` } });
  expect(response.status()).toBe(200);
  expect(await response.json()).toEqual({ revalidated: true });
});

test('timed revalidation recovers catalog edits when the admin notification is unavailable', async ({ page, request }) => {
  await request.post('/api/catalog/revalidate', { headers: { Authorization: `Bearer ${secret}` } });
  await page.goto('/products');
  await expect(page.locator('.product-card h3')).toHaveText(['Image Free Product']);
  admin('update', false);
  await expect.poll(async () => {
    await page.reload();
    return page.locator('.product-card h3').allTextContents();
  }, { timeout: 40_000, intervals: [2000] }).toEqual(['Updated Catalog Product']);
  await expect(page.locator('.product-card')).toHaveCount(1);
  await expect(page.locator('.product-card')).toContainText('1,100.00');
  await expect(page.locator('.product-card')).toContainText('Currently unavailable');
});
