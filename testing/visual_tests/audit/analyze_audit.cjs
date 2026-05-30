const { chromium } = require('playwright');
const path = require('path');

const BASE_URL = 'http://localhost/apsdreamhome/';
const viewports = [
  { name: 'desktop_1920', width: 1920, height: 1080 },
  { name: 'tablet_768',   width: 768,  height: 1024 },
  { name: 'mobile_375',   width: 375,  height: 812 },
];

async function analyze(page, label) {
  return await page.evaluate((l) => {
    const results = { label: l, issues: [], good: [], layout: {} };

    // 1. Viewport size
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    results.layout.viewport = `${vw}x${vh}`;

    // 2. DOCTYPE check
    results.layout.doctype = document.doctype ? document.doctype.name : 'MISSING';

    // 3. Header present
    const header = document.querySelector('header');
    results.layout.header_found = !!header;
    if (!header) results.issues.push('No <header> element found');

    // 4. Navbar
    const nav = document.querySelector('nav, .navbar, header nav');
    results.layout.nav_found = !!nav;
    if (!nav) results.issues.push('No <nav>/navbar found');

    // 5. Main content
    const main = document.querySelector('main, #main, .main-content, .content');
    results.layout.main_found = !!main;
    if (!main && vw > 600) results.issues.push('No <main> content area found');

    // 6. Footer
    const footer = document.querySelector('footer');
    results.layout.footer_found = !!footer;
    if (!footer) results.issues.push('No <footer> found');

    // 7. Hero / banner section
    const hero = document.querySelector('.hero, .banner, #hero, .slider, .carousel');
    results.layout.hero_found = !!hero;

    // 8. Broken images
    const imgs = document.querySelectorAll('img');
    let brokenImgs = 0;
    imgs.forEach(img => { if (!img.complete || img.naturalWidth === 0) brokenImgs++; });
    results.layout.total_images = imgs.length;
    results.layout.broken_images = brokenImgs;
    if (brokenImgs > 0) results.issues.push(`${brokenImgs}/${imgs.length} images broken`);

    // 9. Horizontal overflow
    const docWidth = document.documentElement.scrollWidth;
    results.layout.horizontal_overflow = docWidth > vw + 5; // 5px tolerance
    if (results.layout.horizontal_overflow) {
      results.issues.push(`Horizontal overflow: ${docWidth}px > ${vw}px viewport`);
    }

    // 10. Hamburger visibility on mobile
    const hamburger = document.querySelector('.navbar-toggler');
    results.layout.hamburger_found = !!hamburger;
    if (vw <= 600 && !hamburger) results.issues.push('Mobile: no hamburger menu button found');

    // 11. Collapse menu present
    const collapse = document.querySelector('.navbar-collapse, .collapse');
    results.layout.collapse_found = !!collapse;

    // 12. Empty containers
    const empty = document.querySelectorAll('div:empty, section:empty');
    if (empty.length > 3) results.issues.push(`${empty.length} empty divs/sections found`);

    // 13. Overlapping elements (basic check)
    const allEls = document.querySelectorAll('*');
    let overlapCount = 0;
    const rects = [];
    for (const el of allEls) {
      const r = el.getBoundingClientRect();
      if (r.width === 0 || r.height === 0) continue;
      if (r.x < -5 || r.y < -5) continue; // offscreen
      rects.push(r);
    }
    // simplified: check if certain key elements overlap
    if (header && main) {
      const hr = header.getBoundingClientRect();
      const mr = main.getBoundingClientRect();
      if (hr.bottom > mr.top + 10) {
        results.issues.push(`Header bottom (${hr.bottom}) overlaps main top (${mr.top})`);
      }
    }

    // 14. Buttons are clickable
    const buttons = document.querySelectorAll('button, a.btn');
    let invisibleButtons = 0;
    buttons.forEach(b => {
      const r = b.getBoundingClientRect();
      if (r.width < 5 || r.height < 5) invisibleButtons++;
    });
    if (invisibleButtons > 0) results.issues.push(`${invisibleButtons} buttons have near-zero size`);

    // 15. Font Awesome / icon presence
    const icons = document.querySelectorAll('i.fa, i.fas, i.far, i.fab, svg');
    results.layout.icons_found = icons.length;

    // — Positive observations —
    if (header) results.good.push('Header present');
    if (nav) results.good.push('Navigation present');
    if (footer) results.good.push('Footer present');
    if (hero) results.good.push('Hero/banner section found');
    if (main) results.good.push('Main content area found');
    if (brokenImgs === 0 && imgs.length > 0) results.good.push('All images loaded');
    if (!results.layout.horizontal_overflow) results.good.push('No horizontal overflow');
    if (hamburger && vw <= 600) results.good.push('Hamburger menu button present on mobile');
    if (collapse) results.good.push('Collapsible menu found');
    if (icons.length > 5) results.good.push(`${icons.length} icons rendered`);

    return results;
  }, label);
}

