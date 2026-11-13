import { test, expect, type Page } from '@playwright/test';
import fs from 'node:fs/promises';

const directory = 'artifacts/visual-qa/after';
const widths = [360, 393, 430, 768, 1024, 1440, 1920];

async function ready(page: Page) {
  await page.locator('main h1').waitFor();
  await page.waitForLoadState('networkidle');
  await page.evaluate(async () => { await document.fonts.ready; });
}

async function noOverflow(page: Page) {
  const measurement = await page.evaluate(() => ({ width: innerWidth, document: document.documentElement.scrollWidth }));
  expect(measurement.document).toBeLessThanOrEqual(measurement.width + 1);
}

test.beforeEach(async ({ page }) => {
  await fs.mkdir(directory, { recursive: true });
  await page.route('**/api/**', route => ['GET', 'HEAD'].includes(route.request().method()) ? route.continue() : route.abort('blockedbyclient'));
});

for (const width of widths) {
  test(`responsive pages at ${width}px with real catalog data`, async ({ page }, testInfo) => {
    await page.setViewportSize({ width, height: width < 768 ? 852 : 1000 });
    const errors: string[] = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('console', message => { if (message.type() === 'error') errors.push(message.text()); });
    await page.goto('/products');
    await ready(page);
    expect(await page.locator('.product-card').count(), 'A populated read-only catalog is required for this suite; no fixture products are inserted').toBeGreaterThan(0);
    const productHref = await page.locator('.product-picture').first().getAttribute('href');
    expect(productHref).toBeTruthy();
    const pages = [
      ['home', '/'], ['catalog', '/products'], ['categories', '/categories'],
      ['product', productHref!], ['cart', '/cart'], ['account', '/account'],
      ['wishlist', '/wishlist'], ['returns', '/returns'],
    ];
    const metrics = [];
    for (const [name, path] of pages) {
      await page.goto(path);
      await ready(page);
      await noOverflow(page);
      await expect(page.locator('main h1')).toHaveCount(1);
      const images = page.locator('main img');
      // Load below-fold images before the full-page evidence capture.
      await images.evaluateAll(async nodes => { for (const node of nodes) { const img = node as HTMLImageElement; img.loading = 'eager'; await img.decode().catch(() => {}); } });
      await page.screenshot({ path: `${directory}/${name}-${width}.png`, fullPage: true, animations: 'disabled' });
      const measurement = await page.evaluate(() => ({
        width: innerWidth, documentWidth: document.documentElement.scrollWidth,
        headerPosition: getComputedStyle(document.querySelector('.site-header')!).position,
        h1Font: getComputedStyle(document.querySelector('h1')!).fontSize,
        searchFont: getComputedStyle(document.querySelector('#site-search')!).fontSize,
        brokenImages: [...document.querySelectorAll<HTMLImageElement>('main img')].filter(img => img.complete && !img.naturalWidth).map(img => img.currentSrc),
        navigation: getComputedStyle(document.querySelector('.mobile-nav')!).display,
        cardRows: (() => {
          const rows = new Map<number, { height: number; price: number }[]>();
          for (const card of document.querySelectorAll('.product-grid .product-card')) {
            const box = card.getBoundingClientRect();
            const key = Math.round(box.top);
            const row = rows.get(key) || [];
            row.push({ height: box.height, price: card.querySelector('.product-bottom')!.getBoundingClientRect().top });
            rows.set(key, row);
          }
          return [...rows.values()].map(row => ({ heightSpread: Math.max(...row.map(card => card.height)) - Math.min(...row.map(card => card.height)), priceSpread: Math.max(...row.map(card => card.price)) - Math.min(...row.map(card => card.price)) }));
        })(),
      }));
      metrics.push({ page: name, ...measurement });
      expect(measurement.brokenImages).toEqual([]);
      for (const row of measurement.cardRows) {
        expect(row.heightSpread, `${name}: card heights in one row`).toBeLessThanOrEqual(1);
        expect(row.priceSpread, `${name}: price alignment in one row`).toBeLessThanOrEqual(1);
      }
      const mobileNav = page.getByRole('navigation', { name: 'Mobile shopping navigation' });
      if (width < 768) {
        await expect(mobileNav).toBeVisible();
        for (const link of await mobileNav.getByRole('link').all()) {
          const box = await link.boundingBox();
          expect(box!.height).toBeGreaterThanOrEqual(44);
          expect(box!.width).toBeGreaterThanOrEqual(44);
        }
      } else await expect(mobileNav).toBeHidden();
      await page.evaluate(() => window.scrollTo(0, 500));
      expect((await page.locator('.site-header').boundingBox())!.y).toBeCloseTo(0, 0);
      if (width < 768) {
        await page.evaluate(() => window.scrollTo(0, document.documentElement.scrollHeight));
        const footer = (await page.locator('.footer-bottom').boundingBox())!;
        const navigation = (await mobileNav.boundingBox())!;
        expect(footer.y + footer.height, 'Footer stays above the fixed mobile navigation').toBeLessThanOrEqual(navigation.y + 1);
      }
      await page.evaluate(() => window.scrollTo(0, 0));
    }
    await fs.writeFile(`${directory}/metrics-${width}.json`, JSON.stringify(metrics, null, 2));
    await testInfo.attach('viewport metrics', { body: JSON.stringify(metrics), contentType: 'application/json' });
    expect(errors).toEqual([]);
  });
}

