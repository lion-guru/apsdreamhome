const { chromium } = require('playwright');
const path = require('path');

const BASE_URL = 'http://localhost/apsdreamhome/';
const OUT_DIR = path.resolve(__dirname);
const OUT = f => path.join(OUT_DIR, f);

async function waitForPageReady(page) {
  await page.waitForLoadState('load');
  await page.waitForTimeout(2000);
  // Wait for fonts, images, etc.
  await page.evaluate(() => document.fonts?.ready);
  await page.waitForTimeout(500);
}

(async () => {
  console.log('Launching browser...');
  const browser = await chromium.launch({ headless: true });
  const results = [];

  try {
    // ─── 1. Desktop 1920x1080 Full Page ───────────────────────────
    console.log('\n[1/5] Desktop 1920x1080 - Full Page');
    const ctx1 = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
    const page1 = await ctx1.newPage();
    await page1.goto(BASE_URL, { waitUntil: 'load' });
    await waitForPageReady(page1);
    await page1.screenshot({ path: OUT('desktop_fullpage.png'), fullPage: true });
    console.log('  ✓ Saved: desktop_fullpage.png');
    results.push({ file: 'desktop_fullpage.png', desc: 'Desktop 1920x1080 full page' });
    await ctx1.close();

    // ─── 2. Tablet 768x1024 Full Page ──────────────────────────────
    console.log('[2/5] Tablet 768x1024 - Full Page');
    const ctx2 = await browser.newContext({ viewport: { width: 768, height: 1024 } });
    const page2 = await ctx2.newPage();
    await page2.goto(BASE_URL, { waitUntil: 'load' });
    await waitForPageReady(page2);
    await page2.screenshot({ path: OUT('tablet_fullpage.png'), fullPage: true });
    console.log('  ✓ Saved: tablet_fullpage.png');
    results.push({ file: 'tablet_fullpage.png', desc: 'Tablet 768x1024 full page' });
    await ctx2.close();

    // ─── 3. Mobile 375x812 Full Page ──────────────────────────────
    console.log('[3/5] Mobile 375x812 - Full Page');
    const ctx3 = await browser.newContext({ viewport: { width: 375, height: 812 } });
    const page3 = await ctx3.newPage();
    await page3.goto(BASE_URL, { waitUntil: 'load' });
    await waitForPageReady(page3);
    await page3.screenshot({ path: OUT('mobile_fullpage.png'), fullPage: true });
    console.log('  ✓ Saved: mobile_fullpage.png');
    results.push({ file: 'mobile_fullpage.png', desc: 'Mobile 375x812 full page' });
    await ctx3.close();

    // ─── 4. Desktop 1920x1080 Viewport Only ───────────────────────
    console.log('[4/5] Desktop 1920x1080 - Viewport Only');
    const ctx4 = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
    const page4 = await ctx4.newPage();
    await page4.goto(BASE_URL, { waitUntil: 'load' });
    await waitForPageReady(page4);
    await page4.screenshot({ path: OUT('desktop_viewport.png'), fullPage: false });
    console.log('  ✓ Saved: desktop_viewport.png');
    results.push({ file: 'desktop_viewport.png', desc: 'Desktop 1920x1080 viewport only' });
    await ctx4.close();

    // ─── 5. Mobile 375x812 Hamburger Menu ─────────────────────────
    console.log('[5/5] Mobile 375x812 - Hamburger Menu');
    const ctx5 = await browser.newContext({ viewport: { width: 375, height: 812 } });
    const page5 = await ctx5.newPage();
    await page5.goto(BASE_URL, { waitUntil: 'load' });
    await waitForPageReady(page5);

    // Try multiple selectors for the hamburger / navbar toggle button
    const hamburgerSelectors = [
      '.navbar-toggler',
      '.navbar-toggle',
      '.hamburger',
      '.menu-toggle',
      'button.navbar-toggler',
      'button.collapsed',
      '[data-bs-toggle="collapse"]',
      '.nav-toggle',
      '.menu-icon',
      'button[aria-label*="menu" i]',
      'button[aria-label*="toggle" i]',
      '.mobile-menu-btn',
      '#nav-icon',
      '.hamburger-menu',
      // generic last resort: any button with 3 child spans (common hamburger pattern)
    ];

    let clicked = false;
    for (const sel of hamburgerSelectors) {
      const btn = await page5.$(sel);
      if (btn) {
        console.log(`  → Found hamburger: "${sel}"`);
        await btn.click();
        await page5.waitForTimeout(800);
        clicked = true;
        break;
      }
    }

    if (!clicked) {
      console.log('  → No hamburger button found via selectors. Trying heuristic scan...');
      // fallback: find any small button in the header/nav area
      const buttons = await page5.evaluate(() => {
        const nav = document.querySelector('header') || document.querySelector('nav') || document.body;
        const btns = Array.from(nav.querySelectorAll('button'));
        return btns.map(b => ({ tag: b.tagName, id: b.id, class: b.className, text: b.innerText.slice(0, 30), html: b.outerHTML.slice(0, 120) }));
      });
      if (buttons.length > 0) {
        console.log(`  Found ${buttons.length} buttons in header/nav:`);
        buttons.forEach((b, i) => console.log(`    [${i}] id="${b.id}" class="${b.class}" text="${b.text}"`));
        // try clicking the first one
        await page5.evaluate(() => {
          const nav = document.querySelector('header') || document.querySelector('nav') || document.body;
          const btn = nav.querySelector('button');
          if (btn) btn.click();
        });
        await page5.waitForTimeout(800);
        clicked = true;
      } else {
        console.log('  → No buttons found at all. Taking screenshot without hamburger click.');
      }
    }

    await page5.screenshot({ path: OUT('mobile_hamburger.png'), fullPage: false });
    console.log('  ✓ Saved: mobile_hamburger.png');
    results.push({ file: 'mobile_hamburger.png', desc: 'Mobile 375x812 hamburger menu' });

    // Also capture button info for analysis
    const btnInfo = await page5.evaluate(() => {
      const header = document.querySelector('header');
      if (!header) return { error: 'No header element found' };
      const btns = header.querySelectorAll('button, a.btn, [role="button"]');
      return Array.from(btns).map(b => ({
        tag: b.tagName,
        id: b.id,
        className: b.className.slice(0, 80),
        text: (b.innerText || '').slice(0, 40),
        rect: b.getBoundingClientRect()
      }));
    });
    console.log('  Header interactive elements:', JSON.stringify(btnInfo, null, 2).slice(0, 1000));

    await ctx5.close();

  } catch (err) {
    console.error('ERROR:', err.message);
  } finally {
    await browser.close();
  }

  // ─── Summary ──────────────────────────────────────────────────
  console.log('\n══════════════════════════════════════════════');
  console.log('  SCREENSHOTS CAPTURED');
  console.log('══════════════════════════════════════════════');
  results.forEach(r => console.log(`  ${r.file}  →  ${r.desc}`));
  console.log('══════════════════════════════════════════════\n');
})();
