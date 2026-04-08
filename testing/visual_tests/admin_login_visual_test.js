// Optional visual test for admin login page
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.setViewportSize({ width: 1280, height: 800 });
  await page.goto('http://localhost/apsdreamhome/admin/login', { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'testing/visual_tests/admin_login.png', fullPage: false });
  console.log('Saved screenshot: testing/visual_tests/admin_login.png');
  await browser.close();
})();
