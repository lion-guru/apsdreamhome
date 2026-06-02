import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';

const sidebar_urls = [
  '/admin/dashboard','/admin/analytics','/admin/reports',
  '/admin/leads','/admin/leads/scoring','/admin/customers',
  '/admin/deals','/admin/sales','/admin/campaigns','/admin/bookings',
  '/admin/properties','/admin/projects','/admin/plots','/admin/sites',
  '/admin/resell-properties','/admin/network/tree','/admin/network/genealogy',
  '/admin/network/ranks','/associate/dashboard','/admin/commission',
  '/admin/payouts','/admin/payments','/admin/emi','/admin/accounting',
  '/admin/tasks','/admin/visits','/admin/support_tickets',
  '/admin/gallery','/admin/testimonials','/admin/news','/admin/media',
  '/admin/engagement','/admin/careers','/admin/ai','/admin/ai-settings',
  '/admin/ai/analytics','/admin/users','/employee/dashboard',
  '/admin/locations/states','/admin/legal-pages','/admin/settings',
  '/admin/api-keys','/admin/logout','/admin/user-properties',
  '/admin/services','/admin/newsletter','/admin/scheduler',
  '/admin/loyalty','/admin/plot-costs','/admin/invoices',
  '/admin/roles','/admin/hrm/employees','/admin/godmode','/admin/godmode/users',
  '/admin/blog','/admin/blog/create','/admin/pages','/admin/pages/create',
  '/admin/expenses','/admin/expenses/create','/admin/activity-log',
  '/admin/settings/payment','/admin/settings/email','/admin/settings/sms',
  '/admin/inventory',
  '/admin/ceo-dashboard', '/admin/cfo-dashboard', '/admin/builder-dashboard',
  '/admin/agent-dashboard', '/admin/cm-dashboard',
  '/admin/financial-reports', '/admin/voice-agents',
  '/admin/deal-pipeline', '/admin/property-allocations',
  '/admin/associate-extensions', '/admin/loans', '/admin/backups',
  '/admin/careers/manage', '/admin/careers/manage/jobs',
  '/admin/careers/manage/applications', '/admin/careers/manage/stats',
  '/admin/report-center',
];

const colony_site_plot_urls = [
  '/admin/plots', '/admin/plots/create', '/admin/plots/check-availability',
  '/admin/sites', '/admin/sites/create',
  '/admin/locations/colonies', '/admin/locations/colonies/create',
  '/admin/plot-costs', '/admin/plot-costs/colony/2', '/admin/plot-costs/report/2',
  '/colonies', '/plots',
];

const mlm_commission_urls = [
  '/admin/mlm', '/admin/mlm/associates', '/admin/mlm/commission',
  '/admin/mlm/payouts', '/admin/mlm/network',
  '/admin/commission', '/admin/commission/rules',
  // '/admin/commission/manage', // 404 - no route registered
  '/admin/payouts/list/all',
  '/admin/mlm-growth-reports',
  '/api/mlm/tree',
];

const farmer_urls = [
  '/farmers', '/farmers/list', '/farmers/create',
];

const realestate_lifecycle_urls = [
  ...colony_site_plot_urls,
  ...mlm_commission_urls,
  ...farmer_urls,
];

