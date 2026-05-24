const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load', timeout: 15000 });

  const urls = [
    '/admin/reports/commission',
    '/admin/leads/sources',
    '/admin/mlm-settings/evaluate',
    '/admin/mlm-settings/associate-progress',
  ];

  for (const url of urls) {
    try {
      await page.goto('http://localhost/apsdreamhome' + url, { waitUntil: 'load', timeout: 15000 });
      const title = await page.title();
      const body = await page.evaluate(() => document.body.innerText.substring(0, 400));
      const hasError500 = body.includes('500 - Internal Server Error');
      console.log('\n=== ' + url + ' === HTTP 500: ' + hasError500);
      console.log('Title: ' + title);
      console.log('Body: ' + body.substring(0, 300));
    } catch(e) {
      console.log('\n=== ' + url + ' === ERROR: ' + e.message);
    }
  }
  await browser.close();
})();
