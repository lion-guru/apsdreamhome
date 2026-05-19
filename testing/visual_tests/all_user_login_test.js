// COMPREHENSIVE USER LOGIN TEST - Test All User Roles
// Tests: Customer, Agent, Associate, Employee, Admin login flows
import { chromium } from 'playwright';
import fs from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const REPORT = 'testing/visual_tests/ALL_USER_LOGIN_REPORT.txt';
const screenshots = [];

function log(msg) {
  const ts = new Date().toISOString().slice(11, 19);
  console.log(`[${ts}] ${msg}`);
  fs.appendFileSync(REPORT, `[${ts}] ${msg}\n`);
}

async function testPageLoad(page, url, testName) {
  try {
    await page.goto(url, { waitUntil: 'load', timeout: 60000 });
    const title = await page.title();
    const content = await page.content();
    
    // Check for PHP errors
    if (content.includes('Fatal error') || content.includes('Parse error') || content.includes('Warning:')) {
      log(`${testName}: PHP ERROR DETECTED`);
      return false;
    }
    
    log(`${testName}: Page loaded (Title: ${title})`);
    return true;
  } catch (e) {
    log(`${testName}: FAILED to load -> ${e.message}`);
    return false;
  }
}

async function testCustomerLogin() {
  log('=== TESTING CUSTOMER LOGIN ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  try {
    // Test login page load
    await testPageLoad(page, `${BASE}/login`, 'Customer Login Page');
    await page.screenshot({ path: 'testing/visual_tests/customer_login_page.png', fullPage: false });
    screenshots.push('testing/visual_tests/customer_login_page.png');
    
    // Check for form fields
    const hasIdentityField = await page.locator('input[name="identity"]').count() > 0;
    const hasEmailField = await page.locator('input[name="email"]').count() > 0;
    const hasPassField = await page.locator('input[name="password"]').count() > 0;
    const hasSubmitBtn = await page.locator('button[type="submit"]').count() > 0;
    
    log(`Customer Login Form - Identity field: ${hasIdentityField ? 'YES' : 'NO'}`);
    log(`Customer Login Form - Email field: ${hasEmailField ? 'YES' : 'NO'}`);
    log(`Customer Login Form - Password field: ${hasPassField ? 'YES' : 'NO'}`);
    log(`Customer Login Form - Submit button: ${hasSubmitBtn ? 'YES' : 'NO'}`);
    
    if (!hasIdentityField && !hasEmailField) {
      log('ISSUE: Customer login form missing identity/email field');
    }
    if (!hasPassField) {
      log('ISSUE: Customer login form missing password field');
    }
    if (!hasSubmitBtn) {
      log('ISSUE: Customer login form missing submit button');
    }
    
    // Try to login with test credentials
    if (hasIdentityField && hasPassField) {
      await page.fill('input[name="identity"]', 'testuser@example.com');
      await page.fill('input[name="password"]', 'Test@123');
      await page.locator('button[type="submit"]').first().click();
      await page.waitForTimeout(3000);
      
      const currentUrl = page.url();
      if (currentUrl.includes('/user/dashboard') || currentUrl.includes('/dashboard')) {
        log('Customer Login: SUCCESS - Redirected to dashboard');
        await page.screenshot({ path: 'testing/visual_tests/customer_dashboard.png', fullPage: false });
        screenshots.push('testing/visual_tests/customer_dashboard.png');
      } else if (currentUrl.includes('/login')) {
        log('ISSUE: Customer Login FAILED - Still on login page (invalid credentials or auth error)');
      } else {
        log(`Customer Login: Redirected to ${currentUrl}`);
      }
    }
    
  } catch (e) {
    log(`Customer Login Test Error: ${e.message}`);
  }
  
  await browser.close();
}