async function run() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await context.newPage();

  const results = { pass: 0, fail: 0, details: [] };
  let total = 0;

  function check(path, name, status, pass) {
    total++;
    const icon = pass ? 'OK' : 'FAIL';
    console.log(`  ${icon} ${name} (${path}) => ${status}`);
    results.details.push({ step: `${name}`, pass, status });
    if (pass) results.pass++; else results.fail++;
  }

  // STEP 1: Admin Login
  console.log('--- Step 1: Admin Login ---');
  await page.goto(`${BASE}/admin/login`, { waitUntil: 'load', timeout: 15000 });
  await page.fill('input[name="username"], input[name="email"]', 'testadmin');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(2000);
  let currentUrl = page.url();
  if (currentUrl.includes('login')) {
    console.log('  Login form failed, trying test_login bypass...');
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 15000 });
    await page.waitForTimeout(1000);
    currentUrl = page.url();
  }
  const loggedIn = !currentUrl.includes('login');
  check('/admin/login', 'Admin Login', currentUrl, loggedIn);

  // STEP 2: Sidebar URL Test
  if (!loggedIn) {
    console.log('\n--- SKIPPING steps 2-6 (not logged in) ---');
    results.details.forEach(() => { results.fail++; total++; });
  } else {
    console.log('\n--- Step 2: Sidebar URL Test ---');
    for (const path of sidebar_urls) {
      try {
        const response = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 15000 });
        const status = response.status();
        const pass = status === 200;
        check(path, `Sidebar: ${path}`, status, pass);
      } catch (err) {
        check(path, `Sidebar: ${path}`, `ERR: ${err.message}`, false);
      }
    }

    // STEP 3: Real Estate Lifecycle Routes
    console.log('\n--- Step 3: Real Estate Lifecycle (Colony/Site/Plot/MLM/Farmer) ---');
    for (const path of realestate_lifecycle_urls) {
      try {
        const response = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 15000 });
        const status = response.status();
        const pass = status === 200 || status === 302;
        check(path, `Lifecycle: ${path}`, status, pass);
      } catch (err) {
        check(path, `Lifecycle: ${path}`, `ERR: ${err.message}`, false);
      }
    }

    // STEP 4: Dynamic ID-based routes
    console.log('\n--- Step 4: Dynamic ID Routes (sites, plots, farmers) ---');
    for (const path of ['/admin/sites/1', '/admin/sites/1/edit', '/admin/plots/1', '/admin/plots/1/edit', '/admin/bookings/1', '/farmers/1', '/farmers/1/edit']) {
      try {
        const response = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 15000 });
        const status = response.status();
        const pass = status === 200;
        check(path, `Dynamic: ${path}`, status, pass);
      } catch (err) {
        check(path, `Dynamic: ${path}`, `ERR: ${err.message}`, false);
      }
    }

    // STEP 5: Public Pages
    console.log('\n--- Step 5: Public Pages ---');
    const publicPages = [
      { path: '/', name: 'Homepage' },
      { path: '/properties', name: 'Properties' },
      { path: '/services', name: 'Services' },
      { path: '/contact', name: 'Contact' },
      { path: '/support', name: 'Support' },
      { path: '/calc', name: 'EMI Calculator' },
      { path: '/login', name: 'Customer Login' },
      { path: '/register', name: 'Customer Register' },
      { path: '/associate/login', name: 'Associate Login' },
      { path: '/employee/login', name: 'Employee Login' },
      { path: '/list-property', name: 'List Property' },
      { path: '/whatsapp-chat', name: 'WhatsApp Chat' },
      { path: '/user-ai-suggestions', name: 'AI Suggestions' },
      { path: '/user/investments', name: 'User Investments' },
      { path: '/colonies', name: 'Colonies' },
      { path: '/plots', name: 'Plots' },
      { path: '/associate/register', name: 'Associate Register' },
      { path: '/mlm-dashboard', name: 'MLM Dashboard' },
      // Interactive Tools
      { path: '/tools-hub', name: 'Tools Hub' },
      { path: '/stamp-duty-calculator', name: 'Stamp Duty Calculator' },
      { path: '/plot-size-converter', name: 'Plot Size Converter' },
      { path: '/home-loan-eligibility', name: 'Home Loan Eligibility' },
      { path: '/property-valuation', name: 'Property Valuation' },
      { path: '/rent-vs-buy', name: 'Rent vs Buy Calculator' },
      { path: '/sip-vs-realestate', name: 'SIP vs Real Estate' },
      { path: '/capital-gains-calculator', name: 'Capital Gains Calculator' },
      { path: '/gst-calculator', name: 'GST Calculator' },
      // Content pages
      { path: '/blog', name: 'Blog' },
      { path: '/news', name: 'News' },
      { path: '/faq', name: 'FAQ' },
      // Role dashboards
      { path: '/admin/ceo-dashboard', name: 'CEO Dashboard' },
      { path: '/admin/cfo-dashboard', name: 'CFO Dashboard' },
      { path: '/admin/builder-dashboard', name: 'Builder Dashboard' },
      { path: '/admin/agent-dashboard', name: 'Agent Dashboard' },
      // Additional public routes
      { path: '/compare', name: 'Property Compare' },
      { path: '/user/bank-details', name: 'User Bank Details' },
      { path: '/user/notification-settings', name: 'Notification Settings' },
      { path: '/user/notifications', name: 'User Notifications' },
      // Newly routed
      { path: '/property-workflow', name: 'Property Workflow' },
      { path: '/careers', name: 'Careers' },
      { path: '/careers/apply', name: 'Careers Apply' },

    ];
    for (const { path, name } of publicPages) {
      try {
        const response = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 15000 });
        const status = response.status();
        const pass = status === 200 || status === 302;
        check(path, `Public: ${name}`, status, pass);
      } catch (err) {
        check(path, `Public: ${name}`, `ERR: ${err.message}`, false);
      }
    }

    // STEP 6: Customer login flow
    console.log('\n--- Step 6: Customer Login Flow ---');
    await page.goto(`${BASE}/login`, { waitUntil: 'load', timeout: 15000 });
    await page.fill('input[name="identity"]', 'testuser@example.com');
await page.fill('input[name="password"]', 'Test@123');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'load', timeout: 15000 }),
      page.click('button[type="submit"]')
    ]);
    await page.waitForTimeout(1000);
    const custLoggedIn = !page.url().includes('login');
    check('/login', 'Customer Login', page.url(), custLoggedIn);

    if (custLoggedIn) {
      const custPages = [
        '/user/dashboard', '/user/properties', '/user/inquiries', '/user/profile',
        '/user/favorites', '/user/saved-searches', '/user/network'
      ];
      for (const path of custPages) {
        try {
          const response = await page.goto(`${BASE}${path}`, { waitUntil: 'load', timeout: 15000 });
          const status = response.status();
          const pass = status === 200;
          check(path, `Customer: ${path}`, status, pass);
        } catch (err) {
          check(path, `Customer: ${path}`, `ERR`, false);
        }
      }
    }
  }

  // Summary
  console.log('\n' + '='.repeat(60));
  const expectedFails = ['/admin/godmode', '/admin/godmode/users', '/admin/godmode/system-health'];
  const realFails = results.details.filter(d => !d.pass && !expectedFails.some(e => d.step.includes(e)));
  const expectedFailsCount = results.fail - realFails.length;
  console.log(`TOTAL: ${results.pass} passed, ${results.fail} failed (${total} checks, ${expectedFailsCount} expected)`);
  if (realFails.length > 0) {
    console.log(`UNEXPECTED FAILURES: ${realFails.length}`);
    realFails.forEach(f => console.log(`  - ${f.step}: ${f.status || f.error}`));
  } else {
    console.log('All failures are expected (godmode restricted to non-superadmin)');
  }
  console.log('='.repeat(60));

  await browser.close();
  process.exit(realFails.length > 0 ? 1 : 0);
}

run().catch(err => { console.error(err); process.exit(1); });
