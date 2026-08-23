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

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();
await page.goto(BASE + '/associate/login', { waitUntil: 'domcontentloaded' });
const code = captchaFromCookie((await ctx.cookies()).find(c => c.name === 'PHPSESSID'));
await page.fill('input[name="email"]', 'testassociate@example.com');
await page.fill('input[name="password"]', PASSWORD);
await page.fill('input[name="captcha_code"]', code);
await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}), page.click('#submitBtn')]);
await page.goto(BASE + '/associate/dashboard', { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(2000);

const info = await page.evaluate(() => {
  const main = document.querySelector('main.main-content');
  const kids = [...main.children].map(k => k.tagName.toLowerCase() + '.' + String(k.className).split(/\s+/)[0] + ' [kids:' + k.children.length + ']');
  const cw = document.querySelector('.content-wrapper');
  return {
    mainChildren: kids,
    cwParentTag: cw ? cw.parentElement.tagName : null,
    cwFirstChild: cw && cw.firstElementChild ? cw.firstElementChild.tagName + '.' + String(cw.firstElementChild.className).split(/\s+/)[0] : '(empty)',
    bodyDirectKids: [...document.body.children].map(k => k.tagName.toLowerCase() + '.' + String(k.className).split(/\s+/)[0]),
  };
});
console.log(JSON.stringify(info, null, 1));
await browser.close();
