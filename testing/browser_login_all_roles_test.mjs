import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE_URL = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = path.resolve('_screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

const ROLES_TO_TEST = [
  {
    roleName: 'Super Admin',
    email: 'admin@apsdreamhome.com',
    password: 'Aps@2026',
    expectedPath: '/admin/erp',
    screenshotName: 'login_01_super_admin_dashboard.png'
  },
  {
    roleName: 'Associate Partner',
    email: 'rajesh.associate@apsdreamhome.test',
    password: 'Aps@2026',
    expectedPath: '/associate/dashboard',
    screenshotName: 'login_02_associate_dashboard.png'
  },
  {
    roleName: 'Freelancer Agent',
    email: 'sanjay.freelancer@apsdreamhome.test',
    password: 'Aps@2026',
    expectedPath: '/agent/dashboard',
    screenshotName: 'login_03_freelancer_agent_dashboard.png'
  },
  {
    roleName: 'Employee Agent',
    email: 'pooja.employee@apsdreamhome.test',
    password: 'Aps@2026',
    expectedPath: '/agent/dashboard',
    screenshotName: 'login_04_employee_agent_dashboard.png'
  },
  {
    roleName: 'Employee / Staff',
    email: 'emp_test@apsdreamhome.test',
    password: 'Aps@2026',
    expectedPath: '/employee/dashboard',
    screenshotName: 'login_05_employee_dashboard.png'
  },
  {
    roleName: 'Customer / Buyer',
    email: 'customer_test@apsdreamhome.test',
    password: 'Aps@2026',
    expectedPath: '/user/dashboard',
    screenshotName: 'login_06_customer_dashboard.png'
  }
];

async function runTest() {
  console.log('🚀 Launching Google Chrome for All-Role Login Testing...');
  const browser = await chromium.launch({
    channel: 'chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const context = await browser.newContext({
    viewport: { width: 1366, height: 768 }
  });
  const page = await context.newPage();

  const results = [];

  for (const testCase of ROLES_TO_TEST) {
    console.log(`\n--------------------------------------------------`);
    console.log(`🔑 Testing Login for Role: ${testCase.roleName} (${testCase.email})`);

    try {
      // 1. Clear cookies/session
      await context.clearCookies();
      await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
      await page.waitForTimeout(300);

      // 2. Go to login page
      await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await page.waitForTimeout(300);

      // 3. Fill Credentials
      await page.fill('input[name="identity"]', testCase.email);
      await page.fill('input[name="password"]', testCase.password);

      // 4. Fill Captcha if present
      const captchaInput = await page.$('input[name="captcha_code"]');
      if (captchaInput) {
        await captchaInput.fill('123456');
      }

      // 5. Click Sign In
      console.log('  Submitting login form...');
      await page.click('button[type="submit"]', { timeout: 10000 }).catch(() => {});

      // Wait for navigation away from /login
      await page.waitForURL(url => !url.href.includes('/login') && !url.href.includes('/auth/login'), { timeout: 15000 }).catch(() => {});
      await page.waitForLoadState('domcontentloaded').catch(() => {});
      await page.waitForTimeout(1000);

      const currentUrl = page.url();
      let pageTitle = '';
      try { pageTitle = await page.title(); } catch (e) {}
      let pageContent = '';
      try { pageContent = await page.content(); } catch (e) {}

      console.log(`  Current URL: ${currentUrl}`);
      console.log(`  Page Title:  ${pageTitle}`);

      // Check if "This controller is no longer in use." is present
      const hasDeadControllerError = pageContent.includes('This controller is no longer in use');
      const isExpectedDashboard = currentUrl.includes(testCase.expectedPath);

      // Screenshot the dashboard
      const ssPath = path.join(SCREENSHOT_DIR, testCase.screenshotName);
      await page.screenshot({ path: ssPath, fullPage: false });
      console.log(`  📸 Screenshot saved: ${testCase.screenshotName}`);

      if (hasDeadControllerError) {
        console.error(`  ❌ FAILED: Page contains 'This controller is no longer in use.'`);
        results.push({ ...testCase, status: 'FAILED', reason: 'Dead controller error' });
      } else if (!isExpectedDashboard) {
        // Check for error alert on page
        const alertError = await page.$eval('.alert-error', el => el.textContent.trim()).catch(() => null);
        console.error(`  ❌ FAILED: Did not reach expected dashboard ${testCase.expectedPath}. URL is ${currentUrl}. Error: ${alertError}`);
        results.push({ ...testCase, status: 'FAILED', reason: alertError || `Wrong URL: ${currentUrl}` });
      } else {
        console.log(`  ✅ SUCCESS: Logged in and redirected cleanly to ${testCase.expectedPath}`);
        results.push({ ...testCase, status: 'SUCCESS', targetUrl: currentUrl });
      }

    } catch (err) {
      console.error(`  ❌ EXCEPTION for ${testCase.roleName}: ${err.message}`);
      results.push({ ...testCase, status: 'ERROR', error: err.message });
    }
  }

  await browser.close();

  console.log('\n==================================================');
  console.log('📊 ALL-ROLE LOGIN TEST SUMMARY:');
  console.log('==================================================');
  let allPass = true;
  for (const res of results) {
    const icon = res.status === 'SUCCESS' ? '✅' : '❌';
    console.log(`${icon} [${res.roleName}] -> Status: ${res.status} | Target: ${res.expectedPath} (Screenshot: ${res.screenshotName})`);
    if (res.status !== 'SUCCESS') allPass = false;
  }
  console.log('==================================================\n');

  if (!allPass) {
    process.exit(1);
  } else {
    process.exit(0);
  }
}

runTest().catch(e => {
  console.error('Fatal test error:', e);
  process.exit(1);
});
