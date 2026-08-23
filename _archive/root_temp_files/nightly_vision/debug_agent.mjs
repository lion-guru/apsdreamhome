import { chromium } from 'playwright';
import { readFileSync } from 'fs';

const b = await chromium.launch();
const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
const p = await ctx.newPage();

// agent login via /agent/login form
await p.goto('http://localhost/apsdreamhome/agent/login', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(800);
await p.fill('#email', 'agent@apsdreamhome.com');
await p.fill('#password', 'Aps@2026');
await Promise.all([p.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => {}), p.click('button[type="submit"]')]);
await p.waitForTimeout(1500);
console.log('url after login:', p.url());

await p.goto('http://localhost/apsdreamhome/agent/dashboard', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(1500);

const r = await p.evaluate(() => {
  const out = {};
  const probe = (sel) => {
    const el = document.querySelector(sel);
    if (!el) return 'not found';
    const cs = getComputedStyle(el);
    let bg = 'rgba(0, 0, 0, 0)', n = el.parentElement, depth = 0;
    while (n && depth < 8) { const b = getComputedStyle(n).backgroundColor; if (b && !b.includes('0, 0, 0, 0')) { bg = b; break; } n = n.parentElement; depth++; }
    return { color: cs.color, fontSize: cs.fontSize, bg, text: (el.textContent || '').slice(0, 14) };
  };
  out.soldInfo = probe('small.text-info');
  out.warnH4 = probe('h4.mb-0.text-warning');
  return out;
});
console.log(JSON.stringify(r, null, 1));
await b.close();
