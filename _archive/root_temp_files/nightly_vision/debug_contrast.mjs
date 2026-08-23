import { chromium } from 'playwright';

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto('http://localhost/apsdreamhome/about', { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(1500);

const probe = await page.evaluate(() => {
  const out = {};
  const num = document.querySelector('.glass-stat-item .hero-stat-number');
  if (num) {
    const cs = getComputedStyle(num);
    out.number = { color: cs.color, fs: cs.fontSize };
    // walk ancestors reporting bg
    out.chain = [];
    let n = num;
    while (n && n !== document.documentElement) {
      const c = getComputedStyle(n);
      out.chain.push({ tag: n.tagName, cls: String(n.className).slice(0, 40), bg: c.backgroundColor, bi: (c.backgroundImage || 'none').slice(0, 60) });
      n = n.parentElement;
    }
  }
  return out;
});
console.log(JSON.stringify(probe, null, 1));
await browser.close();
