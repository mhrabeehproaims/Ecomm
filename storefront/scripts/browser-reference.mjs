import { chromium } from '@playwright/test';
import fs from 'node:fs/promises';
await fs.mkdir('artifacts/visual-qa/reference', { recursive: true });
const browser = await chromium.launch();
try {
  const context = await browser.newContext();
  const blocked = [];
  await context.route('**/*', route => {
    if (!['GET', 'HEAD'].includes(route.request().method())) { blocked.push({ method: route.request().method(), url: new URL(route.request().url()).pathname }); return route.abort(); }
    return route.continue();
  });
  const page = await context.newPage();
  for (const width of [393, 1440]) {
    await page.setViewportSize({ width, height: 1000 });
    await page.goto('https://vanthara.com', { waitUntil: 'networkidle', timeout: 45_000 });
    await page.screenshot({ path: `artifacts/visual-qa/reference/vanthara-com-${width}.png`, fullPage: true });
  }
  console.log(JSON.stringify({ title: await page.title(), blockedMutations: blocked }));
} finally { await browser.close(); }
