// Basic Playwright end-to-end user flow (register -> login) skeleton
const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  // Try registration page
  await page.goto('http://localhost/apsdreamhome/register', { waitUntil: 'networkidle' }).catch(() => {});
  // If registration form exists, fill minimal fields
  try {
    if (await page.locator('input[name="name"]').count() > 0) {
      await page.fill('input[name="name"]', 'Auto Tester');
      await page.fill('input[name="email"]', 'autotester@example.com');
      await page.fill('input[name="phone"]', '9999999999');
      await page.fill('input[name="password"]', 'Test@123');
      await page.fill('input[name="confirm_password"]', 'Test@123');
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1500);
    }
  } catch (e) {
    // Ignore if fields aren't present
  }

  // Try to login with the registered user if registration succeeded
  try {
    await page.goto('http://localhost/apsdreamhome/login', { waitUntil: 'networkidle' }).catch(() => {});
    if (await page.locator('input[name="email"]').count() > 0) {
      await page.fill('input[name="email"]', 'autotester@example.com');
      await page.fill('input[name="password"]', 'Test@123');
      const loginBtn = await page.locator('button[type="submit"]');
      if ((await loginBtn.count()) > 0) await loginBtn.first().click();
      await page.waitForTimeout(1000);
    }
    // Post-login: attempt to post a property if possible
    await page.goto('http://localhost/apsdreamhome/list-property', { waitUntil: 'networkidle' }).catch(() => {});
    if ((await page.locator('input[name="name"]').count()) > 0) {
      await page.fill('input[name="name"]', 'Auto Property');
      if ((await page.locator('input[name="phone"]').count()) > 0) await page.fill('input[name="phone"]', '9999999999');
      if ((await page.locator('input[name="email"]').count()) > 0) await page.fill('input[name="email"]', 'autotester@example.com');
      if ((await page.locator('input[name="price"]').count()) > 0) await page.fill('input[name="price"]', '1000000');
      if ((await page.locator('input[name="location"]').count()) > 0) await page.fill('input[name="location"]', 'Test Location');
      if ((await page.locator('input[name="area_sqft"]').count()) > 0) await page.fill('input[name="area_sqft"]', '1000');
      if ((await page.locator('textarea[name="description"]').count()) > 0) await page.fill('textarea[name="description"]', 'Auto posted property from end-to-end test.');
      const submitBtn = await page.locator('button[type="submit"]');
      if ((await submitBtn.count()) > 0) await submitBtn.first().click();
    }
  } catch (e) {
    // Ignore if steps aren't available in current build
  }

  await browser.close();
})();
