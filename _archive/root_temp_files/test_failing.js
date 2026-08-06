const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1');
  await page.waitForURL('**/admin/erp');

  const urls = [
    '/admin/commission/recalculations',
    '/admin/department-requests',
    '/admin/finance/emi-auto-pay',
    '/property-comparison',
    '/admin/ai_settings'
  ];

  for (const path of urls) {
    try {
      const response = await page.goto('http://localhost/apsdreamhome' + path, {
        waitUntil: 'domcontentloaded', timeout: 15000
      });
      console.log(response.status() + ' ' + path);
    } catch (e) {
      console.log('TIMEOUT ' + path);
    }
  }

  await browser.close();
})();
