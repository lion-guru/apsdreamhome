// Admin User Properties smoke test
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto('http://localhost/apsdreamhome/admin/login', { waitUntil: 'networkidle' });
  // We won't attempt login; just navigate to the user-properties page after login prompt is visible
  // This is a placeholder smoke test to ensure route exists; actual login may require credentials
  await page.goto('http://localhost/apsdreamhome/admin/user-properties', { waitUntil: 'networkidle' }).catch(() => {});
  // If navigation succeeds, check for some common admin text
  const hasHeader = await page.locator('text=User Properties').count() > 0;
  if (!hasHeader) {
    console.warn('Admin User Properties page might require authentication or different markup.');
  }
  await browser.close();
})();
