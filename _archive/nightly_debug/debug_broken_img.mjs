import { chromium } from 'playwright';
import fs from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const browser = await chromium.launch();
const ctx = await browser.newContext();
const p = await ctx.newPage();

await p.goto(BASE + '/auth/login', { waitUntil: 'domcontentloaded' });
const sid = (await ctx.cookies()).find(c => c.name === 'PHPSESSID').value;
const sess = fs.readFileSync(`C:/xampp/tmp/sess_${sid}`, 'utf8');
const code = (sess.match(/captcha_code\|s:\d+:"([^"]+)"/) || [])[1];
await p.fill('input[name="identity"]', 'testuser@example.com');
await p.fill('input[name="password"]', 'Aps@2026');
await p.fill('input[name="captcha_code"]', code);
await Promise.all([
  p.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}),
  p.click('form button[type="submit"]'),
]);
await p.waitForTimeout(1500);
console.log('logged in:', p.url());

await p.goto(BASE + '/user/properties', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(2500);
const broken = await p.evaluate(() => Array.from(document.querySelectorAll('img'))
  .filter(i => i.complete && i.naturalWidth === 0 && !i.src.startsWith('data:'))
  .map(i => ({
    src: i.src.slice(0, 200),
    alt: i.alt || '',
    cls: (i.className || '').slice(0, 80),
    parentCls: ((i.closest('[class]') || {}).className || '').slice(0, 100),
    outer: i.outerHTML.slice(0, 250),
  })));
console.log('broken count:', broken.length);
console.log(JSON.stringify(broken, null, 1));
await browser.close();
