const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });

  // Test: mobile menu open
  const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
  await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'load' });
  await page.waitForTimeout(1500);

  // Click hamburger to open mobile menu
  const toggler = await page.$('#navbarToggler');
  if (toggler) {
    await toggler.click();
    await page.waitForTimeout(500);
  }

  // Check if the icons are visible or hidden behind backdrop/menu
  const info = await page.evaluate(() => {
    const results = {};
    
    const aiBtn = document.querySelector('.ai-float-btn');
    const waBtn = document.querySelector('.whatsapp-float-btn');
    
    function getInfo(el, name) {
      if (!el) return name + ': NOT FOUND';
      const cs = getComputedStyle(el);
      const rect = el.getBoundingClientRect();
      
      // Check if behind another element
      const cx = rect.left + rect.width/2;
      const cy = rect.top + rect.height/2;
      const topEl = document.elementFromPoint(cx, cy);
      const menuPanel = document.getElementById('navbarNav');
      
      // Check if menu backdrop is covering it
      const isBehindBackdrop = topEl && (
        topEl.classList.contains('nav-backdrop') || 
        topEl.classList.contains('menu-backdrop') ||
        topEl.closest('.navbar-collapse.show, .navbar-collapse.collapsing') ||
        topEl.closest('#mainHeader') && !topEl.closest('.ai-float-btn, .whatsapp-float-btn')
      );

      return {
        inViewport: rect.left < window.innerWidth && rect.right > 0 && rect.top < window.innerHeight && rect.bottom > 0,
        display: cs.display,
        visibility: cs.visibility,
        opacity: cs.opacity,
        zIndex: cs.zIndex,
        pointerEvents: cs.pointerEvents,
        rect: { x: Math.round(rect.x), y: Math.round(rect.y), w: Math.round(rect.width), h: Math.round(rect.height) },
        topElement: topEl ? topEl.tagName + (topEl.id ? '#' + topEl.id : '') : 'none',
        isBehindBackdrop: isBehindBackdrop,
        elementFromPoint: topEl ? topEl.outerHTML.substring(0, 80) : 'none'
      };
    }

    results.aiFloat = getInfo(aiBtn);
    results.whatsapp = getInfo(waBtn);
    
    // Check menu state
    const nav = document.getElementById('navbarNav');
    results.menuState = nav ? {
      classes: nav.className,
      show: nav.classList.contains('show'),
      collapsing: nav.classList.contains('collapsing')
    } : 'NOT FOUND';
    
    // Backdrop
    const bd = document.querySelector('.nav-backdrop, .menu-backdrop, [class*=backdrop]');
    results.backdrop = bd ? {
      display: getComputedStyle(bd).display,
      zIndex: getComputedStyle(bd).zIndex,
      width: getComputedStyle(bd).width,
      height: getComputedStyle(bd).height,
      opacity: getComputedStyle(bd).opacity,
    } : null;

    return results;
  });

  console.log(JSON.stringify(info, null, 2));
  await page.screenshot({ path: 'testing/visual_tests/menu_open_icons.png', fullPage: false });
  console.log('Screenshot saved: testing/visual_tests/menu_open_icons.png');

  await browser.close();
})();
