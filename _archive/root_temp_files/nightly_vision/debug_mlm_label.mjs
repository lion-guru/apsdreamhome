import { chromium } from 'playwright';

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();
await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(1200);
await page.goto('http://localhost/apsdreamhome/admin/mlm', { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(1500);

const probe = await page.evaluate(() => {
  const el = [...document.querySelectorAll('.aps-cp-stat-label')][0];
  if (!el) return { none: true };
  const cs = getComputedStyle(el);
  const chain = [];
  let n = el;
  while (n && n !== document.documentElement) {
    const c = getComputedStyle(n);
    if (c.backgroundColor !== 'rgba(0, 0, 0, 0)' || c.backgroundImage !== 'none') {
      chain.push({ tag: n.tagName, cls: String(n.className).slice(0, 50), bg: c.backgroundColor.slice(0, 40) });
    }
    n = n.parentElement;
  }
  return { color: cs.color, chain };
});
console.log(JSON.stringify(probe, null, 1));
await browser.close();
