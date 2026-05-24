import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = 'audit_results';
mkdirSync(SCREENSHOT_DIR, { recursive: true });

const results = [];
let errors = 0, warnings = 0;

function report(type, page, status, detail) {
  const icon = status === 'OK' ? '✅' : status === 'WARN' ? '⚠️' : '❌';
  const msg = `${icon} [${type}] ${page}: ${detail}`;
  results.push(msg);
  if (status === 'OK') return;
  if (status === 'WARN') warnings++;
  else errors++;
}

async function auditPage(page, url, name, screenshot = true) {
  const fullUrl = url.startsWith('http') ? url : BASE + url;
  const consoleErrors = [];
  const resourceErrors = [];

  page.on('console', msg => {
    if (msg.type() === 'error') consoleErrors.push(msg.text());
  });
  page.on('response', resp => {
    if (resp.status() >= 400) resourceErrors.push(`${resp.url().substring(0,80)} (${resp.status()})`);
  });

  try {
    const resp = await page.goto(fullUrl, { waitUntil: 'load', timeout: 15000 });
    await page.waitForTimeout(1500);

    const status = resp ? resp.status() : 0;
    const title = await page.title();
    const bodyText = await page.evaluate(() => document.body?.innerText?.length || 0);
    const hasDocType = await page.evaluate(() => document.doctype !== null);

    // Check basic page structure
    const hasViewport = await page.evaluate(() => {
      const meta = document.querySelector('meta[name="viewport"]');
      return meta !== null;
    });
    const hasTitle = title && title.length > 0 && title !== 'APS Dream Home';
    const hasContent = bodyText > 50;

    let issues = [];

    // Status check
    if (status >= 400) {
      issues.push(`HTTP ${status}`);
    }

    // Console errors
    if (consoleErrors.length > 0) {
      const unique = [...new Set(consoleErrors)];
      issues.push(`${unique.length} console error(s): ${unique[0].substring(0,60)}`);
    }

    // Resource errors
    if (resourceErrors.length > 0) {
      const unique = [...new Set(resourceErrors)];
      issues.push(`${unique.length} resource 404(s): ${unique[0].substring(0,60)}`);
    }

    // Missing viewport
    if (!hasViewport) issues.push('Missing viewport meta');

    // Missing/empty title
    if (!hasTitle) issues.push('Missing/empty page title');

    // Empty body
    if (!hasContent) issues.push('Page body appears empty');

    // Check for broken layout (DOCTYPE check)
    if (!hasDocType) issues.push('Missing DOCTYPE');

    const verdict = issues.length === 0 ? 'OK' : 'WARN';
    const detail = issues.length > 0 ? issues.join('; ') : `title="${title.substring(0,50)}" body=${bodyText}b`;

    report(name, fullUrl.substring(BASE.length) || '/', verdict, detail);

    if (screenshot && verdict !== 'OK') {
      const fname = name.replace(/[^a-z0-9]/gi, '_').toLowerCase();
      await page.screenshot({ path: `${SCREENSHOT_DIR}/${fname}_issue.png`, fullPage: true });
    }

  } catch (e) {
    report(name, fullUrl.substring(BASE.length) || '/', 'FAIL', e.message.substring(0,80));
  }

  // Clear listeners
  page.removeAllListeners('console');
  page.removeAllListeners('response');
}

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });

  // ====== Frontend Pages ======
  console.log('=== SCANNING FRONTEND PAGES ===\n');
  const frontContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const frontPage = await frontContext.newPage();

  const frontPages = [
    { url: '/', name: 'Homepage' },
    { url: '/properties', name: 'Properties' },
    { url: '/services', name: 'Services' },
    { url: '/contact', name: 'Contact' },
    { url: '/support', name: 'Support' },
    { url: '/calc', name: 'EMI Calculator' },
    { url: '/login', name: 'Customer Login' },
    { url: '/register', name: 'Customer Register' },
    { url: '/list-property', name: 'List Property' },
    { url: '/colonies', name: 'Colonies' },
    { url: '/plots', name: 'Plots' },
    { url: '/whatsapp-chat', name: 'WhatsApp Chat' },
    { url: '/user-ai-suggestions', name: 'AI Suggestions' },
    { url: '/user/investments', name: 'User Investments' },
    { url: '/mlm-dashboard', name: 'MLM Dashboard' },
    { url: '/tools-hub', name: 'Tools Hub' },
    { url: '/stamp-duty-calculator', name: 'Stamp Duty Calc' },
    { url: '/plot-size-converter', name: 'Plot Size Converter' },
    { url: '/home-loan-eligibility', name: 'Home Loan Eligibility' },
    { url: '/property-valuation', name: 'Property Valuation' },
    { url: '/rent-vs-buy', name: 'Rent vs Buy Calc' },
    { url: '/sip-vs-realestate', name: 'SIP vs Real Estate' },
    { url: '/capital-gains-calculator', name: 'Capital Gains Calc' },
    { url: '/gst-calculator', name: 'GST Calculator' },
    { url: '/properties/submit', name: 'Property Submit' },
    { url: '/properties/list', name: 'Property List Alt' },
    { url: '/news', name: 'News' },
    { url: '/resell', name: 'Resell' },
    { url: '/senior-developer', name: 'Senior Dev' },
    { url: '/downloads', name: 'Downloads' },
    { url: '/associate/login', name: 'Associate Login' },
    { url: '/employee/login', name: 'Employee Login' },
    { url: '/associate/register', name: 'Associate Register' },
    { url: '/farmers', name: 'Farmers' },
    { url: '/farmers/list', name: 'Farmers List' },
    { url: '/farmers/create', name: 'Farmers Create' },
    { url: '/colony/gorakhpur/plots', name: 'Colony Plots' },
  ];

  for (const p of frontPages) {
    await auditPage(frontPage, p.url, p.name, true);
  }
  await frontContext.close();

  // ====== Admin Pages (logged in) ======
  console.log('\n=== SCANNING ADMIN PAGES ===\n');
  const adminContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const adminPage = await adminContext.newPage();

  // Login first
  await adminPage.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'networkidle' });
  await adminPage.waitForTimeout(2000);

  const adminPages = [
    '/admin/dashboard',
    '/admin/analytics',
    '/admin/reports',
    '/admin/leads',
    '/admin/leads/scoring',
    '/admin/customers',
    '/admin/deals',
    '/admin/sales',
    '/admin/campaigns',
    '/admin/bookings',
    '/admin/properties',
    '/admin/projects',
    '/admin/plots',
    '/admin/sites',
    '/admin/resell-properties',
    '/admin/network/tree',
    '/admin/network/genealogy',
    '/admin/network/ranks',
    '/admin/commission',
    '/admin/payouts',
    '/admin/payments',
    '/admin/invoices',
    '/admin/expenses',
    '/admin/accounting',
    '/admin/tasks',
    '/admin/visits',
    '/admin/gallery',
    '/admin/testimonials',
    '/admin/news',
    '/admin/media',
    '/admin/engagement',
    '/admin/careers',
    '/admin/ai',
    '/admin/ai-settings',
    '/admin/ai/analytics',
    '/admin/users',
    '/admin/roles',
    '/admin/locations/states',
    '/admin/locations/districts',
    '/admin/locations/colonies',
    '/admin/legal-pages',
    '/admin/settings',
    '/admin/api-keys',
    '/admin/user-properties',
    '/admin/services',
    '/admin/newsletter',
    '/admin/scheduler',
    '/admin/loyalty',
    '/admin/plot-costs',
    '/admin/hrm/employees',
    '/admin/hrm/attendance',
    '/admin/hrm/leave',
    '/admin/hrm/payroll',
    '/admin/hrm/performance',
    '/admin/hrm/recruitment',
    '/admin/hrm/jobs',
    '/admin/hrm/applicants',
    '/admin/hrm/documents',
    '/admin/hrm/departments',
    '/admin/hrm/designations',
    '/admin/hrm/settings',
    '/admin/blog',
    '/admin/pages',
    '/admin/activity-log',
    '/admin/settings/payment',
    '/admin/settings/email',
    '/admin/settings/sms',
    '/admin/inventory',
    '/admin/godmode',
    // Newly added submenu routes
    '/admin/leads/status',
    '/admin/leads/followups',
    '/admin/leads/import',
    '/admin/leads/analysis',
    '/admin/plots/categories',
  ];

  for (const url of adminPages) {
    await auditPage(adminPage, url, `Admin: ${url.replace('/admin/', '')}`, true);
  }
  await adminContext.close();

  // ====== Customer Pages (logged in) ======
  console.log('\n=== SCANNING CUSTOMER PAGES ===\n');
  const custContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const custPage = await custContext.newPage();

  await custPage.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await custPage.waitForTimeout(500);
  // Fill login form
  await custPage.fill('input[name="identity"], input[name="email"]', 'test@example.com');
  await custPage.fill('input[name="password"]', 'Test@123');
  await custPage.click('button[type="submit"]');
  await custPage.waitForTimeout(3000);

  const custPages = [
    '/user/dashboard',
    '/user/properties',
    '/user/inquiries',
    '/user/profile',
    '/user/investments',
  ];

  for (const url of custPages) {
    await auditPage(custPage, url, `User: ${url.replace('/user/', '')}`, true);
  }
  await custContext.close();

  await browser.close();

  // ====== REPORT ======
  console.log('\n========== AUDIT REPORT ==========\n');
  console.log(`Total: ${results.length} checks, ${errors} errors, ${warnings} warnings\n`);

  const fails = results.filter(r => r.includes('[FAIL]'));
  const warns = results.filter(r => r.includes('[WARN]'));

  if (fails.length > 0) {
    console.log('--- FAILURES ---');
    fails.forEach(f => console.log(f));
    console.log();
  }

  if (warns.length > 0) {
    console.log('--- WARNINGS ---');
    warns.forEach(w => console.log(w));
    console.log();
  }

  if (fails.length === 0 && warns.length === 0) {
    console.log('🎉 All pages passed with zero issues!');
  }

  // Save report
  const reportContent = [
    `APS Dream Home - Deep UI/UX Audit Report`,
    `Date: ${new Date().toISOString()}`,
    `Total: ${results.length} checks, ${errors} errors, ${warnings} warnings`,
    ``,
    ...results
  ].join('\n');
  writeFileSync(`${SCREENSHOT_DIR}/audit_report.txt`, reportContent);
  console.log(`\nReport saved to ${SCREENSHOT_DIR}/audit_report.txt`);

  process.exit(fails.length > 0 ? 1 : 0);
})();