async function testAgentLogin() {
  log('=== TESTING AGENT LOGIN ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  try {
    await testPageLoad(page, `${BASE}/agent/login`, 'Agent Login Page');
    await page.screenshot({ path: 'testing/visual_tests/agent_login_page.png', fullPage: false });
    screenshots.push('testing/visual_tests/agent_login_page.png');
    
    // Check for form fields
    const hasEmailField = await page.locator('input[name="email"]').count() > 0;
    const hasPassField = await page.locator('input[name="password"]').count() > 0;
    const hasSubmitBtn = await page.locator('button[type="submit"]').count() > 0;
    
    log(`Agent Login Form - Email field: ${hasEmailField ? 'YES' : 'NO'}`);
    log(`Agent Login Form - Password field: ${hasPassField ? 'YES' : 'NO'}`);
    log(`Agent Login Form - Submit button: ${hasSubmitBtn ? 'YES' : 'NO'}`);
    
    if (!hasEmailField) {
      log('ISSUE: Agent login form missing email field');
    }
    if (!hasPassField) {
      log('ISSUE: Agent login form missing password field');
    }
    if (!hasSubmitBtn) {
      log('ISSUE: Agent login form missing submit button');
    }
    
  } catch (e) {
    log(`Agent Login Test Error: ${e.message}`);
  }
  
  await browser.close();
}

async function testAssociateLogin() {
  log('=== TESTING ASSOCIATE LOGIN ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  try {
    await testPageLoad(page, `${BASE}/associate/login`, 'Associate Login Page');
    await page.screenshot({ path: 'testing/visual_tests/associate_login_page.png', fullPage: false });
    screenshots.push('testing/visual_tests/associate_login_page.png');
    
    // Check for form fields
    const hasEmailField = await page.locator('input[name="email"]').count() > 0;
    const hasPassField = await page.locator('input[name="password"]').count() > 0;
    const hasSubmitBtn = await page.locator('button[type="submit"]').count() > 0;
    
    log(`Associate Login Form - Email field: ${hasEmailField ? 'YES' : 'NO'}`);
    log(`Associate Login Form - Password field: ${hasPassField ? 'YES' : 'NO'}`);
    log(`Associate Login Form - Submit button: ${hasSubmitBtn ? 'YES' : 'NO'}`);
    
    if (!hasEmailField) {
      log('ISSUE: Associate login form missing email field');
    }
    if (!hasPassField) {
      log('ISSUE: Associate login form missing password field');
    }
    if (!hasSubmitBtn) {
      log('ISSUE: Associate login form missing submit button');
    }
    
  } catch (e) {
    log(`Associate Login Test Error: ${e.message}`);
  }
  
  await browser.close();
}

async function testEmployeeLogin() {
  log('=== TESTING EMPLOYEE LOGIN ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  try {
    await testPageLoad(page, `${BASE}/employee/login`, 'Employee Login Page');
    await page.screenshot({ path: 'testing/visual_tests/employee_login_page.png', fullPage: false });
    screenshots.push('testing/visual_tests/employee_login_page.png');
    
    // Check for form fields
    const hasEmailField = await page.locator('input[name="email"]').count() > 0;
    const hasPassField = await page.locator('input[name="password"]').count() > 0;
    const hasSubmitBtn = await page.locator('button[type="submit"]').count() > 0;
    
    log(`Employee Login Form - Email field: ${hasEmailField ? 'YES' : 'NO'}`);
    log(`Employee Login Form - Password field: ${hasPassField ? 'YES' : 'NO'}`);
    log(`Employee Login Form - Submit button: ${hasSubmitBtn ? 'YES' : 'NO'}`);
    
    if (!hasEmailField) {
      log('ISSUE: Employee login form missing email field');
    }
    if (!hasPassField) {
      log('ISSUE: Employee login form missing password field');
    }
    if (!hasSubmitBtn) {
      log('ISSUE: Employee login form missing submit button');
    }
    
  } catch (e) {
    log(`Employee Login Test Error: ${e.message}`);
  }
  
  await browser.close();
}

