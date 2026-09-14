const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await ctx.newPage();
  await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(2000);

  // Get all loaded CSS files
  const cssLinks = await page.evaluate(() => {
    return Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(l => l.href);
  });
  console.log('=== Loaded CSS files ===');
  cssLinks.forEach(l => console.log(l));

  // Check --premium-emerald value
  const cssVars = await page.evaluate(() => {
    const root = document.documentElement;
    const style = getComputedStyle(root);
    return {
      premiumEmerald: style.getPropertyValue('--premium-emerald'),
      premiumGold: style.getPropertyValue('--premium-gold'),
      premiumNavy: style.getPropertyValue('--premium-navy')
    };
  });
  console.log('=== CSS Variables ===');
  console.log(JSON.stringify(cssVars, null, 2));

  // Check the specific elements
  const elementInfo = await page.evaluate(() => {
    const results = [];
    const selectors = [
      '.capsule-badge.capsule-teal',
      '.capsule-badge.capsule-green', 
      '.capsule-badge.capsule-amber',
      '.capsule-badge.badge-delivered',
      '.capsule-badge.badge-ongoing',
      '.price-tag',
      '.quick-link-card h6',
      '.section-label',
      '.form-select',
      '.bg-dark.text-white',
      '.emi-section'
    ];
    selectors.forEach(sel => {
      const el = document.querySelector(sel);
      if (el) {
        const s = getComputedStyle(el);
        const rect = el.getBoundingClientRect();
        results.push({
          selector: sel,
          color: s.color,
          bgColor: s.backgroundColor,
          hasBgDark: el.classList.contains('bg-dark'),
          tagName: el.tagName,
          text: el.textContent.trim().substring(0, 40)
        });
      }
    });
    return results;
  });
  console.log('=== Element Styles ===');
  console.log(JSON.stringify(elementInfo, null, 2));

  await browser.close();
})();
