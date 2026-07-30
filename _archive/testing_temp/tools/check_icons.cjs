const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const viewports = [
    { w: 390, h: 844, name: 'mobile' },
    { w: 768, h: 1024, name: 'tablet' },
    { w: 1024, h: 768, name: 'tablet_landscape' },
    { w: 1920, h: 1080, name: 'desktop' },
  ];

  for (const vp of viewports) {
    const page = await browser.newPage({ viewport: { width: vp.w, height: vp.h } });
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'load' });
    await page.waitForTimeout(2000);

    // Screenshot
    await page.screenshot({ 
      path: 'testing/visual_tests/icons_' + vp.name + '.png',
      fullPage: false 
    });

    const info = await page.evaluate(() => {
      const results = {};
      
      const aiBtn = document.querySelector('.ai-float-btn');
      const waBtn = document.querySelector('.whatsapp-float-btn');
      const container = document.querySelector('.ai-chatbot-container');
      
      function getInfo(el, name) {
        if (!el) return name + ': NOT FOUND in DOM';
        const cs = getComputedStyle(el);
        const rect = el.getBoundingClientRect();
        const style = {
          position: cs.position,
          display: cs.display,
          visibility: cs.visibility,
          opacity: cs.opacity,
          zIndex: cs.zIndex,
          bottom: cs.bottom,
          left: cs.left,
          right: cs.right,
          rect: { x: Math.round(rect.x), y: Math.round(rect.y), w: Math.round(rect.width), h: Math.round(rect.height) }
        };
        // Check if in viewport
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const inView = rect.left < vw && rect.right > 0 && rect.top < vh && rect.bottom > 0;
        style.inViewport = inView;
        // Check for overlapping elements
        const topEl = document.elementFromPoint(rect.left + rect.width/2, rect.top + rect.height/2);
        style.topElement = topEl ? topEl.tagName + (topEl.id ? '#' + topEl.id : '') + (topEl.className ? '.' + topEl.className.substring(0, 30) : '') : 'none';
        style.isTopElement = topEl === el;
        return style;
      }

      results.aiFloat = getInfo(aiBtn, 'ai-float');
      results.whatsapp = getInfo(waBtn, 'whatsapp');
      results.container = container ? {
        position: getComputedStyle(container).position,
        zIndex: getComputedStyle(container).zIndex,
        bottom: getComputedStyle(container).bottom,
        left: getComputedStyle(container).left,
      } : 'NOT FOUND';
      
      // Check if menu is open
      results.menuOpen = document.querySelector('#mainHeader.menu-open, header.menu-open') !== null;
      
      // Check for backdrop
      const bd = document.querySelector('.nav-backdrop, .menu-backdrop, .header-backdrop, [class*=backdrop]');
      results.backdrop = bd ? {
        display: getComputedStyle(bd).display,
        zIndex: getComputedStyle(bd).zIndex,
        opacity: getComputedStyle(bd).opacity,
      } : null;

      results.viewport = { w: window.innerWidth, h: window.innerHeight };
      return results;
    });

    console.log('=== ' + vp.name + ' (' + vp.w + 'x' + vp.h + ') ===');
    console.log('AI Float Icon: ' + (info.aiFloat.inViewport ? 'VISIBLE' : 'HIDDEN') + ' | pos=' + info.aiFloat.position + ' bottom=' + info.aiFloat.bottom + ' left=' + info.aiFloat.left + ' z=' + info.aiFloat.zIndex + ' rect=(' + info.aiFloat.rect.x + ',' + info.aiFloat.rect.y + ' ' + info.aiFloat.rect.w + 'x' + info.aiFloat.rect.h + ') topEl=' + info.aiFloat.topElement);
    console.log('WhatsApp Icon: ' + (info.whatsapp.inViewport ? 'VISIBLE' : 'HIDDEN') + ' | pos=' + info.whatsapp.position + ' bottom=' + info.whatsapp.bottom + ' right=' + info.whatsapp.right + ' z=' + info.whatsapp.zIndex + ' rect=(' + info.whatsapp.rect.x + ',' + info.whatsapp.rect.y + ' ' + info.whatsapp.rect.w + 'x' + info.whatsapp.rect.h + ') topEl=' + info.whatsapp.topElement);
    if (info.backdrop) console.log('Backdrop present: z=' + info.backdrop.zIndex + ' display=' + info.backdrop.display);
    console.log('Menu open: ' + info.menuOpen);
    console.log('');
    
    await page.close();
  }
  
  await browser.close();
  console.log('Screenshots saved to testing/visual_tests/icons_*.png');
})();
