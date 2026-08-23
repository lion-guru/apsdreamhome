import { chromium } from 'playwright';
import fs from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const PASSWORD = 'Aps@2026';
function captchaFromCookie(cookie) {
  const f = `C:/xampp/tmp/sess_${cookie.value}`;
  if (!fs.existsSync(f)) return null;
  const m = fs.readFileSync(f, 'utf8').match(/captcha_code\|s:\d+:"([^"]+)"/);
  return m ? m[1] : null;
}
async function loginCustomer(ctx, page) {
  await page.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="identity"]', 'testuser@example.com');
  await page.fill('input[name="password"]', PASSWORD);
  await page.fill('input[name="captcha_code"]', captchaFromCookie((await ctx.cookies()).find(c => c.name === 'PHPSESSID')));
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}), page.click('form button[type="submit"]')]);
}

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();
await loginCustomer(ctx, page);
await page.goto(BASE + '/user/dashboard', { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(2000);

const info = await page.evaluate(() => {
  const row = document.querySelector('.aps-cp-hero .row');
  if (!row) return { err: 'row not found' };
  const chain = [];
  let n = row;
  while (n && n !== document.documentElement) {
    const cs = getComputedStyle(n);
    chain.push({
      tag: n.tagName.toLowerCase(), cls: String(n.className || '').slice(0, 50),
      pl: cs.paddingLeft, ml: cs.marginLeft, ovx: cs.overflowX, w: Math.round(n.getBoundingClientRect().width),
    });
    n = n.parentElement;
  }
  return chain;
});
console.log(JSON.stringify(info, null, 1));
await browser.close();
