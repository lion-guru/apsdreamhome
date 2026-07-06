import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';

let browser, context, page;
let pageCount = 0;

async function createBrowser() {
  if (browser) {
    try {
      await browser.close();
    } catch (e) {}
  }
  // Force garbage collection before creating new browser
  if (global.gc) global.gc();
  browser = await chromium.launch({ headless: true });
  context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  page = await context.newPage();
  pageCount = 0;
  page.on('crash', () => console.log('  [!] Browser page crashed'));
}

async function restartIfNeeded() {
  pageCount++;
  if (pageCount >= 15) {
    console.log('  [restart] Memory management...');
    await createBrowser();
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 15000 });
    await page.waitForTimeout(1000);
  }
}

async function safeGoto(path) {
  await restartIfNeeded();
  try {
    const response = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 15000 });
    return { status: response.status(), url: page.url() };
  } catch (err) {
    if (
      err.message.includes('crashed') ||
      err.message.includes('Target closed') ||
      err.message.includes('ERR_ABORTED')
    ) {
      console.log('  [!] Crash detected - restarting browser...');
      await createBrowser();
      await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 15000 });
      await page.waitForTimeout(1000);
      const response = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 15000 });
      return { status: response.status(), url: page.url() };
    }
    throw err;
  }
}

function check(desc, pass, detail) {
  console.log(`  ${pass ? 'PASS' : 'FAIL'} ${desc}${detail ? ' => ' + detail : ''}`);
  return pass;
}

