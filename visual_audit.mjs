import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE_URL = 'http://localhost/apsdreamhome';
const OUTPUT_DIR = 'C:\\xampp\\htdocs\\apsdreamhome\\visual_audit_output';

if (!fs.existsSync(OUTPUT_DIR)) fs.mkdirSync(OUTPUT_DIR, { recursive: true });

// Read admin menu items from DB or use known routes
const adminRoutes = [
  { name: 'Dashboard', url: '/admin/dashboard' },
  { name: 'ERP Overview', url: '/admin/erp' },
  { name: 'Colony Pipeline', url: '/admin/colony-pipeline' },
  { name: 'Bookings', url: '/admin/sales/bookings' },
  { name: 'Payments', url: '/admin/sales/payments' },
  { name: 'EMI Collection', url: '/admin/sales/emi-collection' },
  { name: 'Demand Letters', url: '/admin/sales/demand-letters' },
  { name: 'Commissions', url: '/admin/commission' },
  { name: 'MLM Dashboard', url: '/admin/mlm/dashboard' },
  { name: 'MLM Network', url: '/admin/mlm/network' },
  { name: 'MLM Payouts', url: '/admin/mlm/payouts' },
  { name: 'Finance Dashboard', url: '/admin/finance' },
  { name: 'Cash Book', url: '/admin/finance/cash-book' },
  { name: 'Bank Accounts', url: '/admin/finance/bank-accounts' },
  { name: 'Expenses', url: '/admin/finance/expenses' },
  { name: 'GST', url: '/admin/finance/gst' },
  { name: 'TDS', url: '/admin/finance/tds' },
  { name: 'Backoffice', url: '/admin/backoffice' },
  { name: 'Employees', url: '/admin/backoffice/employees' },
  { name: 'Attendance', url: '/admin/backoffice/attendance' },
  { name: 'Leaves', url: '/admin/backoffice/leaves' },
  { name: 'Payslips', url: '/admin/backoffice/payslips' },
  { name: 'Leads', url: '/admin/leads' },
  { name: 'CRM Dashboard', url: '/admin/crm' },
  { name: 'Deals', url: '/admin/deals' },
  { name: 'Tasks', url: '/admin/crm/tasks' },
  { name: 'Campaigns', url: '/admin/marketing_campaigns' },
  { name: 'Drip Campaigns', url: '/admin/drip_campaigns' },
  { name: 'Customers', url: '/admin/customers' },
  { name: 'Colonies', url: '/admin/colonies' },
  { name: 'Plots', url: '/admin/plots' },
  { name: 'Properties', url: '/admin/properties' },
  { name: 'Projects', url: '/admin/projects' },
  { name: 'Legal Documents', url: '/admin/legal' },
  { name: 'Agreements', url: '/admin/agreements' },
  { name: 'NOC Registry', url: '/admin/noc-registry' },
  { name: 'Site Visits', url: '/admin/site_visits' },
  { name: 'Notifications', url: '/admin/notifications' },
  { name: 'Referrals', url: '/admin/referrals' },
  { name: 'AI Dashboard', url: '/admin/ai' },
  { name: 'AI Calling', url: '/admin/ai/calling' },
  { name: 'Reports', url: '/admin/reports' },
  { name: 'System Health', url: '/admin/system-health' },
  { name: 'Audit Log', url: '/admin/audit-log' },
  { name: 'Cache', url: '/admin/cache' },
  { name: 'Settings', url: '/admin/site-settings' },
  { name: 'Users', url: '/admin/users' },
];

async function takeScreenshots() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1366, height: 768 },
    ignoreHTTPSErrors: true,
  });
  
  // Login first
  const page = await context.newPage();
  console.log('Logging in...');
  await page.goto(`${BASE_URL}/admin/login?test_login=1`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);
  
  // Get cookies for subsequent pages
  const cookies = await context.cookies();
  
  const results = [];
  
  for (const route of adminRoutes) {
    try {
      console.log(`Capturing: ${route.name} (${route.url})`);
      const p = await context.newPage();
      
      // Add cookies
      await context.addCookies(cookies);
      
      await p.goto(`${BASE_URL}${route.url}`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await p.waitForTimeout(2000); // Wait for JS rendering
      
      const safeName = route.name.replace(/[^a-z0-9]/gi, '_').toLowerCase();
      const filename = `${safeName}.png`;
      const filepath = path.join(OUTPUT_DIR, filename);
      
      await p.screenshot({ path: filepath, fullPage: true });
      
      // Check for console errors
      const errors = [];
      p.on('console', msg => {
        if (msg.type() === 'error') errors.push(msg.text());
      });
      
      // Check for JS errors in page
      const jsErrors = await p.evaluate(() => {
        return window.__jsErrors || [];
      }).catch(() => []);
      
      results.push({
        name: route.name,
        url: route.url,
        file: filename,
        status: 'success',
        consoleErrors: errors,
        jsErrors: jsErrors
      });
      
      await p.close();
      console.log(`  ✓ Saved: ${filename}`);
    } catch (e) {
      console.log(`  ✗ Failed: ${e.message}`);
      results.push({
        name: route.name,
        url: route.url,
        file: null,
        status: 'failed',
        error: e.message
      });
    }
  }
  
  await browser.close();
  
  // Save report
  fs.writeFileSync(
    path.join(OUTPUT_DIR, 'audit_report.json'),
    JSON.stringify(results, null, 2)
  );
  
  console.log('\n=== SUMMARY ===');
  const success = results.filter(r => r.status === 'success').length;
  const failed = results.filter(r => r.status === 'failed').length;
  console.log(`Success: ${success}/${results.length}`);
  console.log(`Failed: ${failed}`);
  console.log(`Screenshots in: ${OUTPUT_DIR}`);
  
  return results;
}

takeScreenshots().catch(console.error);