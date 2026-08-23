import { chromium } from 'playwright';

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await page.goto('http://localhost/apsdreamhome/about', { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(1500);

const rules = await page.evaluate(() => {
  const el = document.querySelector('.glass-stat-item .hero-stat-number');
  const matches = [];
  for (const sheet of document.styleSheets) {
    let cssRules;
    try { cssRules = sheet.cssRules; } catch (e) { continue; }
    const walk = (ruleList) => {
      for (const r of ruleList) {
        if (r.cssRules) { walk(r.cssRules); continue; }
        if (!r.selectorText || !r.style) continue;
        if (!r.style.color) continue;
        try {
          if (el.matches(r.selectorText)) {
            matches.push({ sel: r.selectorText.slice(0, 80), color: r.style.color, href: (sheet.href || 'inline').split('/').pop() });
          }
        } catch (e) {}
      }
    };
    walk(cssRules);
  }
  // also inline <style> blocks are covered above via sheet.href null
  return matches;
});
console.log(JSON.stringify(rules, null, 1));
await browser.close();
