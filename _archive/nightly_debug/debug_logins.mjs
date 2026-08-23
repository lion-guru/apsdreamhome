import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';
const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext({ viewport: { width: 1366, height: 768 } });
const page = await ctx.newPage();

// --- Debug customer /auth/login ---
await page.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded', timeout: 45000 });
await page.waitForTimeout(2000);
const inputs = await page.evaluate(() =>
  Array.from(document.querySelectorAll('input')).map(i => ({
    name: i.name, type: i.type, visible: !!(i.offsetWidth || i.offsetHeight || i.getClientRects().length)
  }))
);
console.log('/auth/login inputs:', JSON.stringify(inputs));
console.log('/auth/login URL:', page.url());

// --- Debug associate login ---
await page.goto(BASE + '/associate/login', { waitUntil: 'domcontentloaded', timeout: 45000 });
await page.waitForTimeout(1500);
await page.fill('input[name="email"]', 'testassociate@example.com');
await page.fill('input[name="password"]', 'Aps@2026');
await Promise.all([
  page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => {}),
  page.click('#associateLoginForm button[type="submit"], #associateLoginForm button:not([type]), #associateLoginForm input[type="submit"]').catch(async () => {
    await page.press('input[name="password"]', 'Enter');
  }),
]);
await page.waitForTimeout(2500);
console.log('\nassociate after submit URL:', page.url());
const flash = await page.evaluate(() => {
  const els = document.querySelectorAll('.alert, .error, [class*="invalid"], [role="alert"]');
  return Array.from(els).map(e => e.textContent.trim().slice(0, 120)).filter(Boolean);
});
console.log('associate error msgs:', JSON.stringify(flash));

await browser.close();
