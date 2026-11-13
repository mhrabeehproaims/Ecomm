import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import { chromium } from '@playwright/test';

const origin = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:3000';
const outage = process.argv.includes('--outage');
const directory = 'artifacts/visual-qa/local';
await fs.mkdir(directory, { recursive: true });
const browser = await chromium.launch();
try {
  const page = await browser.newPage({ viewport: { width: 393, height: 852 }, reducedMotion: 'reduce' });
  if (outage) {
    const term = `outage-verification-${Date.now()}`;
    const response = await fetch(`${origin}/api/search?q=${term}`);
    assert.equal(response.status, 503);
    await page.goto(`${origin}/products?keyword=${term}`, { waitUntil: 'networkidle' });
    assert.ok(await page.getByText('The collection is taking a moment.').isVisible());
    await page.screenshot({ path: `${directory}/catalog-api-unavailable-393.png`, fullPage: true });
    await fs.writeFile(`${directory}/outage.json`, JSON.stringify({ searchStatus: response.status, catalogUnavailableVisible: true, noBackendDetails: !(await page.locator('body').innerText()).includes('SQLSTATE') }, null, 2));
    console.log('PASS actual local API outage: search 503 and visible catalog unavailable state');
  } else {
    const results = [];
    for (const path of ['cart', 'wishlist', 'purchase/history', 'show-customer', 'delivery-address']) {
      const response = await fetch(`http://127.0.0.1:8137/api/${path}`, { headers: { Accept: 'application/json' } });
      assert.equal(response.status, 401);
      results.push({ path: `/api/${path}`, status: response.status });
    }
    for (const [name, path, expected] of [
      ['home-empty', '/', 'Good things are on their way.'],
      ['catalog-empty', '/products', 'Good things are on their way.'],
      ['categories-empty', '/categories', 'Our categories are coming together.'],
      ['returns-empty', '/returns', 'No return policies have been published yet.'],
      ['missing-product', '/products/unknown', 'This discovery isn’t here.'],
    ]) {
      await page.goto(`${origin}${path}`, { waitUntil: 'networkidle' });
      assert.ok(await page.getByText(expected, { exact: true }).isVisible());
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
      await page.screenshot({ path: `${directory}/${name}-393.png`, fullPage: true });
      results.push({ path, expectedStateVisible: true });
    }
    await fs.writeFile(`${directory}/checks.json`, JSON.stringify(results, null, 2));
    console.log('PASS 5 protected GETs reject anonymous access and 5 real local browser states render correctly');
  }
} finally { await browser.close(); }
