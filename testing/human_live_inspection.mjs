import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE_URL = 'http://localhost/apsdreamhome';
const OUT_DIR = path.resolve('_screenshots/human_audit');

if (!fs.existsSync(OUT_DIR)) {
  fs.mkdirSync(OUT_DIR, { recursive: true });
}

const auditFindings = {
  testedAt: new Date().toISOString(),
  pagesTested: 0,
  consoleErrors: [],
  networkErrors: [],
  brokenImages: [],
  uiFlaws: [],
  functionalChecks: []
};

async function inspectPage(page, pageName, relativeUrl, options = {}) {
  const url = `${BASE_URL}${relativeUrl}`;
  console.log(`\n🔍 [Testing Page] ${pageName}: ${url}`);
  auditFindings.pagesTested++;

  const pageConsoleErrors = [];
  const pageNetworkErrors = [];

  const consoleHandler = (msg) => {
    if (msg.type() === 'error') {
      const text = msg.text();
      // Ignore routine websocket connection failures if ws server is offline in dev
      if (!text.includes('WebSocket') && !text.includes('favicon.ico')) {
        pageConsoleErrors.push({ page: pageName, text });
      }
    }
  };

  const pageErrorHandler = (err) => {
    pageConsoleErrors.push({ page: pageName, text: err.message });
  };

  const responseHandler = (res) => {
    const status = res.status();
    const reqUrl = res.url();
    if (status >= 400 && !reqUrl.includes('favicon.ico') && !reqUrl.includes('ads.txt')) {
      pageNetworkErrors.push({ page: pageName, status, url: reqUrl });
    }
  };

  page.on('console', consoleHandler);
  page.on('pageerror', pageErrorHandler);
  page.on('response', responseHandler);

  try {
    const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
    await page.waitForTimeout(options.waitMs || 1000);
    const status = resp ? resp.status() : 0;
    const title = await page.title();

    // Check broken images
    const brokenImgs = await page.evaluate(() => {
      const imgs = Array.from(document.querySelectorAll('img'));
      return imgs
        .filter(img => img.src && !img.complete || (img.naturalWidth === 0 && !img.src.startsWith('data:')))
        .map(img => ({ src: img.src, alt: img.alt || '' }));
    });

    if (brokenImgs.length > 0) {
      console.log(`  ⚠️ Found ${brokenImgs.length} broken images`);
      brokenImgs.forEach(b => {
        auditFindings.brokenImages.push({ page: pageName, ...b });
      });
    }

    // Save screenshot
    const screenshotPath = path.join(OUT_DIR, `${pageName}.png`);
    await page.screenshot({ path: screenshotPath, fullPage: options.fullPage || false });
    console.log(`  📸 Screenshot saved: ${pageName}.png (HTTP ${status}, Title: "${title}")`);

    // Check for any obvious PHP errors or stack traces in HTML
    const bodyText = await page.evaluate(() => document.body ? document.body.innerText : '');
    if (bodyText.includes('Fatal error:') || bodyText.includes('Parse error:') || bodyText.includes('Warning: require') || bodyText.includes('SQLSTATE[')) {
      const match = bodyText.match(/(Fatal error|Parse error|Warning|SQLSTATE)[\s\S]{1,200}/i);
      auditFindings.uiFlaws.push({
        page: pageName,
        type: 'PHP_ERROR_RENDERED',
        snippet: match ? match[0] : 'Error string found in body text'
      });
      console.log(`  ❌ PHP Error visible on page: ${match ? match[0] : ''}`);
    }

    if (options.customCheck) {
      await options.customCheck(page, pageName);
    }

  } catch (err) {
    console.error(`  ❌ Failed loading ${pageName}: ${err.message}`);
    auditFindings.uiFlaws.push({
      page: pageName,
      type: 'PAGE_LOAD_EXCEPTION',
      error: err.message
    });
  } finally {
    page.off('console', consoleHandler);
    page.off('pageerror', pageErrorHandler);
    page.off('response', responseHandler);

    if (pageConsoleErrors.length > 0) {
      auditFindings.consoleErrors.push(...pageConsoleErrors);
    }
    if (pageNetworkErrors.length > 0) {
      auditFindings.networkErrors.push(...pageNetworkErrors);
    }
  }
}

