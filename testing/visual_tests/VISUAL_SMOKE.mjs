import { chromium } from 'playwright';
import { mkdirSync, existsSync, statSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const BASE = 'http://localhost/apsdreamhome';
const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT = join(__dirname, 'screenshots');
if (!existsSync(OUT)) mkdirSync(OUT, { recursive: true });

const pages = [
  { path: '/', name: '01-home' },
  { path: '/properties', name: '02-properties' },
  { path: '/colonies', name: '03-colonies' },
  { path: '/admin/login?test_login=1', name: '04-admin-erp', auth: true },
  { path: '/user/dashboard', name: '05-customer' },
  { path: '/associate/dashboard', name: '06-associate' },
  { path: '/employee/dashboard', name: '07-employee' },
];

const viewports = [
  { name: 'desktop', w: 1280, h: 800 },
  { name: 'mobile', w: 390, h: 844 },
];

async function shot(browser, pageCfg, vp) {
  const context = await browser.newContext({ viewport: { width: vp.w, height: vp.h } });
  const page = await context.newPage();
  // Pre-auth for portal pages via test_login or direct goto
  if (pageCfg.auth) {
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForTimeout(1500);
  }
  const url = `${BASE}${pageCfg.path}`;
  const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(1200);
  const file = join(OUT, `${pageCfg.name}-${vp.name}.png`);
  await page.screenshot({ path: file, fullPage: false });
  const size = statSync(file).size;
  const status = resp ? resp.status() : 0;
  const pass = size > 5000 && (status === 200 || status === 302);
  console.log(`  ${pass ? 'OK' : 'FAIL'} ${pageCfg.name} ${vp.name} => ${status} ${Math.round(size/1024)}KB -> ${file}`);
  await context.close();
  return pass;
}

async function run() {
  const browser = await chromium.launch({ headless: true });
  let pass=0, fail=0;
  console.log('--- Visual Smoke (vision) ---');
  for (const vp of viewports) {
    console.log(`\nViewport: ${vp.name} ${vp.w}x${vp.h}`);
    for (const p of pages) {
      const ok = await shot(browser, p, vp);
      if (ok) pass++; else fail++;
    }
  }
  await browser.close();
  console.log(`\nTOTAL: ${pass} passed, ${fail} failed (${pass+fail} shots)`);
  console.log(`Screenshots: ${OUT}`);
  process.exit(fail>0?1:0);
}
run().catch(e=>{ console.error(e); process.exit(1); });
