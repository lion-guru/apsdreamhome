const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  
  // ALL menu items from DB with URLs
  const menuItems = [
    // [section] name -> url
    { sec: 'main', name: 'Dashboard', url: '/admin/dashboard' },
    { sec: 'main', name: 'Analytics', url: '/admin/analytics' },
    { sec: 'main', name: 'All Leads', url: '/admin/leads' },
    { sec: 'main', name: 'All Posts', url: '/admin/news' },
    { sec: 'main', name: 'Add Post', url: '/admin/news/create' },
    { sec: 'main', name: 'All Pages', url: '/admin/pages' },
    { sec: 'main', name: 'General Settings', url: '/admin/settings' },
    { sec: 'main', name: 'Payment Settings', url: '/admin/settings/payment' },
    { sec: 'main', name: 'Email Settings', url: '/admin/settings/email' },
    { sec: 'main', name: 'SMS Settings', url: '/admin/settings/sms' },
    { sec: 'main', name: 'Gallery', url: '/admin/gallery' },
    { sec: 'main', name: 'Legal Pages', url: '/admin/legal-pages' },
    { sec: 'main', name: 'Resell Properties', url: '/admin/resell-properties' },
    { sec: 'main', name: 'Projects', url: '/admin/projects' },
    { sec: 'main', name: 'Careers', url: '/admin/careers' },
    { sec: 'main', name: 'Blog Posts', url: '/admin/blog' },
    { sec: 'main', name: 'Sales Report', url: '/admin/reports/sales' },
    { sec: 'main', name: 'Lead Report', url: '/admin/reports/leads' },
    { sec: 'main', name: 'Commission Report', url: '/admin/reports/commission' },
    { sec: 'main', name: 'Bookings', url: '/admin/bookings' },
    { sec: 'main', name: 'MLM & Enterprise', url: '/admin/mlm-realestate' },
    { sec: 'main', name: 'Sites', url: '/admin/sites' },
    { sec: 'main', name: 'Inquiries', url: '/admin/inquiries' },
    { sec: 'main', name: 'Plots', url: '/admin/plots' },
    { sec: 'main', name: 'Locations', url: '/admin/locations/states' },
    { sec: 'main', name: 'News', url: '/admin/news' },
    { sec: 'main', name: 'Campaigns', url: '/admin/campaigns' },
    { sec: 'main', name: 'Visits', url: '/admin/visits' },
    { sec: 'main', name: 'Deals', url: '/admin/deals' },
    { sec: 'main', name: 'Testimonials', url: '/admin/testimonials' },
    { sec: 'main', name: 'API Keys', url: '/admin/api-keys' },
    { sec: 'main', name: 'Services', url: '/admin/services' },
    { sec: 'main', name: 'User Properties', url: '/admin/user-properties' },
    { sec: 'main', name: 'Plot Costs', url: '/admin/plot-costs' },
    { sec: 'main', name: 'AI Settings', url: '/admin/ai_settings' },
    { sec: 'main', name: 'MLM', url: '/admin/mlm' },
    { sec: 'main', name: 'Commission', url: '/admin/commission' },
    { sec: 'main', name: 'Payouts', url: '/admin/payouts' },
    { sec: 'main', name: 'Reports', url: '/admin/reports' },
    { sec: 'main', name: 'Properties', url: '/admin/properties' },
    { sec: 'main', name: 'Users', url: '/admin/users' },
    { sec: 'main', name: 'Agent Dashboard', url: '/admin/dashboard/agent' },
    { sec: 'main', name: 'Builder Dashboard', url: '/admin/dashboard/builder' },
    { sec: 'main', name: 'CEO Dashboard', url: '/admin/dashboard/ceo' },
    { sec: 'main', name: 'CFO Dashboard', url: '/admin/dashboard/cfo' },
    { sec: 'main', name: 'CM Dashboard', url: '/admin/dashboard/cm' },
    { sec: 'main', name: 'COO Dashboard', url: '/admin/dashboard/coo' },
    { sec: 'main', name: 'CTO Dashboard', url: '/admin/dashboard/cto' },
    { sec: 'main', name: 'Director Dashboard', url: '/admin/dashboard/director' },
    { sec: 'main', name: 'Finance Dashboard', url: '/admin/dashboard/finance' },
    { sec: 'main', name: 'HR Dashboard', url: '/admin/dashboard/hr' },
    { sec: 'main', name: 'IT Dashboard', url: '/admin/dashboard/it' },
    { sec: 'main', name: 'Marketing Dashboard', url: '/admin/dashboard/marketing' },
    { sec: 'main', name: 'Operations Dashboard', url: '/admin/dashboard/operations' },
    { sec: 'main', name: 'Sales Dashboard', url: '/admin/dashboard/sales' },
    { sec: 'main', name: 'Super Admin Dashboard', url: '/admin/dashboard/superadmin' },
    { sec: 'main', name: 'Role Dashboards', url: '#' },
    { sec: 'main', name: 'Properties (dup)', url: '/admin/properties' },
    
    { sec: 'crm', name: 'Add Lead', url: '/admin/leads/create' },
    { sec: 'crm', name: 'Lead Sources', url: '/admin/leads/sources' },
    { sec: 'crm', name: 'Lead Status', url: '/admin/leads/status' },
    { sec: 'crm', name: 'Lead Follow-up', url: '/admin/leads/followups' },
    { sec: 'crm', name: 'Lead Scoring', url: '/admin/leads/scoring' },
    { sec: 'crm', name: 'AI Import', url: '/admin/leads/import' },
    { sec: 'crm', name: 'Analysis', url: '/admin/leads/analysis' },
    { sec: 'crm', name: 'Bulk Actions', url: '/admin/leads' },
    { sec: 'crm', name: 'Telecalling', url: '#' },
    { sec: 'crm', name: 'Tele Dashboard', url: '/admin/telecalling/dashboard' },
    { sec: 'crm', name: 'Assign Leads', url: '/admin/telecalling/assign' },
    { sec: 'crm', name: 'Commissions', url: '/admin/telecalling/commissions' },
    { sec: 'crm', name: 'Approvals', url: '/admin/telecalling/approvals' },
    { sec: 'crm', name: 'Leads (dup)', url: '/admin/leads' },
    
    { sec: 'mlm', name: 'Network Tree', url: '/admin/network/tree' },
    { sec: 'mlm', name: 'Genealogy', url: '/admin/network/genealogy' },
    { sec: 'mlm', name: 'Ranks', url: '/admin/network/ranks' },
    { sec: 'mlm', name: 'All Associates', url: '/admin/mlm/associates' },
    { sec: 'mlm', name: 'Add Associate', url: '/admin/mlm/associates/create' },
    { sec: 'mlm', name: 'Commissions', url: '/admin/commission' },
    { sec: 'mlm', name: 'MLM Settings', url: '/admin/mlm-settings/levels' },
    { sec: 'mlm', name: 'Commission Rules', url: '/admin/mlm-settings/rules' },
    { sec: 'mlm', name: 'Rank Evaluation', url: '/admin/mlm-settings/evaluate' },
    { sec: 'mlm', name: 'Rank Progress', url: '/admin/mlm-settings/associate-progress' },
    
    { sec: 'bookings_plots', name: 'Bookings & Plots', url: '#' },
    { sec: 'bookings_plots', name: 'Add Booking', url: '/admin/bookings/create' },
    { sec: 'bookings_plots', name: 'Add Plot', url: '/admin/plots/create' },
    { sec: 'bookings_plots', name: 'Plot Categories', url: '/admin/plots/categories' },
    
    { sec: 'financial', name: 'Payments', url: '/admin/payments' },
    { sec: 'financial', name: 'Invoices', url: '/admin/invoices' },
    { sec: 'financial', name: 'Expenses', url: '/admin/expenses' },
    
    { sec: 'ai', name: 'AI Calling', url: '#' },
    
    { sec: 'locations', name: 'Districts', url: '/admin/locations/districts' },
    { sec: 'locations', name: 'Colonies', url: '/admin/locations/colonies' },
    
    { sec: 'operations', name: 'Tasks', url: '/admin/tasks' },
    
    { sec: 'properties', name: 'Colony Management', url: '/admin/colonies' },
    
    { sec: 'hrm', name: 'All Employees', url: '/admin/hrm/employees' },
    
    { sec: 'marketing', name: 'All Campaigns', url: '/admin/campaigns' },
    
    { sec: 'users', name: 'Employees', url: '/admin/employees' },
    { sec: 'users', name: 'Roles', url: '/admin/roles' },
    
    { sec: 'settings', name: 'Menu Permissions', url: '/admin/menu-permissions' },
    { sec: 'settings', name: 'God Mode', url: '/admin/godmode' },
    { sec: 'settings', name: 'Activity Log', url: '/admin/activity-log' },
    { sec: 'settings', name: 'Email Settings', url: '/admin/settings/email' },
    { sec: 'settings', name: 'SMS Settings', url: '/admin/settings/sms' },
    { sec: 'settings', name: 'Payment Settings', url: '/admin/settings/payment' },
  ];
  
  const page = await browser.newPage();
  
  // Login as admin
  console.log('Logging in...');
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load', timeout: 15000 });
  console.log('Login done, URL: ' + page.url());
  
  // Test each URL
  const results = [];
  const broken = [];
  
  for (const item of menuItems) {
    if (item.url === '#') {
      results.push({ ...item, status: 'SKIP', title: 'placeholder', bodyLen: 0 });
      continue;
    }
    
    try {
      const resp = await page.goto('http://localhost/apsdreamhome' + item.url, { waitUntil: 'load', timeout: 15000 });
      const status = resp.status();
      const title = await page.title();
      const bodyText = await page.evaluate(() => document.body.innerText);
      const hasError = bodyText.includes('500') || bodyText.includes('Internal Server Error') || bodyText.includes('Fatal error');
      
      results.push({ ...item, status, title: title.substring(0, 60), bodyLen: bodyText.length, hasError });
      
      if (status !== 200 && status !== 302 && status !== 301) {
        broken.push({ ...item, status, reason: 'HTTP ' + status });
      } else if (hasError) {
        broken.push({ ...item, status, reason: 'Page has 500/fatal error text' });
      } else if (bodyText.length < 50) {
        broken.push({ ...item, status, reason: 'Empty/too short body (' + bodyText.length + ' chars)' });
      }
    } catch(e) {
      results.push({ ...item, status: 'ERR', title: e.message.substring(0, 60), bodyLen: 0, hasError: true });
      broken.push({ ...item, status: 'ERR', reason: e.message.substring(0, 100) });
    }
  }
  
  // Print results
  console.log('\n=== RESULTS BY SECTION ===\n');
  let currentSec = '';
  let ok = 0, fail = 0, skip = 0;
  
  for (const r of results) {
    if (r.sec !== currentSec) {
      currentSec = r.sec;
      console.log('\n--- ' + currentSec.toUpperCase() + ' ---');
    }
    if (r.url === '#') {
      console.log('  [SKIP] ' + r.name + ' (placeholder)');
      skip++;
    } else if (r.status === 200 || r.status === 302 || r.status === 301) {
      let note = '';
      if (r.hasError) note = ' <-- HAS 500 TEXT';
      console.log('  [OK] ' + r.name + ' -> ' + r.url + ' (' + r.status + ', ' + r.bodyLen + ' chars)' + note);
      ok++;
    } else {
      console.log('  [FAIL] ' + r.name + ' -> ' + r.url + ' (' + r.status + ')');
      fail++;
    }
  }
  
  console.log('\n\n=== SUMMARY ===');
  console.log('Total: ' + results.length + ', OK: ' + ok + ', FAIL: ' + fail + ', SKIP: ' + skip);
  
  if (broken.length > 0) {
    console.log('\n=== BROKEN ITEMS ===');
    broken.forEach(b => console.log('  [' + b.status + '] ' + b.sec + '/' + b.name + ' -> ' + b.url + ': ' + b.reason));
  }
  
  await browser.close();
})();
