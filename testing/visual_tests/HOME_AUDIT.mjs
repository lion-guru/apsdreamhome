import { chromium } from 'playwright';

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });
await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'domcontentloaded', timeout: 15000 });
await page.waitForTimeout(3000);

const issues = await page.evaluate(() => {
  const problems = [];

  // 1. Check style-xxxxx remnants
  document.querySelectorAll('[class*="style-"]').forEach(el => {
    if (el.tagName !== 'SCRIPT') {
      const r = el.getBoundingClientRect();
      if (r.height > 0) {
        problems.push('STYLE-REMNANT: ' + el.tagName + ' class=' + el.className.substring(0, 80) + ' h=' + Math.round(r.height));
      }
    }
  });

  // 2. Check hero search form input contrast
  document.querySelectorAll('.hero-premium input, .hero-premium select').forEach(el => {
    const s = getComputedStyle(el);
    problems.push('HERO-INPUT: ' + el.tagName + ' bg=' + s.backgroundColor + ' border-color=' + s.borderColor + ' color=' + s.color + ' placeholder=' + (el.placeholder || ''));
  });

  // 3. Check section-subtitle colors
  document.querySelectorAll('.section-subtitle').forEach(el => {
    const s = getComputedStyle(el);
    const r = el.getBoundingClientRect();
    if (r.height > 0) {
      problems.push('SUBTITLE: text=' + el.textContent.trim().substring(0, 30) + ' color=' + s.color + ' bg=' + s.backgroundColor);
    }
  });

  // 4. Check btn-primary on dark backgrounds
  document.querySelectorAll('.btn-primary').forEach(el => {
    const s = getComputedStyle(el);
    const r = el.getBoundingClientRect();
    if (r.height > 0) {
      let parent = el.parentElement;
      let parentBg = 'none';
      while (parent) {
        const pb = getComputedStyle(parent).backgroundColor;
        if (pb !== 'rgba(0, 0, 0, 0)') { parentBg = pb; break; }
        parent = parent.parentElement;
      }
      problems.push('BTN-PRIMARY: text=' + el.textContent.trim().substring(0, 20) + ' color=' + s.color + ' bg=' + s.backgroundColor + ' parentBg=' + parentBg);
    }
  });

  // 5. Check ps-filter-btn active state
  document.querySelectorAll('.ps-filter-btn').forEach(el => {
    const s = getComputedStyle(el);
    const r = el.getBoundingClientRect();
    if (r.height > 0) {
      problems.push('FILTER-BTN: text=' + el.textContent.trim().substring(0, 15) + ' color=' + s.color + ' bg=' + s.backgroundColor + ' active=' + el.classList.contains('active'));
    }
  });

  // 6. Check all sections have non-zero height
  const allSections = document.querySelectorAll('section');
  allSections.forEach((el, idx) => {
    const r = el.getBoundingClientRect();
    if (r.height < 10) {
      problems.push('EMPTY-SECTION: idx=' + idx + ' class=' + (el.className || '').substring(0, 40) + ' h=' + Math.round(r.height));
    }
  });

  // 7. Check for console errors captured
  // (This is from page context so we can't capture console here)

  return problems;
});

issues.forEach(i => console.log(i));
console.log('\nTotal issues: ' + issues.length);

// Take screenshots at key points
await page.screenshot({ path: 'C:/xampp/htdocs/apsdreamhome/testing/visual_tests/screenshots/audit-hero.png', fullPage: false });
await page.evaluate(() => window.scrollTo(0, 1400));
await page.waitForTimeout(500);
await page.screenshot({ path: 'C:/xampp/htdocs/apsdreamhome/testing/visual_tests/screenshots/audit-projects.png', fullPage: false });
await page.evaluate(() => window.scrollTo(0, 4000));
await page.waitForTimeout(500);
await page.screenshot({ path: 'C:/xampp/htdocs/apsdreamhome/testing/visual_tests/screenshots/audit-emi.png', fullPage: false });
await page.evaluate(() => window.scrollTo(0, 7000));
await page.waitForTimeout(500);
await page.screenshot({ path: 'C:/xampp/htdocs/apsdreamhome/testing/visual_tests/screenshots/audit-colonies.png', fullPage: false });
await page.evaluate(() => window.scrollTo(0, 10000));
await page.waitForTimeout(500);
await page.screenshot({ path: 'C:/xampp/htdocs/apsdreamhome/testing/visual_tests/screenshots/audit-cta.png', fullPage: false });

await browser.close();
