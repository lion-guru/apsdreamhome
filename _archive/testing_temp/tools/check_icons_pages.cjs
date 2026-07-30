const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  
  // Test on different pages
  const pages = [
    { url: '/', name: 'home' },
    { url: '/properties', name: 'properties' },
    { url: '/contact', name: 'contact' },
    { url: '/login', name: 'login' },
  ];

  const vps = [
    { w: 390, h: 844, name: 'mobile' },
    { w: 768, h: 1024, name: 'tablet' },
  ];

  for (const vp of vps) {
    for (const p of pages) {
      const page = await browser.newPage({ viewport: { width: vp.w, height: vp.h } });
      await page.goto(`http://localhost/apsdreamhome${p.url}`, { waitUntil: 'load', timeout: 15000 });
      await page.waitForTimeout(1500);

      const info = await page.evaluate(() => {
        const aiBtn = document.querySelector('.ai-float-btn');
        const waBtn = document.querySelector('.whatsapp-float-btn');
        
        function check(el) {
          if (!el) return { found: false };
          const cs = getComputedStyle(el);
          const rect = el.getBoundingClientRect();
          const vw = window.innerWidth;
          const vh = window.innerHeight;
          const inView = rect.left < vw && rect.right > 0 && rect.top < vh && rect.bottom > 0;
          
          // Check what's at the icon's center point
          const cx = rect.left + rect.width/2;
          const cy = rect.top + rect.height/2;
          const topEl = document.elementFromPoint(cx, cy);
          
          return {
            found: true,
            inView,
            display: cs.display,
            visibility: cs.visibility,
            opacity: cs.opacity,
            pointerEvents: cs.pointerEvents,
            position: cs.position,
            zIndex: cs.zIndex,
            rect: `${Math.round(rect.x)},${Math.round(rect.y)} ${Math.round(rect.w)}x${Math.round(rect.h)}`,
            isClickable: topEl === el || el.contains(topEl),
            topElTag: topEl ? topEl.tagName + (topEl.id ? '#' + topEl.id : '') : 'none'
          };
        }
        
        return { ai: check(aiBtn), wa: check(waBtn), vw: window.innerWidth, vh: window.innerHeight };
      });

      const ai = info.ai.found ? (info.ai.inView && info.ai.isClickable ? 'SHOWS' : 'HIDDEN') : 'MISSING';
      const wa = info.wa.found ? (info.wa.inView && info.wa.isClickable ? 'SHOWS' : 'HIDDEN') : 'MISSING';
      const reason = [];
      if (info.ai.found && (!info.ai.inView || !info.ai.isClickable)) reason.push(`AI: inView=${info.ai.inView} clickable=${info.ai.isClickable} pos=${info.ai.position} z=${info.ai.zIndex} rect=${info.ai.rect} top=${info.ai.topElTag}`);
      if (info.wa.found && (!info.wa.inView || !info.wa.isClickable)) reason.push(`WA: inView=${info.wa.inView} clickable=${info.wa.isClickable} pos=${info.wa.position} z=${info.wa.zIndex} rect=${info.wa.rect} top=${info.wa.topElTag}`);

      console.log(`${vp.name} ${p.url}: AI=${ai} WA=${wa} | viewport=${info.vw}x${info.vh}`);
      if (reason.length) console.log('  -> ' + reason.join(' | '));
      
      await page.close();
    }
  }
  await browser.close();
})();