test('browse category, search by keyboard and inspect actual product gallery', async ({ page }) => {
  await page.setViewportSize({ width: 393, height: 852 });
  await page.goto('/'); await ready(page);
  await page.locator('.category-card').first().click();
  await expect(page).toHaveURL(/\/categories\//);
  await expect(page.locator('.product-card').first()).toBeVisible();
  const name = (await page.locator('.product-card h3').first().innerText()).trim();
  const query = name.split(' ').slice(0, 2).join(' ');
  const search = page.getByRole('combobox', { name: 'Search the Vanthara catalog' });
  await search.fill(query);
  await expect(page.getByRole('option').first()).toBeVisible();
  await search.press('ArrowDown');
  await expect(page.getByRole('option').first()).toHaveAttribute('aria-selected', 'true');
  await page.screenshot({ path: `${directory}/search-suggestions-393.png`, animations: 'disabled' });
  await search.press('Escape');
  await expect(search).toHaveAttribute('aria-expanded', 'false');
  await search.press('ArrowDown');
  await search.press('Enter');
  await expect(page).toHaveURL(/\/products\/[^?]+\?id=/);
  await expect(page.locator('main h1')).not.toHaveText('This discovery isn’t here.');
  const thumbnails = page.locator('.gallery-thumbnails button');
  if (await thumbnails.count() > 1) {
    await thumbnails.nth(1).click();
    await expect(thumbnails.nth(1)).toHaveAttribute('aria-pressed', 'true');
    await expect(page.locator('.gallery-main img')).toHaveAttribute('alt', /view 2 of/);
    await page.getByRole('button', { name: 'Previous product image' }).click();
    await expect(thumbnails.first()).toHaveAttribute('aria-pressed', 'true');
  }
  await page.getByRole('navigation', { name: 'Mobile shopping navigation' }).getByRole('link', { name: 'Bag', exact: true }).click();
  await expect(page).toHaveURL(/\/login\?next=/);
  await expect(page.getByRole('heading', { name: 'Welcome back.', exact: true })).toBeVisible();
});

test('filters update actual results and search failures remain recoverable', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto('/products'); await ready(page);
  await page.locator('.filter-disclosure > summary').click();
  await page.getByLabel('Maximum price (₹)').fill('0');
  await page.getByRole('button', { name: 'Apply filters' }).click();
  await expect(page).toHaveURL(/max=0/);
  await expect(page.locator('.product-card')).toHaveCount(0);
  await page.getByRole('link', { name: 'Clear all', exact: true }).click();
  await expect(page.locator('.product-card').first()).toBeVisible();
  await page.route('**/api/search?*', route => route.fulfill({ status: 503, contentType: 'application/json', body: JSON.stringify({ message: 'Controlled QA outage' }) }));
  const search = page.getByRole('combobox', { name: 'Search the Vanthara catalog' });
  await search.fill('unavailable-check');
  await expect(page.getByText('Suggestions are unavailable. You can still submit your search.')).toBeVisible();
  await page.screenshot({ path: `${directory}/controlled-search-error-1440.png`, animations: 'disabled' });
  await search.press('Enter');
  await expect(page).toHaveURL(/keyword=unavailable-check/);
  await expect(page.locator('main h1')).toContainText('unavailable-check');
});

test('keyboard skip link, named icon shortcuts and mobile route indicators', async ({ page }) => {
  await page.setViewportSize({ width: 393, height: 852 });
  await page.goto('/'); await ready(page);
  await page.keyboard.press('Tab');
  await expect(page.getByRole('link', { name: 'Skip to content' })).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(page.locator('#main-content')).toBeFocused();
  await expect(page.getByRole('navigation', { name: 'Shopping shortcuts' }).getByRole('link', { name: 'Your shopping bag' })).toBeVisible();
  await page.locator('.mobile-nav').getByRole('link', { name: 'Account', exact: true }).click();
  await expect(page).toHaveURL(/\/login\?next=/);
  await expect(page.getByRole('heading', { name: 'Welcome back.', exact: true })).toBeVisible();
});

for (const width of [393, 1440]) {
  test(`information pages at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 1000 });
    for (const name of ['about', 'contact', 'wholesale', 'faq', 'shipping', 'privacy', 'terms']) {
      await page.goto(`/${name}`); await ready(page); await noOverflow(page);
      await expect(page.locator('main h1')).toHaveCount(1);
      await page.screenshot({ path: `${directory}/${name}-${width}.png`, fullPage: true, animations: 'disabled' });
    }
  });
}
