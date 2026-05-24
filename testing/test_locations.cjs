const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load', timeout: 15000 });
  
  for (const url of ['/admin/locations/districts', '/admin/locations/colonies']) {
    try {
      await page.goto('http://localhost/apsdreamhome' + url, { waitUntil: 'load', timeout: 15000 });
      const title = await page.title();
      const body = await page.evaluate(() => document.body.innerText.substring(0, 300));
      console.log('\n=== ' + url + ' ===');
      console.log('Title: ' + title);
      console.log('Body: ' + body);
    } catch(e) {
      console.log('\n=== ' + url + ' === ERROR: ' + e.message);
    }
  }
  await browser.close();
})();
