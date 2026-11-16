import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import { chromium } from '@playwright/test';
const origin = 'http://127.0.0.1:3140';
const log = '../storage/framework/testing/vanthara-checkout-admin-cart-browser-20261116a/http-requests.ndjson';
const phase = process.argv[2] || 'before';
assert.ok(['before', 'after'].includes(phase));
const browser = await chromium.launch();
try {
  const page = await browser.newPage({ viewport: { width: 393, height: 852 } });
  const identity = await page.request.get('http://127.0.0.1:8138/__storefront-test/identity');
  assert.equal((await identity.json()).environment, 'isolated-storefront-test');
  await page.route('**/*', route => ['http://127.0.0.1:3140', 'http://127.0.0.1:8138'].includes(new URL(route.request().url()).origin) ? route.continue() : route.abort());
  const signedIn = await page.request.post(origin + '/api/customer/login', { headers: { Origin: origin }, data: { mobile: '9000000011', password: 'FixturePassword123!' } });
  assert.equal(signedIn.status(), 200);
  const list = await (await page.request.get('http://127.0.0.1:8138/api/api-products')).json();
  const product = list.data.find(p => p.product_name === 'QA Cotton Bag');
  const wishlist = await (await page.request.get(origin + '/api/customer/wishlist')).json();
  if (!wishlist.products.some(p => p.product_name === product.product_name)) assert.equal((await page.request.post(origin + '/api/customer/wishlist', { headers: { Origin: origin }, data: { product_id: product.product_id } })).status(), 200);
  const samples = [], errors = [];
  page.on('pageerror', error => errors.push(error.message));
  let paths = [];
  page.on('request', request => { if (request.url().startsWith(origin + '/api/customer/')) paths.push(new URL(request.url()).pathname); });
  for (let sample = 0; sample < 3; sample++) for (const kind of ['cart', 'wishlist']) {
    await fs.writeFile(log, ''); paths = [];
    const start = performance.now();
    await page.goto(origin + '/' + kind, { waitUntil: 'domcontentloaded' });
    await page.locator(kind === 'cart' ? '.bag-item' : '.saved-fashion-card').first().waitFor();
    const contentReadyMs = performance.now() - start;
    await page.waitForLoadState('networkidle');
    const requests = (await fs.readFile(log, 'utf8')).trim().split('\n').filter(Boolean).map(line => JSON.parse(line)).filter(row => ['/api/show-customer', '/api/cart', '/api/wishlist', '/api/shopping-state'].includes(row.path));
    samples.push({ sample, kind, contentReadyMs: Math.round(contentReadyMs), browserReads: [...paths], laravelReads: requests });
  }
  assert.deepEqual(errors, []);
  await fs.mkdir('artifacts/shopping-load', { recursive: true });
  await fs.writeFile(`artifacts/shopping-load/${phase}.json`, JSON.stringify({ context: 'Isolated local production build, existing sample database, no live payments', samples }, null, 2));
  console.log(JSON.stringify(samples.map(s => ({ kind: s.kind, ms: s.contentReadyMs, browserReads: s.browserReads.length, laravelReads: s.laravelReads.length })), null, 2));
} finally { await browser.close(); }
