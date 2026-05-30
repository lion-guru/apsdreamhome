const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });

  const results = [];
  
  await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1', { waitUntil: 'load' });
  await page.waitForTimeout(2000);
  
  const url = page.url();
  results.push({ page: 'Login', url, status: url.includes('admin/dashboard') ? 'PASS' : 'FAIL', issues: [] });

  const pages = [
    '/admin/dashboard', '/admin/analytics', '/admin/reports', '/admin/leads',
    '/admin/customers', '/admin/deals', '/admin/sales', '/admin/campaigns',
    '/admin/bookings', '/admin/properties', '/admin/projects', '/admin/plots',
    '/admin/sites', '/admin/network/tree', '/admin/network/ranks',
    '/admin/commission', '/admin/payouts', '/admin/payments', '/admin/accounting',
    '/admin/tasks', '/admin/support_tickets', '/admin/gallery', '/admin/testimonials',
    '/admin/news', '/admin/media', '/admin/engagement', '/admin/careers',
    '/admin/ai', '/admin/ai/analytics', '/admin/users', '/admin/locations/states',
    '/admin/locations/colonies', '/admin/settings', '/admin/plot-costs',
    '/admin/invoices', '/admin/roles', '/admin/hrm/employees', '/admin/inventory',
    '/admin/user-properties', '/admin/services', '/admin/newsletter',
    '/admin/scheduler', '/admin/loyalty', '/admin/blog', '/admin/expenses',
    '/admin/activity-log',
  ];

  for (const p of pages) {
    try {
      await page.goto('http://localhost/apsdreamhome' + p, { waitUntil: 'load', timeout: 15000 });
      await page.waitForTimeout(1000);
      
      const issues = [];
      const info = await page.evaluate(() => {
        const body = document.body;
        const text = body ? body.innerText : '';
        const hasScroll = document.documentElement.scrollWidth > document.documentElement.clientWidth;
        const hasSidebar = !!(document.querySelector('.sidebar, #sidebar, .app-sidebar, [class*=sidebar], .main-sidebar, .nav-sidebar'));
        const title = document.title || '';
        const phpErrors = text.match(/Fatal error|Parse error|Warning:|Notice:|Undefined/g);
        return { text, hasScroll, hasSidebar, title, phpErrors };
      });
      
      if (info.phpErrors) issues.push('PHP error in page: ' + info.phpErrors.slice(0, 3).join(', '));
      if (info.hasScroll) issues.push('Horizontal scroll detected (content wider than viewport)');
      if (!info.title || info.title === '') issues.push('Missing page title');
      if (!info.hasSidebar) issues.push('No sidebar detected on admin page');
      
      results.push({ page: p, url: page.url(), status: issues.length === 0 ? 'PASS' : 'WARN', issues });
    } catch (e) {
      results.push({ page: p, url: 'ERROR', status: 'FAIL', issues: [e.message] });
    }
  }

  const pass = results.filter(r => r.status === 'PASS').length;
  const warn = results.filter(r => r.status === 'WARN').length;
  const fail = results.filter(r => r.status === 'FAIL').length;
  
  console.log('==================== ADMIN DEEP SCAN ====================');
  console.log('Total: ' + results.length + ' | PASS: ' + pass + ' | WARN: ' + warn + ' | FAIL: ' + fail);
  console.log('');
  
  for (const r of results) {
    if (r.status !== 'PASS') {
      console.log('[' + r.status + '] ' + r.page + ' (' + r.url + ')');
      r.issues.forEach(i => console.log('       -> ' + i));
    }
  }
  
  if (warn === 0 && fail === 0) {
    console.log('All admin pages clean!');
  }

  await browser.close();
})();
