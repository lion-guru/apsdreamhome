const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });

  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load' });
  await page.waitForTimeout(2000);

  const targets = ['/admin/bookings', '/admin/projects', '/admin/services'];

  for (const t of targets) {
    console.log('=== ' + t + ' ===');
    await page.goto('http://localhost/apsdreamhome' + t, { waitUntil: 'load', timeout: 15000 });
    await page.waitForTimeout(1000);

    const wide = await page.evaluate(() => {
      const all = document.querySelectorAll('*');
      const vw = document.documentElement.clientWidth;
      const offenders = [];
      for (const el of all) {
        const rect = el.getBoundingClientRect();
        if (rect.width > vw + 5) {
          offenders.push({
            tag: el.tagName,
            id: el.id,
            cls: el.className,
            width: Math.round(rect.width),
            vw: vw,
            text: (el.innerText || '').substring(0, 60).replace(/\s+/g, ' ')
          });
          if (offenders.length >= 5) break;
        }
      }
      return offenders;
    });

    for (const o of wide) {
      console.log('  W: ' + o.width + 'px > vw:' + o.vw + 'px | <' + o.tag + (o.id ? ' #' + o.id : '') + ' class=' + (o.cls || '') + '> | text: "' + o.text + '"');
    }
    if (wide.length === 0) console.log('  No wide elements found');
  }

  await browser.close();
})();
