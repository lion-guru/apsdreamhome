// Admin property approval smoke test: login (via test hook) and attempt to approve a pending property
const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  // Use test login path to bypass manual login if configured
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'networkidle' }).catch(() => {});
  // Navigate to admin user-properties
  await page.goto('http://localhost/apsdreamhome/admin/user-properties', { waitUntil: 'networkidle' }).catch(() => {});
  // Try to find a Verify/Approve action button in the first row
  // Try to find a Verify/Approve action button in the first row (broader search)
  const verifyBtn = await page.locator("text=Verify");
  const approveBtn = await page.locator("text=Approve");
  if ((await verifyBtn.count()) > 0) {
    await verifyBtn.first().click().catch(() => {});
    await page.waitForTimeout(1000).catch(() => {});
    console.log('Admin property verify action attempted (if button present).');
  } else if ((await approveBtn.count()) > 0) {
    await approveBtn.first().click().catch(() => {});
    await page.waitForTimeout(1000).catch(() => {});
    console.log('Admin property approve action attempted (if button present).');
  } else {
    console.log('No Verify/Approve action found on admin user-properties (no pending items or different UI).');
  }
  // Save a quick screenshot for verification
  await page.screenshot({ path: 'testing/visual_tests/admin_property_approval.png', fullPage: true }).catch(() => {});
  await browser.close();
})();
