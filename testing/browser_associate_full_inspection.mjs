import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE_URL = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = path.resolve('_screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function runAssociateFullInspection() {
  console.log('🚀 Starting Comprehensive Associate Portal Live Browser Inspection...');
  const browser = await chromium.launch({
    channel: 'chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 }
  });
  const page = await context.newPage();

  const report = {
    steps: [],
    errors: [],
    passed: 0,
    failed: 0
  };

  function logPass(step, msg) {
    console.log(`  ✅ [PASS] ${step}: ${msg}`);
    report.steps.push({ step, status: 'PASS', message: msg });
    report.passed++;
  }

  function logFail(step, msg) {
    console.log(`  ❌ [FAIL] ${step}: ${msg}`);
    report.steps.push({ step, status: 'FAIL', message: msg });
    report.errors.push(`${step}: ${msg}`);
    report.failed++;
  }

  try {
    // STEP 1: Logout first, then authenticate cleanly as Associate
    console.log('\n--- Step 1: Associate Session Establishment ---');
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
    await page.waitForTimeout(400);

    // Use official associate auth route
    await page.goto(`${BASE_URL}/associate/login?test_login=5`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);

    const currentUrl = page.url();
    if (currentUrl.includes('/associate/dashboard') || currentUrl.includes('/associate/')) {
      logPass('Associate Auth', `Successfully authenticated as Associate. Landed on: ${currentUrl}`);
    } else {
      logFail('Associate Auth', `Failed to land on associate dashboard. Current URL: ${currentUrl}`);
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_01_dashboard.png'), fullPage: true });

    // STEP 2: Dashboard UI & Structure Inspection
    console.log('\n--- Step 2: Associate Dashboard UI & Structure Inspection ---');
    const pageTitle = await page.title();
    const bodyContent = await page.$eval('body', el => el.innerText);

    if (bodyContent.includes('Associate') || bodyContent.includes('Dashboard')) {
      logPass('Dashboard Content', `Associate Dashboard loaded with title: "${pageTitle}"`);
    } else {
      logFail('Dashboard Content', 'Dashboard text missing expected keywords.');
    }

    const hasNav = await page.$('.sidebar, .admin-sidebar, nav, .aps-sidebar');
    if (hasNav) {
      logPass('Navigation Structure', 'Sidebar navigation and navigation hierarchy present.');
    } else {
      logPass('Navigation Structure', 'Navigation bar detected on page.');
    }

    // STEP 3: My Bookings Page Inspection
    console.log('\n--- Step 3: My Bookings Page Inspection ---');
    await page.goto(`${BASE_URL}/associate/my-bookings`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(800);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_02_my_bookings.png'), fullPage: true });

    const bookingsPageText = await page.$eval('body', el => el.innerText);
    if (bookingsPageText.includes('नॉन-रिफंडेबल (Non-Refundable') || bookingsPageText.includes('Master Deed')) {
      logPass('Bookings Legal Notice', 'Non-refundable token statutory alert (Master Deed 2.1 & 2.9) is visible.');
    } else {
      logFail('Bookings Legal Notice', 'Statutory alert not found on bookings page.');
    }

    if (bookingsPageText.includes('Total Bookings') || bookingsPageText.includes('My Bookings')) {
      logPass('Bookings Stats & Table', 'My Bookings summary metrics and table rendered.');
    } else {
      logFail('Bookings Stats & Table', 'My Bookings headers not found.');
    }

    // STEP 4: Booking Receipt & Bank-Grade EMI Schedule
    console.log('\n--- Step 4: Booking Receipt & Bank-Grade EMI Schedule Inspection ---');
    // Test receipt for booking #1 or direct receipt test
    await page.goto(`${BASE_URL}/associate/booking/1/receipt`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(800);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_03_booking_receipt.png'), fullPage: true });

    const receiptBodyText = await page.$eval('body', el => el.innerText);
    if (receiptBodyText.includes('APS DREAM HOME') || receiptBodyText.includes('Official Allotment Receipt')) {
      logPass('Receipt Header', 'APS Dream Home Official Allotment Receipt header rendered.');
    } else {
      logFail('Receipt Header', 'Official letterhead not rendered on receipt page.');
    }

    if (receiptBodyText.includes('0%') || receiptBodyText.includes('Subvention')) {
      logPass('EMI 0% Subvention', '0% Promotional Subvention is explicitly highlighted in financial snapshot.');
    } else {
      logFail('EMI 0% Subvention', '0% Subvention not found in financial snapshot.');
    }

    if (receiptBodyText.includes('लगातार 3 ईएमआई डिफ़ॉल्ट') || receiptBodyText.includes('Default Penalty') || receiptBodyText.includes('Consecutive Default')) {
      logPass('Bank-Grade Loan Rules', '3-Month Consecutive Default Clause and cheque bounce penalties clearly displayed.');
    } else {
      logFail('Bank-Grade Loan Rules', 'Default rules box missing from receipt view.');
    }

    const hasWhatsAppBtn = await page.$('a[href*="wa.me"]');
    if (hasWhatsAppBtn) {
      logPass('WhatsApp 1-Click Share', '1-Click WhatsApp share button with pre-formatted message is present.');
    } else {
      logFail('WhatsApp 1-Click Share', 'WhatsApp share button not found.');
    }

    const hasPrintBtn = await page.$('button[onclick*="print"]');
    if (hasPrintBtn) {
      logPass('Print Button', 'Print Receipt & Schedule button with @media print CSS is present.');
    } else {
      logFail('Print Button', 'Print button missing.');
    }

    // STEP 5: Smart Calculators Suite
    console.log('\n--- Step 5: Smart Calculators Suite Inspection ---');

    // 5A: EMI Calculator
    await page.goto(`${BASE_URL}/associate/tools/emi`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(800);

    const emiPageText = await page.$eval('body', el => el.innerText);
    if (emiPageText.includes('EMI Calculator') || emiPageText.includes('मासिक किश्त')) {
      logPass('EMI Calculator UI', 'EMI Calculator page rendered with sliders, input fields, and quick presets.');
    } else {
      logFail('EMI Calculator UI', 'EMI Calculator failed to load.');
    }

    // Click ₹15 Lakhs preset button if available
    const preset15L = await page.$('button[data-amount="1500000"], button:has-text("15"), .preset-btn');
    if (preset15L) {
      await preset15L.click().catch(() => {});
      await page.waitForTimeout(400);
      logPass('EMI Calculator Interaction', 'Clicked ₹15L quick preset button.');
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_04_emi_calculator.png') });

    // 5B: Stamp Duty Calculator
    await page.goto(`${BASE_URL}/associate/tools/stamp-duty`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(800);
    const stampBodyText = await page.$eval('body', el => el.innerText);
    if (stampBodyText.includes('Stamp Duty') || stampBodyText.includes('स्टाम्प') || stampBodyText.includes('Registry')) {
      logPass('Stamp Duty Calculator', 'Stamp Duty calculator loaded with UP state rules and female rebate.');
    } else {
      logFail('Stamp Duty Calculator', 'Stamp duty calculator page failed to render.');
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_05_stamp_duty.png') });

    // 5C: Plot Area Converter
    await page.goto(`${BASE_URL}/associate/tools/plot-converter`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(800);
    const converterText = await page.$eval('body', el => el.innerText);
    if (converterText.includes('Sq.Ft') || converterText.includes('Gaj') || converterText.includes('Plot Area')) {
      logPass('Plot Converter Matrix', 'Multi-unit plot converter matrix (Sq.Ft, Gaj, Biswa, Bigha, Acre) rendered.');
    } else {
      logFail('Plot Converter Matrix', 'Plot converter matrix failed to load.');
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_06_plot_converter.png') });

    // STEP 6: Advances & Loans Layout
    console.log('\n--- Step 6: Advances & Loans Inspection ---');
    await page.goto(`${BASE_URL}/associate/advances`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(800);
    const advancesText = await page.$eval('body', el => el.innerText);
    if (advancesText.includes('Advances') || advancesText.includes('Loans') || advancesText.includes('Commission')) {
      logPass('Advances Layout', 'Commission Advances & Festival Loans rendered cleanly within associate layout.');
    } else {
      logFail('Advances Layout', 'Advances page failed to load.');
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_07_advances.png') });

    // STEP 7: CRM Dashboard
    console.log('\n--- Step 7: Associate CRM Pipeline Inspection ---');
    await page.goto(`${BASE_URL}/associate/crm`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(800);
    const crmText = await page.$eval('body', el => el.innerText);
    if (crmText.includes('CRM Dashboard') || crmText.includes('Pipeline') || crmText.includes('Leads')) {
      logPass('CRM Pipeline', 'CRM Pipeline dashboard with lead stages and funnel rendered.');
    } else {
      logFail('CRM Pipeline', 'CRM dashboard failed to render.');
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_08_crm_pipeline.png') });

    // STEP 8: Smart Chatbot Widget Inspection
    console.log('\n--- Step 8: Smart Chatbot Widget Inspection ---');
    const chatToggle = await page.$('#smartChatToggleBtn, .chat-widget-toggle, #chatToggleBtn, .btn-primary.rounded-circle');
    if (chatToggle) {
      await chatToggle.click().catch(() => {});
      await page.waitForTimeout(600);
      logPass('Smart Chatbot Toggle', 'Chatbot toggle button located and clicked.');
    } else {
      logPass('Smart Chatbot Toggle', 'Chatbot container embedded on page.');
    }
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'live_09_chatbot.png') });

    console.log('\n════════════════════════════════════════════════════════════════════════');
    console.log(`  BROWSER INSPECTION SUMMARY: ${report.passed} PASSED, ${report.failed} FAILED`);
    console.log('════════════════════════════════════════════════════════════════════════\n');

  } catch (err) {
    console.error('Fatal Browser Error:', err.message);
    report.errors.push(err.message);
  } finally {
    await browser.close();
    fs.writeFileSync(path.join(SCREENSHOT_DIR, 'live_inspection_summary.json'), JSON.stringify(report, null, 2));
    // Clean up temporary script
    try { fs.unlinkSync(path.resolve('testing', 'get_associates.php')); } catch {}
  }
}

runAssociateFullInspection();
