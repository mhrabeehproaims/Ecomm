import { chromium } from '@playwright/test';
import fs from 'node:fs/promises';
const origin = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:3001';
const browser = await chromium.launch();
try {
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, reducedMotion: 'reduce' });
  await page.addInitScript(() => {
    window.__qa = { cls: 0, lcp: 0 };
    new PerformanceObserver(list => { for (const e of list.getEntries()) if (!e.hadRecentInput) window.__qa.cls += e.value; }).observe({ type: 'layout-shift', buffered: true });
    new PerformanceObserver(list => { for (const e of list.getEntries()) window.__qa.lcp = e.startTime; }).observe({ type: 'largest-contentful-paint', buffered: true });
  });
  const results = [];
  for (const path of ['/', '/products', '/cart', '/account', '/returns']) {
    await page.goto(`${origin}${path}`, { waitUntil: 'networkidle' });
    await page.locator('main h1').waitFor();
    results.push(await page.evaluate(path => {
      const parse = value => { const n = value.match(/[\d.]+/g)?.map(Number) || [255, 255, 255]; return [...n.slice(0, 3), n[3] ?? 1]; };
      const blend = (a, b) => a.slice(0, 3).map((v, i) => v * a[3] + b[i] * (1 - a[3]));
      const luminance = color => color.map(c => { c /= 255; return c <= .04045 ? c / 12.92 : ((c + .055) / 1.055) ** 2.4; }).reduce((a, v, i) => a + v * [.2126, .7152, .0722][i], 0);
      const failures = [];
      let checked = 0;
      for (const element of document.querySelectorAll('body *')) {
        if (!element.childNodes.length || ![...element.childNodes].some(n => n.nodeType === 3 && n.textContent.trim())) continue;
        if (element.closest('[aria-hidden=true],.sr-only,script,style,svg')) continue;
        const rect = element.getBoundingClientRect(), style = getComputedStyle(element);
        if (rect.width < 2 || rect.height < 2 || style.visibility !== 'visible') continue;
        const chain = []; for (let e = element; e; e = e.parentElement) chain.unshift(e);
        let background = [255, 255, 255];
        for (const e of chain) background = blend(parse(getComputedStyle(e).backgroundColor), background);
        const foreground = blend(parse(style.color), background);
        const a = luminance(foreground), b = luminance(background);
        const ratio = (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
        const large = parseFloat(style.fontSize) >= 24 || (parseFloat(style.fontSize) >= 18.66 && parseInt(style.fontWeight) >= 700);
        checked++;
        if (ratio < (large ? 3 : 4.5)) failures.push({ text: element.textContent.trim().slice(0, 70), ratio: +ratio.toFixed(2), foreground: style.color, background });
      }
      const nav = performance.getEntriesByType('navigation')[0];
      const scripts = performance.getEntriesByType('resource').filter(r => r.initiatorType === 'script');
      return { path, contrastSamples: checked, contrastFailures: failures, localTiming: { ttfbMs: Math.round(nav.responseStart), domContentLoadedMs: Math.round(nav.domContentLoadedEventEnd), lcpMs: Math.round(window.__qa.lcp), cls: +window.__qa.cls.toFixed(4) }, scripts: { requests: scripts.length, encodedBytes: scripts.reduce((sum, r) => sum + r.encodedBodySize, 0) }, title: document.title, canonical: document.querySelector('link[rel=canonical]')?.href, noindex: document.querySelector('meta[name=robots]')?.content.includes('noindex') };
    }, path));
  }
  await fs.writeFile('artifacts/visual-qa/accessibility-performance.json', JSON.stringify({ scope: 'Local, unthrottled Chromium samples; not a Lighthouse, WCAG certification or field Core Web Vitals result. Contrast uses rendered CSS backgrounds; photo content is not sampled.', results }, null, 2));
  console.log(JSON.stringify(results));
} finally { await browser.close(); }
