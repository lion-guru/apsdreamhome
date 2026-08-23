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
  const cw = [...document.querySelectorAll('.content-wrapper')];
  const mains = [...document.querySelectorAll('main.main-content')];
  const row = [...document.querySelectorAll('.row.g-4')].find(r => r.textContent.includes('Performance Overview'));
  const path = [];
  let n = row;
  while (n && n !== document.documentElement) {
    path.push(n.tagName.toLowerCase() + '.' + String(n.className).split(/\s+/).slice(0,2).join('.'));
    n = n.parentElement;
  }
  return {
    contentWrappers: cw.length,
    cwPaddings: cw.map(w => getComputedStyle(w).paddingLeft),
    mainsCount: mains.length,
    rowParentPath: path.slice(0, 6),
    rowHTMLStart: row ? row.parentElement.outerHTML.slice(0, 200) : null,
  };
});
console.log(JSON.stringify(info, null, 1));
await browser.close();