(async () => {
  console.log('=== HOME PAGE VISUAL AUDIT ===\n');
  const browser = await chromium.launch({ headless: true });

  for (const vp of viewports) {
    console.log(`── ${vp.name} (${vp.width}x${vp.height}) ──`);
    const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
    const page = await ctx.newPage();
    await page.goto(BASE_URL, { waitUntil: 'load' });
    await page.waitForTimeout(2000);
    await page.evaluate(() => document.fonts?.ready);
    await page.waitForTimeout(500);

    const report = await analyze(page, vp.name);
    console.log(`  Viewport: ${report.layout.viewport}`);
    console.log(`  DOCTYPE: ${report.layout.doctype}`);
    console.log(`  Header: ${report.layout.header_found}, Nav: ${report.layout.nav_found}, Main: ${report.layout.main_found}, Footer: ${report.layout.footer_found}`);
    console.log(`  Hero: ${report.layout.hero_found}, Hamburger: ${report.layout.hamburger_found}, Collapse: ${report.layout.collapse_found}`);
    console.log(`  Images: ${report.layout.total_images} total, ${report.layout.broken_images} broken`);
    console.log(`  Icons: ${report.layout.icons_found}, H-overflow: ${report.layout.horizontal_overflow}`);

    if (report.good.length > 0) {
      console.log(`  ✅ Good: ${report.good.join(', ')}`);
    }
    if (report.issues.length > 0) {
      console.log(`  ❌ Issues:`);
      report.issues.forEach(i => console.log(`    • ${i}`));
    } else {
      console.log(`  ✅ No issues detected`);
    }
    console.log('');

    // Mobile hamburger test
    if (vp.width <= 600) {
      const ham = await page.$('.navbar-toggler');
      if (ham) {
        await ham.click();
        await page.waitForTimeout(800);
        const afterClick = await page.evaluate(() => {
          const menu = document.querySelector('.navbar-collapse, .collapse');
          if (!menu) return { expanded: 'unknown - no collapse element' };
          const isVisible = menu.getBoundingClientRect().height > 5;
          const hasShow = menu.classList.contains('show') || menu.classList.contains('in');
          const style = window.getComputedStyle(menu).display;
          return { expanded: isVisible || hasShow, display: style, height: Math.round(menu.getBoundingClientRect().height), classes: menu.className };
        });
        console.log(`  🍔 Hamburger click test: expanded=${afterClick.expanded}, display=${afterClick.display}, height=${afterClick.height}px`);
        if (!afterClick.expanded) {
          console.log(`  ❌ Hamburger click did NOT expand the menu`);
        } else {
          console.log(`  ✅ Hamburger menu expanded successfully`);
        }
      } else {
        console.log(`  ❌ No hamburger button found for mobile`);
      }
    }

    await ctx.close();
  }

  // Check all pages load stat
  console.log('── Additional Checks ──');
  const ctx = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const p = await ctx.newPage();

  const pagesToCheck = [
    '/', '/properties', '/list-property', '/services',
    '/login', '/register', '/support'
  ];
  for (const route of pagesToCheck) {
    try {
      const resp = await p.goto(BASE_URL.replace(/\/$/, '') + route, { waitUntil: 'load', timeout: 10000 });
      const status = resp ? resp.status() : 'NO RESPONSE';
      const title = await p.title();
      const hasContent = await p.evaluate(() => document.body.innerText.length > 50);
      console.log(`  ${route}: HTTP ${status}, title="${title.slice(0, 60)}", hasContent=${hasContent}`);
    } catch (err) {
      console.log(`  ${route}: ERROR - ${err.message.slice(0, 80)}`);
    }
  }

  await ctx.close();
  await browser.close();
  console.log('\n=== AUDIT COMPLETE ===');
})();
