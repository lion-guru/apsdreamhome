import { chromium } from 'playwright';

const p = await (await chromium.launch()).newPage();
await p.goto('http://localhost/apsdreamhome/', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(1500);
const r = await p.evaluate(() => {
  const out = {};
  out.order = [...document.querySelectorAll('link[rel=stylesheet]')].map(l => (l.getAttribute('href') || '').split('/').pop());
  const mb = document.querySelector('.mega-badge');
  if (mb) {
    out.megaBadge = { fontSize: getComputedStyle(mb).fontSize };
    const matched = [];
    for (const sh of document.styleSheets) {
      try {
        for (const r of sh.cssRules) {
          if (r.selectorText && r.selectorText.trim() === '.mega-badge' && r.style && r.style.fontSize) {
            matched.push({ href: (sh.href || 'inline').split('/').pop(), fs: r.style.fontSize, prio: r.style.getPropertyPriority('font-size') });
          }
        }
      } catch (e) {}
    }
    out.matched = matched;
  }
  return out;
});
console.log(JSON.stringify(r, null, 1));
process.exit(0);
