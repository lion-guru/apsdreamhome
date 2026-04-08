// Admin property workflow smoke test (end-to-end style, lightweight)
const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();

  // Navigate to admin login and attempt a login if env creds exist
  await page.goto('http://localhost/apsdreamhome/admin/login', { waitUntil: 'networkidle' });
  const adminUser = process.env.ADMIN_TEST_USER || '';
  const adminPass = process.env.ADMIN_TEST_PASS || '';
  if (adminUser && adminPass) {
    const userSel = (await page.locator('input[name="username"]').count() > 0) ? 'input[name="username"]' : 'input[name="email"]';
    await page.fill(userSel, adminUser);
    await page.fill('input[name="password"]', adminPass);
    const btn = await page.locator('button[type="submit"]');
    if ((await btn.count()) > 0) await btn.first().click();
    try { await page.waitForNavigation({ waitUntil: 'networkidle', timeout: 5000 }); } catch {}
  }

  // Go to user properties and attempt a simple check
  await page.goto('http://localhost/apsdreamhome/admin/user-properties', { waitUntil: 'networkidle' }).catch(() => {});
  const hasHeader = (await page.locator('text="User Properties"').count()) > 0;
  if (hasHeader) {
    console.log('Admin User Properties loaded (authenticated or publicly accessible in this config).');
  } else {
    console.log('Admin User Properties not loaded (login may be required).');
  }

  // Try to click a potential action button if present
  const actionBtn = await page.locator('text=/Verify|Approve|Action/');
  if ((await actionBtn.count()) > 0) {
    await actionBtn.first().click().catch(() => {});
  }

  await browser.close();
})();
