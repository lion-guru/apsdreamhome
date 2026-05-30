const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  
  // Deep check on mobile homepage - is chatbot visible?
  const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
  await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'load', timeout: 15000 });
  await page.waitForTimeout(2000);

  const result = await page.evaluate(() => {
    const aiBtn = document.querySelector('.ai-float-btn');
    const waBtn = document.querySelector('.whatsapp-float-btn');
    const aiContainer = document.querySelector('.ai-chatbot-container');
    
    function deep(el, name) {
      if (!el) return { found: false, msg: name + ' not in DOM' };
      const cs = getComputedStyle(el);
      const rect = el.getBoundingClientRect();
      
      // Check actual visibility
      const inViewport = rect.left < window.innerWidth && rect.right > 0 && 
                         rect.top < window.innerHeight && rect.bottom > 0;
      
      // What's at the center of this element?
      const cx = Math.round(rect.left + rect.width/2);
      const cy = Math.round(rect.top + rect.height/2);
      let topEl = null;
      try { topEl = document.elementFromPoint(cx, cy); } catch(e) {}
      
      // What's at the element's position?
      let topAtCorner = null;
      try { topAtCorner = document.elementFromPoint(Math.round(rect.left)+2, Math.round(rect.top)+2); } catch(e) {}
      
      return {
        found: true,
        inViewport,
        tagName: el.tagName,
        id: el.id,
        display: cs.display,
        visibility: cs.visibility,
        opacity: cs.opacity,
        position: cs.position,
        zIndex: cs.zIndex,
        pointerEvents: cs.pointerEvents,
        bottom: cs.bottom,
        left: cs.left,
        right: cs.right,
        rect: `${Math.round(rect.x)},${Math.round(rect.y)} ${Math.round(rect.w)}x${Math.round(rect.h)}`,
        vw: window.innerWidth,
        vh: window.innerHeight,
        elAtCenter: topEl ? (topEl === el ? 'SELF' : topEl.tagName + (topEl.id?'#'+topEl.id:'') + '.' + (topEl.className||'').substring(0,40)) : 'error',
        elAtCorner: topAtCorner ? (topAtCorner === el ? 'SELF' : topAtCorner.tagName + (topAtCorner.id?'#'+topAtCorner.id:'') + '.' + (topAtCorner.className||'').substring(0,40)) : 'error',
      };
    }

    return {
      ai: deep(aiBtn, 'ai-float-btn'),
      wa: deep(waBtn, 'whatsapp-float-btn'),
      container: aiContainer ? { position: getComputedStyle(aiContainer).position, zIndex: getComputedStyle(aiContainer).zIndex } : 'no container',
      bodyOverflow: getComputedStyle(document.body).overflow,
      htmlOverflow: getComputedStyle(document.documentElement).overflow,
      scrollbarWidth: window.innerWidth - document.documentElement.clientWidth,
    };
  });

  console.log(JSON.stringify(result, null, 2));
  await page.screenshot({ path: 'testing/visual_tests/mobile_homepage_icons_deep.png', fullPage: false });
  console.log('Screenshot saved');
  await browser.close();
})();
