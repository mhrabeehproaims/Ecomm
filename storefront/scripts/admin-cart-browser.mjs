import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import { chromium } from '@playwright/test';

// Only the explicitly isolated test router is accepted. Never automate a real admin login.
const origin = 'http://127.0.0.1:8142';
const browser = await chromium.launch();
try {
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  const identity = await page.request.get(origin + '/__storefront-test/identity');
  assert.equal((await identity.json()).environment, 'isolated-admin-cart-test');
  await page.route('**/*', route => new URL(route.request().url()).origin === origin ? route.continue() : route.abort());
  const errors = [];
  page.on('pageerror', error => errors.push(error.stack || error.message));
  await page.goto(origin + '/carts', { waitUntil: 'networkidle' });
  assert.equal(new URL(page.url()).pathname, '/');
  await page.locator('[name=username]').fill('admin-cart@example.test');
  await page.locator('[name=password]').fill('IsolatedCartBrowser123!');
  await page.locator('button[type=submit]').click();
  await page.waitForURL('**/carts');
  await page.waitForLoadState('networkidle');
  assert.equal(await page.locator('#customer-cart-table tbody tr[data-cart-id]').count(), 3);
  assert.match(await page.locator('#customer-cart-table').innerText(), /Fixture Customer/);
  assert.match(await page.locator('#customer-cart-table').innerText(), /Other Cart Customer/);
  assert.match(await page.locator('#customer-cart-table').innerText(), /250\.00/);
  assert.equal(await page.getByRole('link', { name: 'Cart Items', exact: true }).getAttribute('href'), origin + '/carts');
  await fs.mkdir('artifacts/admin-carts', { recursive: true });
  await page.screenshot({ path: 'artifacts/admin-carts/all-1440.png', fullPage: true });
  await page.locator('#cart-customer').selectOption('1', { force: true });
  await page.getByRole('button', { name: 'Filter', exact: true }).click();
  await page.waitForURL(url => url.pathname === '/carts' && url.searchParams.get('customer') === '1');
  assert.equal(await page.locator('#customer-cart-table tbody tr[data-cart-id]').count(), 2);
  await page.locator('#cart-product').selectOption('1', { force: true });
  await page.getByRole('button', { name: 'Filter', exact: true }).click();
  await page.waitForURL(url => url.searchParams.get('customer') === '1' && url.searchParams.get('product') === '1');
  assert.equal(await page.locator('#customer-cart-table tbody tr[data-cart-id]').count(), 1);
  assert.match(await page.locator('#customer-cart-table tbody').innerText(), /QA Cotton Bag/);
  await page.screenshot({ path: 'artifacts/admin-carts/filtered-1440.png', fullPage: true });
  await page.getByRole('link', { name: 'Clear filters' }).click();
  await page.waitForURL(origin + '/carts');
  assert.equal(await page.locator('#customer-cart-table tbody tr[data-cart-id]').count(), 3);
  await page.setViewportSize({ width: 393, height: 852 });
  await page.screenshot({ path: 'artifacts/admin-carts/all-393.png', fullPage: true });
  assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), true);
  await page.goto(origin + '/carts?customer=2&product=1', { waitUntil: 'networkidle' });
  assert.match(await page.locator('#customer-cart-table').innerText(), /No cart items found for these filters/);
  await page.screenshot({ path: 'artifacts/admin-carts/empty-393.png', fullPage: true });
  assert.deepEqual(errors, []);
  await fs.writeFile('artifacts/admin-carts/browser-results.json', JSON.stringify({ passed: true, checks: ['anonymous redirect', 'real isolated admin login', 'cart rows and saved prices', 'customer and combined filters', 'clear filters', 'empty state', '393px overflow', 'no script exceptions'], scriptErrors: errors }, null, 2));
  console.log('PASS: isolated admin login, cart rows, customer/product filters, clear/empty states and responsive layout.');
} finally {
  await browser.close();
}
