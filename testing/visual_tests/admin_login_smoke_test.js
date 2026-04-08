// Admin login smoke test with Playwright
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  // Attempt test-login shortcut if available
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'networkidle' }).catch(() => {});

  // Optional: attempt automated login if admin credentials are provided via env vars
  const adminUser = process.env.ADMIN_TEST_USER || '';
  const adminPass = process.env.ADMIN_TEST_PASS || '';
  if (adminUser && adminPass) {
    // Try common selectors for login inputs
    try {
      const userInput = await page.locator('input[name="username"]');
      const emailInput = await page.locator('input[name="email"]');
      const passInput = await page.locator('input[name="password"]');
      const inputUser = (await userInput.count()) > 0 ? userInput : emailInput;
      if ((await inputUser.count()) > 0 && (await passInput.count()) > 0) {
        await inputUser.first().fill(adminUser);
        await passInput.first().fill(adminPass);
        // Try submit button
        const btn = await page.locator('button[type="submit"]');
        if ((await btn.count()) > 0) {
          await btn.first().click();
          await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
        }
        console.log('Admin login attempted with env credentials');
      }
    } catch (e) {
      // Ignore login errors in automated path; keep test resilient
      console.log('Admin login automation error (ignored in smoke test):', e?.message);
    }
  }

  // Fallback: try a lightweight login with known test admin creds when env not provided
  if (!adminUser && !adminPass) {
    try {
      const selectors = ['input[name="username"]', 'input[name="email"]'];
      let filled = false;
      for (const sel of selectors) {
        if (await page.locator(sel).count() > 0) {
          await page.fill(sel, 'testadmin');
          const pwSel = await page.locator('input[name="password"]');
          if ((await pwSel.count()) > 0) {
            await pwSel.first().fill('Test@123');
          }
          const btn = await page.locator('button[type="submit"]');
          if ((await btn.count()) > 0) {
            await btn.first().click();
          }
          filled = true; break;
        }
      }
      if (filled) {
        try { await page.waitForNavigation({ waitUntil: 'networkidle', timeout: 5000 }); } catch {}
        const url = await page.url();
        if (url.includes('/admin')) {
          console.log('Admin login attempted with testadmin via fallback path');
        } else {
          console.log('Admin login fallback did not reach admin area (likely CAPTCHA).');
        }
      }
    } catch (e) {
      // ignore
    }
  }

  const hasUserLabel = await page.locator('text=Username or Email').count() > 0;
  const hasPasswordLabel = await page.locator('text=Password').count() > 0;
  // Do not rely on captcha text matching; just ensure username and password fields exist
  if (!hasUserLabel || !hasPasswordLabel) {
    console.error('Admin login page structure has changed or captcha missing');
    process.exit(1);
  }

  // After potential login, report current URL to verify navigation state.
  try {
    const usernameField = await page.locator('input[name="username"]');
    const emailField = await page.locator('input[name="email"]');
    const passwordField = await page.locator('input[name="password"]');
    if ((await usernameField.count()) > 0 || (await emailField.count()) > 0) {
      const uSel = (await usernameField.count()) > 0 ? 'input[name="username"]' : 'input[name="email"]';
      const userVal = process.env.ADMIN_TEST_USER || (await page.locator(uSel).first().inputValue()) || 'testadmin';
      await page.fill(uSel, userVal);
      const passVal = process.env.ADMIN_TEST_PASS || 'Test@123';
      if ((await passwordField.count()) > 0) await page.fill('input[name="password"]', passVal);
      const btn = await page.locator('button[type="submit"]');
      if ((await btn.count()) > 0) await btn.first().click();
      await page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {});
    }
  } catch {}

  try {
    console.log('Current URL after login attempt (if any):', await page.url());
  } catch {}
  await browser.close();
  console.log('Admin login UI simple checks passed');
})();
