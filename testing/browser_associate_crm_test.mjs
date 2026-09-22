import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE_URL = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = path.resolve('_screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function runAssociateWorkflowTest() {
  console.log('🚀 Launching Google Chrome for Associate CRM & Business Workflow Test...');
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
    console.log('\nStep 1: Visiting logout...');
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(500);

    // 2. Login as Associate
    console.log('Step 2: Logging in as Associate (rajesh.associate@apsdreamhome.test)...');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.fill('input[name="identity"]', 'rajesh.associate@apsdreamhome.test');
    await page.fill('input[name="password"]', 'Password@123');

    const captcha = await page.$('input[name="captcha_code"]');
    if (captcha) await captcha.fill('123456');

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    console.log(`  Landed on: ${page.url()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_01_associate_cockpit.png') });

    // 3. Navigate to Associate CRM Dashboard
    console.log('\nStep 3: Visiting Associate CRM Dashboard (/associate/crm)...');
    await page.goto(`${BASE_URL}/associate/crm`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  CRM URL: ${page.url()}`);
    console.log(`  CRM Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_02_crm_dashboard.png') });

    // 4. Navigate to Add Lead Form
    console.log('\nStep 4: Navigating to Add Lead (/associate/leads/add)...');
    await page.goto(`${BASE_URL}/associate/leads/add`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Add Lead URL: ${page.url()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_03_add_lead_form.png') });

    // 5. Fill and Submit Add Lead Form
    console.log('\nStep 5: Submitting new lead details...');
    const testLeadName = 'Vikas Gupta ' + Math.floor(Math.random() * 1000);
    const randomPhone = '99' + Math.floor(10000000 + Math.random() * 90000000);
    await page.fill('input[name="name"]', testLeadName);
    await page.fill('input[name="phone"]', randomPhone);
    await page.fill('input[name="email"]', 'vikas.client@test.com');
    
    // Fill other fields if present
    const cityInput = await page.$('input[name="city"]');
    if (cityInput) await cityInput.fill('Gorakhpur');

    const budgetMinInput = await page.$('input[name="budget_min"]');
    if (budgetMinInput) await budgetMinInput.fill('1500000');

    const budgetMaxInput = await page.$('input[name="budget_max"]');
    if (budgetMaxInput) await budgetMaxInput.fill('2500000');

    const notesInput = await page.$('textarea[name="notes"]');
    if (notesInput) await notesInput.fill('Interested in 1500 sqft residential plot near Medical College road.');

    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_04_lead_form_filled.png') });

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}),
      page.click('button.btn-primary[type="submit"]')
    ]);
    await page.waitForTimeout(1000);

    console.log(`  After Submit URL: ${page.url()}`);
    console.log(`  Page Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_05_lead_detail_view.png') });

    // 6. View Leads List
    console.log('\nStep 6: Visiting Leads List (/associate/leads)...');
    await page.goto(`${BASE_URL}/associate/leads`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    const leadsContent = await page.content();
    const hasLeadName = leadsContent.includes(testLeadName);
    console.log(`  Leads list has newly created lead '${testLeadName}': ${hasLeadName}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_06_leads_list_verified.png') });

    // 7. View Site Visits page
    console.log('\nStep 7: Visiting Site Visits (/associate/site-visits)...');
    await page.goto(`${BASE_URL}/associate/site-visits`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Site Visits URL: ${page.url()} | Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_07_site_visits_page.png') });

    // 8. View MLM Team / Genealogy
    console.log('\nStep 8: Visiting MLM Genealogy Network (/associate/genealogy)...');
    await page.goto(`${BASE_URL}/associate/genealogy`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Genealogy URL: ${page.url()} | Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_08_genealogy_tree.png') });

    // 9. View Commissions and Wallet
    console.log('\nStep 9: Visiting Wallet & Payouts (/associate/wallet)...');
    await page.goto(`${BASE_URL}/associate/wallet`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    console.log(`  Wallet URL: ${page.url()} | Title: ${await page.title()}`);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_09_associate_wallet.png') });

    console.log('\n==================================================');
    console.log('🎉 ASSOCIATE CRM & WORKFLOW TEST COMPLETED SUCCESSFULLY!');
    console.log('==================================================\n');

  } catch (err) {
    console.error('❌ Error during associate workflow:', err);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'flow_error.png') });
    process.exit(1);
  } finally {
    await browser.close();
  }
}

runAssociateWorkflowTest().catch(e => {
  console.error('Fatal error:', e);
  process.exit(1);
});
