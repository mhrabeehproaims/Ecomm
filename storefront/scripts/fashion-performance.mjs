import fs from 'node:fs/promises';
import { chromium } from '@playwright/test';
const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, reducedMotion: 'reduce' });
await page.addInitScript(() => {
  window.__fashionMetrics = { cls: 0, lcp: 0 };
  new PerformanceObserver(list => { for (const entry of list.getEntries()) if (!entry.hadRecentInput) window.__fashionMetrics.cls += entry.value; }).observe({type:'layout-shift', buffered:true});
  new PerformanceObserver(list => { for (const entry of list.getEntries()) window.__fashionMetrics.lcp = entry.startTime; }).observe({type:'largest-contentful-paint',buffered:true});
});
await page.goto((process.env.PERF_ORIGIN || 'http://127.0.0.1:3000') + '/', {waitUntil:'networkidle'});
await page.evaluate(async () => { await document.fonts.ready; });
const metrics = await page.evaluate(() => ({...window.__fashionMetrics, navigation: performance.getEntriesByType('navigation').map(e=>({responseStart:e.responseStart,domContentLoaded:e.domContentLoadedEventEnd,load:e.loadEventEnd})), resources:performance.getEntriesByType('resource').filter(e=>/\.js|\.woff2|fashion/.test(e.name)).map(e=>({url:e.name.split('/').slice(-2).join('/'),transferSize:e.transferSize,decodedBodySize:e.decodedBodySize}))}));
const contrast = (a,b) => { const lum = hex=>{const c=hex.match(/[a-f\d]{2}/gi).map(c=>parseInt(c,16)/255).map(c=>c<=.04045?c/12.92:((c+.055)/1.055)**2.4); return .2126*c[0]+.7152*c[1]+.0722*c[2];}; const x=lum(a),y=lum(b);return (Math.max(x,y)+.05)/(Math.min(x,y)+.05); };
metrics.contrast={plumOnWhite:contrast('7c0046','ffffff'),greenOnWhite:contrast('1c3f1d','ffffff'),mutedOnIvory:contrast('6f6966','f7f5f1'),footerText:contrast('d2d0c8','292a27')};
metrics.context=process.env.PERF_CONTEXT || 'Unthrottled local Next development server, Chromium desktop, cold browser context; diagnostic only, not a production performance certification.';
await fs.writeFile(process.env.PERF_OUTPUT || 'artifacts/fashion-redesign/performance.json',JSON.stringify(metrics,null,2));
await page.screenshot({path:process.env.PERF_SCREENSHOT || 'artifacts/fashion-redesign/final-home-viewport-1440.png'});
await browser.close();
console.log(JSON.stringify({cls:metrics.cls,lcp:metrics.lcp,contrast:metrics.contrast}));
