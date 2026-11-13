import { chromium } from '@playwright/test';
import fs from 'node:fs/promises';
const directory = 'artifacts/visual-qa/before';
await fs.mkdir(directory, { recursive: true });
const browser = await chromium.launch();
try {
  const page = await browser.newPage();
  const observations = [];
  for (const width of [360, 393, 430, 768, 1024, 1440, 1920]) {
    await page.setViewportSize({ width, height: 1000 });
    await page.goto('http://127.0.0.1:3001', { waitUntil: 'networkidle' });
    await page.screenshot({ path: `${directory}/home-${width}.png`, fullPage: true });
    observations.push(await page.evaluate(() => ({
      width: innerWidth,
      documentWidth: document.documentElement.scrollWidth,
      headerPosition: getComputedStyle(document.querySelector('.site-header')).position,
      mobileNav: getComputedStyle(document.querySelector('.mobile-nav')).display,
      mobileLabelSize: getComputedStyle(document.querySelector('.mobile-nav a')).fontSize,
      searchSize: getComputedStyle(document.querySelector('#site-search')).fontSize,
      minVisibleFont: Math.min(...[...document.querySelectorAll('p, a, span, label, input')].filter(e => e.getBoundingClientRect().width && e.getBoundingClientRect().height).map(e => parseFloat(getComputedStyle(e).fontSize))),
    })));
  }
  await page.setViewportSize({ width: 393, height: 852 });
  await page.goto('http://127.0.0.1:3001/products', { waitUntil: 'networkidle' });
  await page.screenshot({ path: `${directory}/catalog-393.png`, fullPage: true });
  await fs.writeFile(`${directory}/observations.json`, JSON.stringify(observations, null, 2));
  console.log(JSON.stringify(observations));
} finally { await browser.close(); }
