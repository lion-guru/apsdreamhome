const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  
  // Login
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load', timeout: 15000 });

  const urls = [
    // HTTP 500 routes
    '/admin/reports/commission',
    '/admin/leads/sources',
    '/admin/mlm-settings/evaluate',
    '/admin/mlm-settings/associate-progress',
    '/admin/locations/districts',
    '/admin/locations/colonies',
    // Pages with 500 text
    '/admin/plots',
    '/admin/deals',
    '/admin/user-properties',
    '/admin/plot-costs',
    '/admin/properties',
    '/admin/mlm-settings/levels',
    '/admin/bookings/create',
    '/admin/colonies',
  ];

  for (const url of urls) {
    try {
      await page.goto('http://localhost/apsdreamhome' + url, { waitUntil: 'load', timeout: 15000 });
      const status = await page.evaluate(() => document.title);
      const body = await page.evaluate(() => document.body.innerText.substring(0, 500));
      console.log('\n=== ' + url + ' ===');
      console.log('Title: ' + status);
      console.log('Body[0-500]:');
      console.log(body);
    } catch(e) {
      console.log('\n=== ' + url + ' === ERROR: ' + e.message);
    }
  }

  await browser.close();
})();
