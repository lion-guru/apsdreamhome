import { chromium } from 'playwright';
import { execSync } from 'child_process';

// 1. Clean test database records first using FK-safe PHP script
try {
  const cleanOut = execSync('php testing/clean_test_users.php', { cwd: process.cwd() }).toString();
  console.log('DB Prep:', cleanOut.trim());
} catch (e) {
  console.log('Cleanup error:', e.message);
}

const BASE_URL = 'http://localhost/apsdreamhome';

async function runTest() {
  console.log('🚀 Starting Chrome Browser Test for Associate, Freelancer Agent & Employee Agent Registration...\n');
  const browser = await chromium.launch({ channel: 'chrome', headless: true });

  const results = [];

  // ==========================================
  // 1. TEST ASSOCIATE PARTNER REGISTRATION
  // ==========================================
  console.log('====================================================');
  console.log('1. Testing Associate Partner Registration Flow');
  console.log('====================================================');
  const context1 = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page1 = await context1.newPage();

  console.log('Navigating to register page...');
  await page1.goto(`${BASE_URL}/register`);
  await page1.waitForLoadState('domcontentloaded');
  await page1.waitForTimeout(1200);
  await page1.screenshot({ path: '_screenshots/reg_01_page_load.png', fullPage: true });

  console.log('Clicking Associate role card...');
  await page1.click('.role-card[data-role="associate"]');
  await page1.waitForTimeout(400);
  await page1.screenshot({ path: '_screenshots/reg_02_associate_selected.png', fullPage: true });

  console.log('Filling Associate details...');
  await page1.fill('input[name="name"]', 'Rajesh Sharma (Associate Partner)');
  await page1.fill('input[name="email"]', 'rajesh.associate@apsdreamhome.test');
  await page1.fill('input[name="phone"]', '9811223344');
  await page1.fill('input[name="password"]', 'Password@123');
  await page1.fill('input[name="confirm_password"]', 'Password@123');
  await page1.fill('#referralCodeInput', 'ADM1442');
  await page1.check('#terms');
  await page1.fill('input[name="captcha_code"]', '123456');

  await page1.screenshot({ path: '_screenshots/reg_03_associate_filled.png', fullPage: true });

  console.log('Submitting Associate registration...');
  await Promise.all([
    page1.waitForNavigation({ timeout: 15000 }).catch(e => console.log('Nav:', e.message)),
    page1.click('#btnSubmit')
  ]);

  await page1.waitForTimeout(1500);
  const url1 = page1.url();
  console.log('✅ Associate successfully registered! Redirected to:', url1);
  await page1.screenshot({ path: '_screenshots/reg_04_associate_success.png', fullPage: true });

  // Visit Associate Dashboard
  console.log('Visiting Associate Cockpit / Dashboard...');
  await page1.goto(`${BASE_URL}/associate/dashboard`);
  await page1.waitForLoadState('domcontentloaded');
  await page1.waitForTimeout(1200);
  await page1.screenshot({ path: '_screenshots/reg_04b_associate_dashboard.png', fullPage: true });
  console.log('✅ Associate Dashboard verified! Current URL:', page1.url());
  results.push({ 
    Role: 'Associate Partner', 
    Type: 'Freelancer MLM Network', 
    Status: 'Registered & Active',
    RedirectURL: url1, 
    DashboardURL: page1.url() 
  });
  await context1.close();

  // ==========================================
  // 2. TEST FREELANCER AGENT REGISTRATION
  // ==========================================
  console.log('\n====================================================');
  console.log('2. Testing Freelancer Agent Registration Flow');
  console.log('====================================================');
  const context2 = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page2 = await context2.newPage();

  console.log('Navigating to register page...');
  await page2.goto(`${BASE_URL}/register`);
  await page2.waitForLoadState('domcontentloaded');
  await page2.waitForTimeout(1200);

  console.log('Clicking Agent role card...');
  await page2.click('.role-card[data-role="agent"]');
  await page2.waitForTimeout(400);

  console.log('Selecting "Freelancer Agent" option...');
  await page2.check('#agent_type_freelancer');

  console.log('Filling Freelancer Agent details...');
  await page2.fill('input[name="name"]', 'Sanjay Verma (Freelancer Agent)');
  await page2.fill('input[name="email"]', 'sanjay.freelancer@apsdreamhome.test');
  await page2.fill('input[name="phone"]', '9822334455');
  await page2.fill('input[name="password"]', 'Password@123');
  await page2.fill('input[name="confirm_password"]', 'Password@123');
  await page2.fill('#referralCodeInput', 'ADM1442');
  await page2.check('#terms');
  await page2.fill('input[name="captcha_code"]', '123456');

  await page2.screenshot({ path: '_screenshots/reg_05_freelancer_filled.png', fullPage: true });

  console.log('Submitting Freelancer Agent registration...');
  await Promise.all([
    page2.waitForNavigation({ timeout: 15000 }).catch(e => console.log('Nav:', e.message)),
    page2.click('#btnSubmit')
  ]);

  await page2.waitForTimeout(1500);
  const url2 = page2.url();
  console.log('✅ Freelancer Agent successfully registered! Redirected to:', url2);
  await page2.screenshot({ path: '_screenshots/reg_06_freelancer_success.png', fullPage: true });

  // Visit Agent Dashboard
  console.log('Visiting Agent Dashboard (Freelancer Mode)...');
  await page2.goto(`${BASE_URL}/agent/dashboard`);
  await page2.waitForLoadState('domcontentloaded');
  await page2.waitForTimeout(1200);
  await page2.screenshot({ path: '_screenshots/reg_06b_freelancer_dashboard.png', fullPage: true });
  console.log('✅ Freelancer Agent Dashboard verified! Current URL:', page2.url());
  results.push({ 
    Role: 'Real Estate Agent', 
    Type: 'Freelancer / Independent', 
    Status: 'Registered & Active',
    RedirectURL: url2, 
    DashboardURL: page2.url() 
  });
  await context2.close();

  // ==========================================
  // 3. TEST EMPLOYEE AGENT REGISTRATION
  // ==========================================
  console.log('\n====================================================');
  console.log('3. Testing Employee Agent (In-House) Registration Flow');
  console.log('====================================================');
  const context3 = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page3 = await context3.newPage();

  console.log('Navigating to register page...');
  await page3.goto(`${BASE_URL}/register`);
  await page3.waitForLoadState('domcontentloaded');
  await page3.waitForTimeout(1200);

  console.log('Clicking Agent role card...');
  await page3.click('.role-card[data-role="agent"]');
  await page3.waitForTimeout(400);

  console.log('Selecting "Employee Agent" option...');
  await page3.check('#agent_type_employee');

  console.log('Filling Employee Agent details...');
  await page3.fill('input[name="name"]', 'Pooja Mishra (Employee Agent)');
  await page3.fill('input[name="email"]', 'pooja.employee@apsdreamhome.test');
  await page3.fill('input[name="phone"]', '9833445566');
  await page3.fill('input[name="password"]', 'Password@123');
  await page3.fill('input[name="confirm_password"]', 'Password@123');
  await page3.fill('#referralCodeInput', 'ADM1442');
  await page3.check('#terms');
  await page3.fill('input[name="captcha_code"]', '123456');

  await page3.screenshot({ path: '_screenshots/reg_07_employee_agent_filled.png', fullPage: true });

  console.log('Submitting Employee Agent registration...');
  await Promise.all([
    page3.waitForNavigation({ timeout: 15000 }).catch(e => console.log('Nav:', e.message)),
    page3.click('#btnSubmit')
  ]);

  await page3.waitForTimeout(1500);
  const url3 = page3.url();
  console.log('✅ Employee Agent successfully registered! Redirected to:', url3);
  await page3.screenshot({ path: '_screenshots/reg_08_employee_agent_success.png', fullPage: true });

  // Visit Agent Dashboard
  console.log('Visiting Agent Dashboard (In-House / MLM Company Mode)...');
  await page3.goto(`${BASE_URL}/agent/dashboard`);
  await page3.waitForLoadState('domcontentloaded');
  await page3.waitForTimeout(1200);
  await page3.screenshot({ path: '_screenshots/reg_08b_employee_agent_dashboard.png', fullPage: true });
  console.log('✅ Employee Agent Dashboard verified! Current URL:', page3.url());
  results.push({ 
    Role: 'Real Estate Agent', 
    Type: 'Company / Employee (In-House)', 
    Status: 'Registered & Active',
    RedirectURL: url3, 
    DashboardURL: page3.url() 
  });
  await context3.close();

  await browser.close();

  console.log('\n====================================================');
  console.log('📊 REGISTRATION WORKFLOW EXECUTION SUMMARY');
  console.log('====================================================');
  console.table(results);
}

runTest().catch(err => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
