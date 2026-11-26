const { chromium } = require('playwright');

const pages = [
  '/', '/properties', '/properties/11336', '/projects', '/colonies',
  '/offers', '/contact', '/emi-calculator', '/tools-hub', '/blog', '/careers',
  '/about', '/services', '/rera-lookup', '/plots/layout', '/emi-schedule', '/nonexistent-page-xyz'
];

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });

  async function audit(viewport, label) {
    const page = await browser.newPage({ viewport });
    console.log(`\n===== ${label} (${viewport.width}x${viewport.height}) =====`);
    for (const path of pages) {
      const consoleErrs = [];
      const failedReqs = [];
      page.removeAllListeners('console');
      page.removeAllListeners('requestfailed');
      page.on('console', m => { if (m.type() === 'error') consoleErrs.push(m.text().slice(0, 120)); });
      page.on('requestfailed', r => failedReqs.push(r.url().slice(0, 90) + ' :: ' + (r.failure()?.errorText || 'failed')));
      try {
        const resp = await page.goto('http://localhost/apsdreamhome' + path, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForTimeout(1400);
        const m = await page.evaluate(() => {
          const imgs = [...document.images];
          const broken = imgs.filter(i => i.complete && i.naturalWidth === 0).map(i => (i.currentSrc || i.src).split('/').pop());
          const overflow = document.documentElement.scrollWidth > window.innerWidth + 2;
          return {
            imgs: imgs.length,
            broken: broken.length,
            overflow,
            sw: document.documentElement.scrollWidth,
            iw: window.innerWidth,
            title: (document.title || '').slice(0, 60)
          };
        });
        console.log(`${path}  HTTP ${resp.status()}  imgs:${m.imgs} broken:${m.broken} overflow:${m.overflow ? 'YES sw='+m.sw+'>iw='+m.iw : 'no'}`);
        if (consoleErrs.length) console.log('    console errors: ' + consoleErrs.slice(0, 3).join(' | '));
        if (failedReqs.length) console.log('    failed reqs   : ' + failedReqs.slice(0, 3).join(' | '));
        if (m.broken > 0) console.log('    broken imgs   : ' + m.broken);
      } catch (e) {
        console.log(`${path}  ERR :: ${String(e).slice(0, 120)}`);
      }
    }
    await page.close();
  }

  await audit({ width: 1366, height: 900 }, 'DESKTOP');
  await audit({ width: 375, height: 800 }, 'MOBILE');

  await browser.close();
})().catch(e => { console.error('FATAL', e); process.exit(1); });
