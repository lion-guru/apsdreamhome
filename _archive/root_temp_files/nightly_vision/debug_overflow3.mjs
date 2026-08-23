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
  const rows = [...document.querySelectorAll('.row.g-4')];
  const row = rows.find(r => r.textContent.includes('Performance Overview'));
  const out = {
    url: location.href,
    title: document.title.slice(0, 60),
    rowCount: rows.length,
    hasPerf: document.body.textContent.includes('Performance Overview'),
  };
  if (!row) return out;
  out.chain = [];
  let n = row;
  while (n && n !== document.documentElement) {
    const cs = getComputedStyle(n);
    out.chain.push({
      tag: n.tagName.toLowerCase(), cls: String(n.className || '').slice(0, 55),
      pl: cs.paddingLeft, ml: cs.marginLeft, ovx: cs.overflowX,
      x: Math.round(n.getBoundingClientRect().x), w: Math.round(n.getBoundingClientRect().width),
    });
    n = n.parentElement;
  }
  out.css = [...document.querySelectorAll('link[rel="stylesheet"]')].map(l => l.getAttribute('href').split('/').pop());
  return out;
});
console.log(JSON.stringify(info, null, 1));
await browser.close();
