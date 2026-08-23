import { chromium } from 'playwright';
const b = await chromium.launch();
const p = await (await b.newContext()).newPage();
await p.goto('http://localhost/apsdreamhome/about', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(1000);
const r = await p.evaluate(() => {
  const g = s => {
    const el = document.querySelector(s);
    if (!el) return null;
    const cs = getComputedStyle(el);
    let n = el.parentElement, bgs = [];
    for (let k = 0; k < 6 && n; k++) {
      const bg = getComputedStyle(n).backgroundColor;
      const bi = getComputedStyle(n).backgroundImage;
      if ((bg && bg !== 'rgba(0, 0, 0, 0)') || (bi && bi !== 'none')) bgs.push(n.tagName + '.' + String(n.className).split(' ')[0] + ' bg=' + bg + (bi !== 'none' ? ' img' : ''));
      n = n.parentElement;
    }
    return { color: cs.color, fs: cs.fontSize, bgs };
  };
  return { stat: g('.glass-stat-item .stat-label'), hero: g('.hero-subtitle'), badge: g('.story-badge') };
});
console.log(JSON.stringify(r, null, 1));
process.exit(0);
