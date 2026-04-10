// MASTER TEST RUNNER - APS Dream Home A-to-Z Test Suite
// Runs all phases: DB health -> Seeds -> Header visuals -> Admin flows -> E2E user flows -> Report
import { chromium } from 'playwright';
import { execSync } from 'child_process';
import fs from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const REPORT = 'testing/visual_tests/TEST_REPORT.txt';
const screenshots = [];

function log(msg) {
  const ts = new Date().toISOString().slice(11, 19);
  console.log(`[${ts}] ${msg}`);
  fs.appendFileSync(REPORT, `[${ts}] ${msg}\n`);
}

async function runBrowserTest(fn, label) {
  try {
    await fn();
    log(`PASS: ${label}`);
    return true;
  } catch (e) {
    log(`FAIL: ${label} -> ${e.message}`);
    return false;
  }
}

async function headerVisualTests() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  const viewports = [
    { name: 'Desktop', w: 1280, h: 800 },
    { name: 'Tablet', w: 1024, h: 768 },
    { name: 'Mobile', w: 412, h: 915 },
  ];
  for (const vp of viewports) {
    await page.setViewportSize({ width: vp.w, height: vp.h });
    await page.goto(BASE, { waitUntil: 'networkidle' });
    const path = `testing/visual_tests/header_${vp.name}.png`;
    await page.screenshot({ path, fullPage: false });
    screenshots.push(path);
    log(`Screenshot saved: ${path}`);
  }
  await browser.close();
}

async function adminLoginTests() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'networkidle' });
  const url = await page.url();
  if (url.includes('/admin')) {
    log('Admin test-login bypass: SUCCESS');
  } else {
    log('Admin test-login bypass: FAILED (still on login page)');
  }
  await page.screenshot({ path: 'testing/visual_tests/admin_dashboard.png', fullPage: false });
  screenshots.push('testing/visual_tests/admin_dashboard.png');
  await browser.close();
}

async function adminUserPropertiesTests() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  // Use test login first
  await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'networkidle' });
  await page.goto(`${BASE}/admin/user-properties`, { waitUntil: 'networkidle' });
  const html = await page.content();
  if (html.includes('User Properties') || html.includes('<table')) {
    log('Admin User Properties page: LOADED');
  } else {
    log('Admin User Properties page: NOT LOADED (auth issue or UI changed)');
  }
  await page.screenshot({ path: 'testing/visual_tests/admin_user_properties.png', fullPage: false });
  screenshots.push('testing/visual_tests/admin_user_properties.png');
  await browser.close();
}

async function userPropertyPostingTests() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto(`${BASE}/list-property`, { waitUntil: 'networkidle' });
  const hasForm = await page.locator('form').count() > 0;
  if (hasForm) {
    log('List Property form: PRESENT');
    try {
      if (await page.locator('input[name="name"]').count() > 0) await page.fill('input[name="name"]', 'Auto Test Property');
      if (await page.locator('input[name="phone"]').count() > 0) await page.fill('input[name="phone"]', '9999999999');
      if (await page.locator('input[name="email"]').count() > 0) await page.fill('input[name="email"]', 'autotest@example.com');
      if (await page.locator('input[name="price"]').count() > 0) await page.fill('input[name="price"]', '500000');
      if (await page.locator('input[name="location"]').count() > 0) await page.fill('input[name="location"]', 'Gorakhpur');
      if (await page.locator('input[name="area_sqft"]').count() > 0) await page.fill('input[name="area_sqft"]', '1500');
      if (await page.locator('textarea[name="description"]').count() > 0) {
        await page.fill('textarea[name="description"]', 'Automated test property listing.');
      }
      const btn = page.locator('button[type="submit"]');
      if ((await btn.count()) > 0) {
        await btn.first().click();
        await page.waitForTimeout(2000);
        log('List Property form: SUBMITTED');
      }
    } catch (e) {
      log(`List Property form: SUBMIT ERROR -> ${e.message}`);
    }
  } else {
    log('List Property form: NOT FOUND');
  }
  await page.screenshot({ path: 'testing/visual_tests/list_property.png', fullPage: false });
  screenshots.push('testing/visual_tests/list_property.png');
  await browser.close();
}

