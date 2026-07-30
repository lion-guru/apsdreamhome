const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load', timeout: 15000 });

  const urls = [
    '/admin/reports/commission', '/admin/leads/sources',
    '/admin/mlm-settings/evaluate', '/admin/mlm-settings/associate-progress',
    '/admin/locations/districts', '/admin/locations/colonies',
  ];

  let allOk = true;
  for (const url of urls) {
    try {
      await page.goto('http://localhost/apsdreamhome' + url, { waitUntil: 'load', timeout: 15000 });
      const status = await page.evaluate(() => document.title);
      const has500 = status.includes('500') || (await page.evaluate(() => document.body.innerText.includes('Internal Server Error')));
      console.log((has500 ? 'FAIL' : 'OK') + ': ' + url + ' (title: ' + status.substring(0, 40) + ')');
      if (has500) allOk = false;
    } catch(e) { console.log('FAIL: ' + url + ' (' + e.message.substring(0, 60) + ')'); allOk = false; }
  }
  console.log('\n' + (allOk ? 'ALL 6 FIXED!' : 'SOME STILL FAILING'));
  await browser.close();
})();
