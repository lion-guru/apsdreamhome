import { chromium } from 'playwright';

const ctx = await (await chromium.launch()).newContext({ viewport: { width: 1366, height: 900 } });
const p = await ctx.newPage();
await p.goto('http://localhost/apsdreamhome/associate/login', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(800);
await p.fill('input[name="email"]', 'testassociate@example.com');
await p.fill('input[name="password"]', 'Aps@2026');
const sessCookies = await ctx.cookies();
const sid = sessCookies.find(c => c.name === 'PHPSESSID');
let captcha = '';
try {
  const fs = await import('fs');
  const sess = fs.readFileSync('C:/xampp/tmp/sess_' + sid.value, 'utf8');
  const m = sess.match(/captcha_code\|s:\d+:"([^"]+)"/);
  if (m) captcha = m[1];
} catch (e) {}
if (captcha) await p.fill('[name=captcha_code]', captcha);
await Promise.all([p.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}), p.click('#submitBtn')]);
await p.waitForTimeout(1200);

const r = await p.evaluate(() => {
  const out = [];
  document.querySelectorAll('a, button').forEach(el => {
    const name = (el.getAttribute('aria-label') || el.getAttribute('title') || (el.innerText || '').trim());
    if (el.querySelector('svg, i') && !name) {
      let n = el.parentElement, path = [];
      for (let k = 0; k < 4 && n; k++) { path.push(n.tagName + '.' + String(n.className).split(' ').slice(0, 2).join('.')); n = n.parentElement; }
      out.push({ tag: el.tagName, cls: String(el.className).slice(0, 44), icon: (el.querySelector('i') || {}).className || '', path: path.join(' < ') });
    }
  });
  return out;
});
console.log(JSON.stringify(r, null, 1));
process.exit(0);