async function runAudit() {
  console.log('🚀 Starting Deep Live Inspection via Chromium...');
  const browser = await chromium.launch({
    channel: 'chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const context = await browser.newContext({
    viewport: { width: 1366, height: 768 }
  });
  const page = await context.newPage();

  // 1. Public Homepage
  await inspectPage(page, '01_homepage', '/', {
    customCheck: async (p) => {
      // Test hero search form elements
      const searchBtn = await p.$('button[type="submit"], .btn-search, form[action*="search"] button');
      console.log(`  [Homepage] Search button present: ${!!searchBtn}`);
      
      // Test Chat Widget
      const chatLauncher = await p.$('#cwToggle, .cw-toggle, .chat-widget-toggle, #chatLauncher');
      if (chatLauncher) {
        console.log('  [Homepage] Chat widget toggle found. Clicking...');
        await chatLauncher.click().catch(() => {});
        await p.waitForTimeout(600);
        await p.screenshot({ path: path.join(OUT_DIR, '01_homepage_chat_opened.png') });
        const chatWindow = await p.$('.cw-box, .chat-widget-window, #chatWindow');
        const isVisible = chatWindow ? await chatWindow.isVisible().catch(() => false) : false;
        console.log(`  [Homepage] Chat window visible after click: ${isVisible}`);
        auditFindings.functionalChecks.push({ feature: 'Chat Widget Toggle', working: isVisible });
      } else {
        auditFindings.functionalChecks.push({ feature: 'Chat Widget Toggle', working: false, reason: 'Button not found' });
      }
    }
  });

  // 2. Properties Listing
  await inspectPage(page, '02_properties_page', '/properties', {
    customCheck: async (p) => {
      const cards = await p.$$('.property-card, .listing-card, .plot-card');
      console.log(`  [Properties] Rendered cards: ${cards.length}`);
      const emptyState = await p.$('.empty-state, .no-results, .alert-info');
      console.log(`  [Properties] Empty state shown: ${!!emptyState}`);
    }
  });

  // 3. Search Page
  await inspectPage(page, '03_search_page', '/search?q=gorakhpur');

  // 4. Projects / Colonies
  await inspectPage(page, '04_colonies_page', '/colonies');

  // 5. EMI Calculator Page
  await inspectPage(page, '05_emi_calculator', '/emi-calculator', {
    customCheck: async (p) => {
      // Look for EMI result element
      const emiVal = await p.$('#monthlyEmi, .emi-amount, #emiAmount, .monthly-emi');
      if (emiVal) {
        const text = await emiVal.innerText().catch(() => '');
        console.log(`  [EMI Calculator] Calculated monthly EMI display: "${text}"`);
        auditFindings.functionalChecks.push({ feature: 'EMI Calculator Display', working: text.length > 0, value: text });
      } else {
        console.log('  [EMI Calculator] EMI result element not found');
      }
    }
  });

  // 6. Contact Page
  await inspectPage(page, '06_contact_page', '/contact', {
    customCheck: async (p) => {
      const form = await p.$('form');
      console.log(`  [Contact] Contact form found: ${!!form}`);
    }
  });

  // 7. About Page
  await inspectPage(page, '07_about_page', '/about');

  // 8. FAQ Page
  await inspectPage(page, '08_faq_page', '/faq', {
    customCheck: async (p) => {
      const accordion = await p.$('.accordion-button, .faq-question');
      if (accordion) {
        await accordion.click().catch(() => {});
        await p.waitForTimeout(400);
        await p.screenshot({ path: path.join(OUT_DIR, '08_faq_expanded.png') });
        console.log('  [FAQ] Clicked accordion item, captured expanded state');
      }
    }
  });

  // 9. Blog Page
  await inspectPage(page, '09_blog_page', '/blog');

  // 10. Legal / Privacy / Terms
  await inspectPage(page, '10_privacy_page', '/privacy');
  await inspectPage(page, '11_terms_page', '/terms');

  // 11. Auth Pages
  await inspectPage(page, '12_login_page', '/login');
  await inspectPage(page, '13_register_page', '/register');
  await inspectPage(page, '14_forgot_password_page', '/forgot-password');
  await inspectPage(page, '15_air_login_page', '/auth/air-login');

  // 12. Logged-in Customer Workflow
  console.log('\n🔑 Testing Customer Portal Flows...');
  try {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="identity"]', 'customer_test@apsdreamhome.test');
    await page.fill('input[name="password"]', 'Aps@2026');
    const cap = await page.$('input[name="captcha_code"]');
    if (cap) await cap.fill('123456');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    // Customer subpages
    await inspectPage(page, '16_customer_dashboard', '/user/dashboard');
    await inspectPage(page, '17_customer_properties', '/user/properties');
    await inspectPage(page, '18_customer_inquiries', '/user/inquiries');
    await inspectPage(page, '19_customer_bookings', '/user/bookings');
    await inspectPage(page, '20_customer_favorites', '/user/favorites');
    await inspectPage(page, '21_customer_profile', '/user/profile');
    await inspectPage(page, '22_customer_notifications', '/user/notifications');
    await inspectPage(page, '23_customer_kyc', '/user/kyc');
  } catch (err) {
    console.error('Customer workflow error:', err.message);
  }

  // 13. Logged-in Associate Workflow
  console.log('\n🔑 Testing Associate Portal Flows...');
  try {
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded' });
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="identity"]', 'rajesh.associate@apsdreamhome.test');
    await page.fill('input[name="password"]', 'Aps@2026');
    const cap = await page.$('input[name="captcha_code"]');
    if (cap) await cap.fill('123456');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    await inspectPage(page, '24_associate_dashboard', '/associate/dashboard', {
      customCheck: async (p) => {
        // Handle Code of Conduct modal if present
        const acceptBtn = await p.$('#acceptConductBtn, button:has-text("I Accept"), .btn:has-text("Accept")');
        if (acceptBtn) {
          console.log('  [Associate] Code of Conduct modal detected. Clicking accept...');
          await acceptBtn.click().catch(() => {});
          await p.waitForTimeout(800);
          await p.screenshot({ path: path.join(OUT_DIR, '24_associate_dashboard_after_conduct.png') });
        }
      }
    });
    await inspectPage(page, '25_associate_leads', '/associate/leads');
    await inspectPage(page, '26_associate_commissions', '/associate/commissions');
    await inspectPage(page, '27_associate_wallet', '/associate/wallet');
    await inspectPage(page, '28_associate_network', '/associate/network');
  } catch (err) {
    console.error('Associate workflow error:', err.message);
  }

  // 14. Logged-in Employee Workflow
  console.log('\n🔑 Testing Employee Portal Flows...');
  try {
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded' });
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="identity"]', 'emp_test@apsdreamhome.test');
    await page.fill('input[name="password"]', 'Aps@2026');
    const cap = await page.$('input[name="captcha_code"]');
    if (cap) await cap.fill('123456');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    await inspectPage(page, '29_employee_dashboard', '/employee/dashboard');
    await inspectPage(page, '30_employee_tasks', '/employee/tasks');
    await inspectPage(page, '31_employee_attendance', '/employee/attendance');
    await inspectPage(page, '32_employee_leaves', '/employee/leaves');
    await inspectPage(page, '33_employee_payroll', '/employee/payroll');
  } catch (err) {
    console.error('Employee workflow error:', err.message);
  }

  // 15. Logged-in Admin Panel Flows
  console.log('\n🔑 Testing Admin Panel Flows...');
  try {
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded' });
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="identity"]', 'admin@apsdreamhome.com');
    await page.fill('input[name="password"]', 'Aps@2026');
    const cap = await page.$('input[name="captcha_code"]');
    if (cap) await cap.fill('123456');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    await inspectPage(page, '34_admin_erp', '/admin/erp');
    await inspectPage(page, '35_admin_dashboard', '/admin/dashboard');
    await inspectPage(page, '36_admin_leads', '/admin/leads');
    await inspectPage(page, '37_admin_plots', '/admin/plots');
    await inspectPage(page, '38_admin_colonies', '/admin/colonies');
    await inspectPage(page, '39_admin_users', '/admin/users');
    await inspectPage(page, '40_admin_finance', '/admin/finance/cash-flow');
  } catch (err) {
    console.error('Admin workflow error:', err.message);
  }

  await browser.close();

  // Write audit results JSON
  fs.writeFileSync('testing/human_live_inspection_report.json', JSON.stringify(auditFindings, null, 2));
  console.log('\n==================================================');
  console.log('🏁 HUMAN-LIKE LIVE AUDIT COMPLETE');
  console.log(`Pages Tested: ${auditFindings.pagesTested}`);
  console.log(`Console Errors: ${auditFindings.consoleErrors.length}`);
  console.log(`Network Errors: ${auditFindings.networkErrors.length}`);
  console.log(`Broken Images: ${auditFindings.brokenImages.length}`);
  console.log(`UI/PHP Flaws: ${auditFindings.uiFlaws.length}`);
  console.log('Report saved to: testing/human_live_inspection_report.json');
  console.log('Screenshots saved to: _screenshots/human_audit/');
  console.log('==================================================\n');
}

runAudit().catch(console.error);