async function testAdminLogin() {
  log('=== TESTING ADMIN LOGIN ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  try {
    await testPageLoad(page, `${BASE}/admin/login`, 'Admin Login Page');
    await page.screenshot({ path: 'testing/visual_tests/admin_login_page.png', fullPage: false });
    screenshots.push('testing/visual_tests/admin_login_page.png');
    
    // Check for form fields
    const hasEmailField = await page.locator('input[name="email"]').count() > 0;
    const hasPassField = await page.locator('input[name="password"]').count() > 0;
    const hasSubmitBtn = await page.locator('button[type="submit"]').count() > 0;
    
    log(`Admin Login Form - Email field: ${hasEmailField ? 'YES' : 'NO'}`);
    log(`Admin Login Form - Password field: ${hasPassField ? 'YES' : 'NO'}`);
    log(`Admin Login Form - Submit button: ${hasSubmitBtn ? 'YES' : 'NO'}`);
    
    if (!hasEmailField) {
      log('ISSUE: Admin login form missing email field');
    }
    if (!hasPassField) {
      log('ISSUE: Admin login form missing password field');
    }
    if (!hasSubmitBtn) {
      log('ISSUE: Admin login form missing submit button');
    }
    
    // Test test-login bypass
    try {
      await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 60000 });
      const currentUrl = page.url();
      if (currentUrl.includes('/admin') && !currentUrl.includes('/login')) {
        log('Admin test-login bypass: SUCCESS');
        await page.screenshot({ path: 'testing/visual_tests/admin_dashboard.png', fullPage: false });
        screenshots.push('testing/visual_tests/admin_dashboard.png');
      } else {
        log('ISSUE: Admin test-login bypass FAILED');
      }
    } catch (e) {
      log(`Admin test-login bypass Error: ${e.message}`);
    }
    
  } catch (e) {
    log(`Admin Login Test Error: ${e.message}`);
  }
  
  await browser.close();
}

async function testDashboards() {
  log('=== TESTING DASHBOARD ACCESS ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  const dashboards = [
    { url: '/user/dashboard', name: 'Customer Dashboard' },
    { url: '/agent/dashboard', name: 'Agent Dashboard' },
    { url: '/associate/dashboard', name: 'Associate Dashboard' },
    { url: '/employee/dashboard', name: 'Employee Dashboard' },
    { url: '/admin/dashboard', name: 'Admin Dashboard' },
  ];
  
  for (const dash of dashboards) {
    try {
      await page.goto(`${BASE}${dash.url}`, { waitUntil: 'load', timeout: 60000 });
      const content = await page.content();
      
      // Check for PHP errors
      if (content.includes('Fatal error') || content.includes('Parse error')) {
        log(`ISSUE: ${dash.name} has PHP errors`);
      } else if (content.includes('Login') || content.includes('login')) {
        log(`${dash.name}: Requires authentication (redirected to login)`);
      } else {
        log(`${dash.name}: Accessible`);
      }
      
      await page.screenshot({ path: `testing/visual_tests/${dash.name.replace(/\s+/g, '_').toLowerCase()}.png`, fullPage: false });
      screenshots.push(`testing/visual_tests/${dash.name.replace(/\s+/g, '_').toLowerCase()}.png`);
      
    } catch (e) {
      log(`ISSUE: ${dash.name} failed to load -> ${e.message}`);
    }
  }
  
  await browser.close();
}

async function main() {
  log('STARTING COMPREHENSIVE USER LOGIN TEST');
  log('=====================================');
  
  // Clear previous report
  fs.writeFileSync(REPORT, '');
  
  await testCustomerLogin();
  await testAgentLogin();
  await testAssociateLogin();
  await testEmployeeLogin();
  await testAdminLogin();
  await testDashboards();
  
  log('=====================================');
  log('TEST COMPLETED');
  log(`Screenshots saved: ${screenshots.length}`);
  log(`Report saved to: ${REPORT}`);
}

main().catch(console.error);