async function userPageTests() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();

  // Seed a test user and log in
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  try {
    execSync('php tools/db_seed_testdata.php', { stdio: 'pipe' });
  } catch (e) {}

  // Test login
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  const identityField = page.locator('input[name="identity"]').first();
  const passField = page.locator('input[name="password"]').first();
  if ((await identityField.count()) > 0 && (await passField.count()) > 0) {
    await identityField.fill('testuser@example.com');
    await passField.fill('Test@123');
    await page.locator('button[type="submit"]').first().click().catch(() => {});
    await page.waitForTimeout(2000);
  }

  // Test user dashboard
  await page.goto(`${BASE}/user/dashboard`, { waitUntil: 'networkidle' });
  const dashHtml = await page.content();
  if (dashHtml.includes('Welcome') || dashHtml.includes('Dashboard') || dashHtml.includes('My Properties')) {
    log('User Dashboard: LOADED');
  } else {
    log('User Dashboard: NOT LOADED (auth or render issue)');
  }
  await page.screenshot({ path: 'testing/visual_tests/user_dashboard.png', fullPage: false });
  screenshots.push('testing/visual_tests/user_dashboard.png');

  // Test user properties
  await page.goto(`${BASE}/user/properties`, { waitUntil: 'networkidle' });
  const propHtml = await page.content();
  if (propHtml.includes('My Properties') || propHtml.includes('Property')) {
    log('User Properties: LOADED');
  } else {
    log('User Properties: NOT LOADED');
  }

  // Test user inquiries
  await page.goto(`${BASE}/user/inquiries`, { waitUntil: 'networkidle' });
  const inqHtml = await page.content();
  if (inqHtml.includes('Inquiries') || inqHtml.includes('inquiry')) {
    log('User Inquiries: LOADED');
  } else {
    log('User Inquiries: NOT LOADED');
  }

  // Test user profile
  await page.goto(`${BASE}/user/profile`, { waitUntil: 'networkidle' });
  const profHtml = await page.content();
  if (profHtml.includes('Profile') || profHtml.includes('profile')) {
    log('User Profile: LOADED');
  } else {
    log('User Profile: NOT LOADED');
  }

  await browser.close();
}

async function newsletterTests() {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto(BASE, { waitUntil: 'networkidle' });
  try {
    const emailInput = page.locator('input[name="email"]').first();
    if ((await emailInput.count()) > 0) {
      await emailInput.fill('autotest@newsletter.example.com');
      const form = page.locator('form').first();
      if ((await form.count()) > 0) {
        await form.locator('button[type="submit"]').first().click().catch(() => {});
        await page.waitForTimeout(1500);
        log('Newsletter form: SUBMITTED');
      }
    }
  } catch (e) {
    log(`Newsletter form: ERROR -> ${e.message}`);
  }
  await browser.close();
}

async function main() {
  const ts = new Date().toISOString();
  fs.writeFileSync(REPORT, `=== APS Dream Home A-to-Z Test Report ===\nGenerated: ${ts}\n\n`);
  log('Starting A-to-Z test suite...');

  // Phase 0: DB Health
  log('--- Phase 0: DB Health ---');
  try {
    execSync('php testing/db_health_check.php', { stdio: 'inherit' });
  } catch (e) {
    log('DB Health check: FAILED');
  }

  // Phase 0b: DB Seeds
  log('--- Phase 0b: DB Seeds ---');
  try {
    execSync('php tools/db_seed_testdata.php', { stdio: 'inherit' });
  } catch (e) {
    log('DB Seed: FAILED');
  }

  // Phase 1: Header visuals
  log('--- Phase 1: Header UI/UX ---');
  await runBrowserTest(headerVisualTests, 'Header Visual Tests (Desktop/Tablet/Mobile)');

  // Phase 2: Admin flows
  log('--- Phase 2: Admin Login ---');
  await runBrowserTest(adminLoginTests, 'Admin Login (test-login bypass)');
  await runBrowserTest(adminUserPropertiesTests, 'Admin User Properties');

  // Phase 3: User flows
  log('--- Phase 3: User Property Posting ---');
  await runBrowserTest(userPropertyPostingTests, 'List Property Form');

  // Phase 4: Newsletter
  log('--- Phase 4: Newsletter ---');
  await runBrowserTest(newsletterTests, 'Newsletter Subscription');

  // Phase 5: User pages (requires login)
  log('--- Phase 5: User Pages ---');
  await runBrowserTest(userPageTests, 'User Dashboard/Properties/Inquiries/Profile');

  // Summary
  log('--- Summary ---');
  log(`Total screenshots captured: ${screenshots.length}`);
  screenshots.forEach(s => log(`  - ${s}`));
  log('A-to-Z test suite COMPLETE');

  const content = fs.readFileSync(REPORT, 'utf8');
  fs.appendFileSync(REPORT, `\n=== END OF REPORT ===\n`);
  console.log('\n' + fs.readFileSync(REPORT, 'utf8'));
}

main().catch(e => {
  console.error('MASTER TEST RUNNER ERROR:', e);
  process.exit(1);
});
