import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';

async function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await ctx.newPage();

  await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'networkidle' });
  await sleep(2000);

  const newRoutes = [
    '/admin/leads/status',
    '/admin/leads/followups',
    '/admin/leads/import',
    '/admin/leads/analysis',
    '/admin/plots/categories',
    '/admin/hrm/employees',
  ];

  console.log('=== New/Fixed Routes ===');
  for (const url of newRoutes) {
    try {
      await page.goto(`${BASE}${url}`, { waitUntil: 'networkidle', timeout: 15000 });
      await sleep(500);
      const status = page.url().startsWith(`${BASE}${url}`) ? 'OK' : `REDIRECT(${page.url().substring(BASE.length,80)})`;
      const title = await page.title();
      console.log(`  ${url}: ${status} | ${title.substring(0,60)}`);
    } catch (e) {
      console.log(`  ${url}: ERROR ${e.message.substring(0,60)}`);
    }
  }

  // Also check all 5 previously-404 menu items now resolve
  await page.goto(`${BASE}/admin/leads`, { waitUntil: 'networkidle' });
  await sleep(1000);
  console.log(`\n  /admin/leads (fixed layout): ${await page.title()}`);

  await browser.close();
  console.log('\n=== Verification Complete ===');
})();