async function run() {
  const results = { pass: 0, fail: 0 };
  await createBrowser();

  console.log('=== APS DREAM HOME - FULL WORKFLOW E2E TEST ===\n');

  // Login
  console.log('--- Step 1: Admin Login ---');
  await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 15000 });
  await page.waitForTimeout(1000);
  const loggedIn = !page.url().includes('login');
  loggedIn ? results.pass++ : results.fail++;
  check('Admin Login via test_login bypass', loggedIn, page.url());

  if (!loggedIn) {
    console.log('\nFATAL: Cannot login. Aborting.');
    await browser.close();
    process.exit(1);
  }

  // PHASE A: Land Acquisition
  console.log('\n--- Phase A: Land Acquisition Pipeline ---');
  const landUrls = [
    ['/admin/land-inventory/leads', 'Land Leads List'],
    ['/admin/land-inventory/leads/new', 'Create Land Lead Form'],
    ['/admin/land-inventory/acquisitions', 'Land Acquisitions'],
    ['/admin/land-inventory/brokers', 'Land Brokers'],
  ];
  for (const [path, name] of landUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE B: Colony Creation & Pipeline
  console.log('\n--- Phase B: Colony Creation & Pipeline ---');
  const colonyUrls = [
    ['/admin/colonies', 'Colonies List'],
    ['/admin/colonies/create', 'Create Colony Form'],
    ['/admin/colony-pipeline', 'Colony Pipeline Dashboard'],
    ['/admin/colony-pipeline/2', 'Colony Pipeline Detail (Suryoday)'],
    ['/admin/colony-pipeline/2/map', 'Colony Interactive Map'],
    ['/admin/locations/colonies', 'Location Colonies'],
  ];
  for (const [path, name] of colonyUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE C: Plot Cutting & Pricing
  console.log('\n--- Phase C: Plot Cutting & Pricing ---');
  const plotUrls = [
    ['/admin/plots', 'Plots List'],
    ['/admin/plots/create', 'Create Plot Form'],
    ['/admin/plots/check-availability', 'Plot Availability Checker'],
    ['/admin/plots/map', 'Plot Map View'],
    ['/admin/plot-costs', 'Plot Costs Dashboard'],
    ['/admin/plot-costs/colony/2', 'Plot Costs - Colony 2'],
    ['/admin/plot-costs/report/2', 'Plot Cost Report - Colony 2'],
  ];
  for (const [path, name] of plotUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE D: Booking & Sales
  console.log('\n--- Phase D: Booking & Sales ---');
  const salesUrls = [
    ['/admin/bookings', 'Bookings List'],
    ['/admin/bookings/create', 'Create Booking Form'],
    ['/admin/sales', 'Sales Dashboard'],
    ['/admin/deals', 'Deals Pipeline'],
    ['/admin/deal-pipeline', 'Deal Pipeline View'],
    ['/admin/customers', 'Customers List'],
    ['/admin/property-allocations', 'Property Allocations'],
  ];
  for (const [path, name] of salesUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE E: Commission Engine
  console.log('\n--- Phase E: Commission Engine ---');
  const commissionUrls = [
    ['/admin/mlm', 'MLM Dashboard'],
    ['/admin/mlm/associates', 'MLM Associates'],
    ['/admin/mlm/commission', 'Commission Ledger'],
    ['/admin/mlm/network', 'Network Tree'],
    ['/admin/commission', 'Commission Dashboard'],
    ['/admin/commission/rules', 'Commission Rules'],
    ['/admin/network/tree', 'Network Tree (Alt)'],
    ['/admin/network/genealogy', 'Genealogy View'],
    ['/admin/network/ranks', 'Rank Definitions'],
    ['/admin/mlm-growth-reports', 'MLM Growth Reports'],
  ];
  for (const [path, name] of commissionUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE F: Payouts
  console.log('\n--- Phase F: Payouts ---');
  const payoutUrls = [
    ['/admin/payouts', 'Payouts Dashboard'],
    ['/admin/payouts/list/all', 'All Payout Requests'],
    ['/admin/mlm/payouts', 'MLM Payouts'],
    ['/admin/mlm-rewards/withdrawals', 'Reward Withdrawals'],
  ];
  for (const [path, name] of payoutUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE G: Finance & Accounting
  console.log('\n--- Phase G: Finance & Accounting ---');
  const financeUrls = [
    ['/admin/finance', 'Finance Dashboard'],
    ['/admin/accounting', 'Accounting'],
    ['/admin/emi', 'EMI Dashboard'],
    ['/admin/payments', 'Payments'],
    ['/admin/invoices', 'Invoices'],
    ['/admin/expenses', 'Expenses'],
  ];
  for (const [path, name] of financeUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE H: CRM & Lead Pipeline
  console.log('\n--- Phase H: CRM & Lead Pipeline ---');
  const crmUrls = [
    ['/admin/leads', 'Leads List'],
    ['/admin/leads/scoring', 'Lead Scoring'],
    ['/admin/crm', 'CRM Dashboard'],
    ['/admin/crm/analytics', 'CRM Analytics'],
    ['/admin/crm/templates', 'Email/SMS Templates'],
    ['/admin/crm/segments', 'Lead Segments'],
    ['/admin/crm/forms', 'Lead Forms'],
    ['/admin/crm/drip', 'Drip Campaigns'],
    ['/admin/crm/sla', 'SLA Dashboard'],
  ];
  for (const [path, name] of crmUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE I: AI System
  console.log('\n--- Phase I: AI System ---');
  const aiUrls = [
    ['/admin/ai', 'AI Dashboard'],
    ['/admin/ai-settings', 'AI Settings'],
    ['/admin/agentic-ai', 'Agentic AI Dashboard'],
    ['/admin/ai-training', 'AI Training'],
  ];
  for (const [path, name] of aiUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE J: ERP & Dashboard
  console.log('\n--- Phase J: ERP & Dashboard ---');
  const erpUrls = [
    ['/admin/erp', 'ERP Overview'],
    ['/admin/dashboard', 'Admin Dashboard'],
    ['/admin/analytics', 'Analytics'],
    ['/admin/reports', 'Reports'],
    ['/admin/hrm/employees', 'HRM Employees'],
    ['/admin/backoffice', 'Backoffice Dashboard'],
    ['/admin/godmode', 'God Mode'],
  ];
  for (const [path, name] of erpUrls) {
    try {
      const { status } = await safeGoto(path);
      const pass = status === 200;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
    } catch (err) {
      results.fail++;
      check(name, false, `ERR: ${err.message}`);
    }
  }

  // PHASE K: Public Pages (fresh browser to avoid memory exhaustion from admin pages)
  console.log('\n--- Phase K: Public Pages ---');
  console.log('  [restart] Fresh browser for public pages...');
  await createBrowser();
  await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.waitForTimeout(500);

  const publicUrls = [
    ['/', 'Homepage'],
    ['/properties', 'Properties Listing'],
    ['/colonies', 'Colonies Listing'],
    ['/plots', 'Plots Listing'],
    ['/services', 'Business Directory'],
    ['/contact', 'Contact Page'],
    ['/login', 'Customer Login'],
    ['/register', 'Customer Register'],
    ['/associate/login', 'Associate Login'],
    ['/associate/register', 'Associate Register'],
    ['/careers', 'Careers Page'],
    ['/faq', 'FAQ Page'],
    ['/blog', 'Blog'],
  ];
  for (const [path, name] of publicUrls) {
    try {
      // Use domcontentloaded for public pages (lighter than full 'load')
      await restartIfNeeded();
      const response = await page.goto(`${BASE}${path}`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      const status = response.status();
      const pass = status === 200 || status === 302;
      pass ? results.pass++ : results.fail++;
      check(name, pass, `${status} ${path}`);
      // Brief pause to let JS execute without accumulating memory
      await page.waitForTimeout(300);
    } catch (err) {
      if (err.message.includes('crashed') || err.message.includes('Target closed')) {
        console.log('  [!] Crash on public page - restarting...');
        await createBrowser();
        await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded', timeout: 15000 });
        await page.waitForTimeout(500);
        // Retry once
        try {
          const response = await page.goto(`${BASE}${path}`, { waitUntil: 'domcontentloaded', timeout: 15000 });
          const status = response.status();
          const pass = status === 200 || status === 302;
          pass ? results.pass++ : results.fail++;
          check(name + ' (retry)', pass, `${status} ${path}`);
        } catch (retryErr) {
          results.fail++;
          check(name, false, `ERR: ${retryErr.message}`);
        }
      } else {
        results.fail++;
        check(name, false, `ERR: ${err.message}`);
      }
    }
  }

  // SUMMARY
  const total = results.pass + results.fail;
  console.log('\n' + '='.repeat(60));
  console.log(`WORKFLOW E2E: ${results.pass}/${total} PASS (${results.fail} FAIL)`);
  console.log('='.repeat(60));

  if (results.fail === 0) {
    console.log('\nALL WORKFLOW STEPS PASSED!');
  } else {
    console.log(`\n${results.fail} steps need attention.`);
  }

  await browser.close();
  process.exit(results.fail > 0 ? 1 : 0);
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
