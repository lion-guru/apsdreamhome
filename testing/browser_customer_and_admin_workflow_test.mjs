import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE_URL = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = path.resolve('_screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function runCustomerAndAdminWorkflowTest() {
  console.log('🚀 Launching Google Chrome for Customer Booking & Admin Sales Workflow Test...');
  const browser = await chromium.launch({
    channel: 'chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const context = await browser.newContext({
    viewport: { width: 1366, height: 768 }
  });
  const page = await context.newPage();

  try {
    // 1. Clear session
    console.log('\n--- PHASE 1: CUSTOMER PORTAL & PLOT EXPLORATION ---');
    console.log('Step 1: Logging out old session...');
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(500);

    // 2. Customer Login
    console.log('Step 2: Customer logging in (customer_test@apsdreamhome.test)...');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.fill('input[name="identity"]', 'customer_test@apsdreamhome.test');
    await page.fill('input[name="password"]', 'Password@123');

    const captcha = await page.$('input[name="captcha_code"]');
    if (captcha) await captcha.fill('123456');

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    console.log(`  Customer Dashboard URL: ${page.url()}`);
    console.log(`  Customer Page Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_cust_01_dashboard.png') });

    // 3. Browse Plots
    console.log('\nStep 3: Browsing Available Plots (/plots/browse)...');
    await page.goto(`${BASE_URL}/plots/browse`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Plots Browse URL: ${page.url()}`);
    console.log(`  Plots Browse Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_cust_02_plots_browse.png') });

    // 4. View Plot Detail
    console.log('\nStep 4: Viewing Plot 63 Detail (/plots/63/detail)...');
    await page.goto(`${BASE_URL}/plots/63/detail`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Plot Detail URL: ${page.url()}`);
    console.log(`  Plot Detail Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_cust_03_plot_detail.png') });

    // 5. Check and Submit Booking Form
    console.log('\nStep 5: Opening Plot Booking Form (/plots/63/book)...');
    await page.goto(`${BASE_URL}/plots/63/book`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Plot Book Form URL: ${page.url()}`);
    console.log(`  Plot Book Form Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_cust_04_booking_form.png') });

    const notes = await page.$('textarea[name="notes"]');
    if (notes) await notes.fill('Booking plot 63 as test customer.');

    const checks = ['#termsCheck', '#cancellationCheck', '#emiTermsCheck', '#kycCheck', '#esignConsent', '#tripartiteConsent'];
    for (const c of checks) {
      const el = await page.$(c);
      if (el) await el.check();
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_cust_05_booking_form_filled.png') });

    console.log('  Submitting booking...');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('#submitBooking')
    ]);
    await page.waitForTimeout(1000);
    console.log(`  After Booking Submit URL: ${page.url()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_cust_06_booking_result.png') });

    // 6. Admin Sales & Bookings Verification
    console.log('\n--- PHASE 2: SUPER ADMIN BACKOFFICE & SALES OVERSIGHT ---');
    console.log('Step 6: Logging out Customer and logging in as Super Admin...');
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(500);

    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.fill('input[name="identity"]', 'admin@apsdreamhome.com');
    await page.fill('input[name="password"]', 'Aps@2026');

    const captcha2 = await page.$('input[name="captcha_code"]');
    if (captcha2) await captcha2.fill('123456');

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    console.log(`  Admin Dashboard URL: ${page.url()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_admin_01_dashboard.png') });

    // 7. Admin Sales Bookings
    console.log('\nStep 7: Inspecting Sales Bookings (/admin/sales/bookings)...');
    await page.goto(`${BASE_URL}/admin/sales/bookings`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Sales Bookings URL: ${page.url()}`);
    console.log(`  Sales Bookings Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_admin_02_sales_bookings.png') });

    // 8. Admin Commissions
    console.log('\nStep 8: Inspecting Sales Commissions (/admin/sales/commissions)...');
    await page.goto(`${BASE_URL}/admin/sales/commissions`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Sales Commissions URL: ${page.url()}`);
    console.log(`  Sales Commissions Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_admin_03_sales_commissions.png') });

    // 9. Admin Plot Inventory Management
    console.log('\nStep 9: Inspecting Plot Inventory Management (/admin/plots/manage)...');
    await page.goto(`${BASE_URL}/admin/plots/manage`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Plot Inventory URL: ${page.url()}`);
    console.log(`  Plot Inventory Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_admin_04_plot_inventory.png') });

    console.log('\n==================================================');
    console.log('🎉 CUSTOMER & ADMIN WORKFLOW TEST COMPLETED SUCCESSFULLY!');
    console.log('==================================================\n');

  } catch (err) {
    console.error('❌ Error during customer/admin workflow test:', err);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_cust_admin_error.png') });
    process.exit(1);
  } finally {
    await browser.close();
  }
}

runCustomerAndAdminWorkflowTest().catch(e => {
  console.error('Fatal error:', e);
  process.exit(1);
});
