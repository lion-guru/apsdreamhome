const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const ctx = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await ctx.newPage();
  await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(3000);

  const cardStyles = await page.evaluate(() => {
    const card = document.querySelector('.card.border-0.shadow-lg.bg-dark');
    if (!card) return { error: 'card not found' };
    const s = getComputedStyle(card);
    return {
      backgroundColor: s.backgroundColor,
      color: s.color,
      classList: Array.from(card.classList),
      parentSection: card.closest('section') ? Array.from(card.closest('section').classList) : 'no section parent'
    };
  });
  console.log('Card styles:', JSON.stringify(cardStyles, null, 2));

  // Also check what the form-select in dark sections looks like
  const formStyles = await page.evaluate(() => {
    const select = document.querySelector('.emi-section .form-select, .hero-search-glass .form-select');
    if (!select) {
      const allSelects = document.querySelectorAll('.form-select');
      const results = [];
      allSelects.forEach((s, i) => {
        const st = getComputedStyle(s);
        results.push({
          index: i,
          classes: s.className,
          color: st.color,
          bgColor: st.backgroundColor,
          placeholderColor: getComputedStyle(s, '::placeholder').color
        });
      });
      return results;
    }
    const st = getComputedStyle(select);
    return {
      classes: select.className,
      color: st.color,
      bgColor: st.backgroundColor,
      placeholderColor: getComputedStyle(select, '::placeholder').color
    };
  });
  console.log('Form select styles:', JSON.stringify(formStyles, null, 2));

  await browser.close();
})();
