// Admin User Properties access test (no login) with Playwright
const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto('http://localhost/apsdreamhome/admin/user-properties', { waitUntil: 'networkidle' }).catch(() => {});
  const pageContent = await page.content();
  const listingPresent = pageContent.includes('User Properties') || pageContent.includes('<table') || pageContent.includes('<th>ID</th>');
  if (listingPresent) {
    console.log('Admin User Properties page loaded (listing detected).');
  } else {
    console.log('Admin User Properties listing not clearly detected; login may be required or UI changed.');
  }
  await browser.close();
})();
