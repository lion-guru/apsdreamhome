import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const pages = ['/', '/properties', '/admin/login?test_login=1'];

async function vitalsFor(browser, path) {
  const ctx = await browser.newContext();
  const page = await ctx.newPage();
  await page.goto(`${BASE}${path}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(2000);
  const m = await page.evaluate(() => {
    const nav = performance.getEntriesByType('navigation')[0];
    const paint = performance.getEntriesByType('paint');
    const lcpEntries = performance.getEntriesByType('largest-contentful-paint');
    const clsEntries = performance.getEntriesByType('layout-shift');
    let cls = 0; for (const e of clsEntries) if (!e.hadRecentInput) cls += e.value;
    const fcp = (paint.find(p=>p.name==='first-contentful-paint')||{}).startTime || 0;
    const lcp = lcpEntries.length ? lcpEntries[lcpEntries.length-1].startTime : 0;
    return {
      domContentLoaded: nav ? Math.round(nav.domContentLoadedEventEnd) : 0,
      load: nav ? Math.round(nav.loadEventEnd) : 0,
      fcp: Math.round(fcp),
      lcp: Math.round(lcp),
      cls: Math.round(cls*1000)/1000,
      resources: performance.getEntriesByType('resource').length,
    };
  });
  console.log(`  ${path} => dom:${m.domContentLoaded}ms load:${m.load}ms FCP:${m.fcp}ms LCP:${m.lcp}ms CLS:${m.cls} res:${m.resources}`);
  await ctx.close();
  return m;
}

async function run(){
  const browser = await chromium.launch({ headless:true });
  console.log('--- Vitals Smoke ---');
  for (const p of pages) await vitalsFor(browser, p);
  await browser.close();
}
run().catch(e=>{console.error(e);process.exit(1);});
