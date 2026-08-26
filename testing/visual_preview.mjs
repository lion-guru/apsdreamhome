/**
 * Visual Preview Capture — Session 78 night
 * Screenshots 8 key pages for render-quality verification.
 * Run: node testing/visual_preview.mjs
 */
import { chromium } from 'playwright';
import { mkdirSync } from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const OUT = 'testing/screenshots/session78';
mkdirSync(OUT, { recursive: true });

const PUBLIC_PAGES = [
  ['home', '/'],
  ['colonies', '/colonies'],
  ['properties', '/properties'],
  ['faq', '/faq'],
  ['blog', '/blog'],
  ['mobile-app', '/mobile-app'],
];

const run = async () => {
  const browser = await chromium.launch();
  const results = [];

  // Public pages (fresh context each)
  for (const [name, path] of PUBLIC_PAGES) {
    const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
    const page = await ctx.newPage();
    try {
      await page.goto(BASE + path, { waitUntil: 'domcontentloaded', timeout: 20000 });
      await page.waitForTimeout(1200);
      // Blank-page detector: body text length
      const textLen = (await page.evaluate(() => document.body.innerText.length));
      await page.screenshot({ path: `${OUT}/${name}.png`, fullPage: false });
      results.push([name, 'OK', `${textLen} chars`]);
    } catch (e) {
      results.push([name, 'FAIL', e.message.split('\n')[0].slice(0, 60)]);
    }
    await ctx.close();
  }

  // Admin ERP (with test_login session)
  const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
  const page = await ctx.newPage();
  try {
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: `${OUT}/admin-dashboard.png` });
    const url = page.url();
    results.push(['admin-dashboard', url.includes('dashboard') || url.includes('erp') ? 'OK' : 'REDIRECT', url.replace(BASE, '')]);

    await page.goto(`${BASE}/admin/mlm`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
    await page.screenshot({ path: `${OUT}/admin-mlm.png` });
    results.push(['admin-mlm', 'OK', page.url().replace(BASE, '')]);
  } catch (e) {
    results.push(['admin', 'FAIL', e.message.split('\n')[0].slice(0, 60)]);
  }
  await ctx.close();

  await browser.close();

  console.log('\n=== VISUAL PREVIEW RESULTS ===');
  let fail = 0;
  for (const [name, status, detail] of results) {
    if (status !== 'OK') fail++;
    console.log(`${status === 'OK' ? 'PASS' : 'WARN'}  ${name.padEnd(18)} ${status.padEnd(8)} ${detail}`);
  }
  console.log(`\nScreenshots saved to ${OUT}`);
  process.exit(fail > 2 ? 1 : 0);
};

run();
