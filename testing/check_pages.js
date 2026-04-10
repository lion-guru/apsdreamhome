const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  
  console.log('=== Testing Admin Pages ===\n');
  
  const pages = [
    'admin/dashboard',
    'admin/payouts',
    'admin/mlm',
    'admin/commission',
    'admin/properties',
    'admin/plots',
    'admin/sites',
    'admin/bookings',
    'admin/leads',
    'admin/campaigns',
    'admin/gallery',
    'admin/users',
    'admin/settings',
    'admin/ai-settings',
    'admin/legal-pages'
  ];
  
  for (const p of pages) {
    try {
      await page.goto('http://localhost/apsdreamhome/' + p, { waitUntil: 'networkidle', timeout: 15000 });
      const html = await page.content();
      
      // Check structure
      const hasFullHtml = html.includes('<!DOCTYPE html>') && html.includes('<html') && html.includes('<head>') && html.includes('<body>');
      const hasSidebar = html.includes('.sidebar') || html.includes('id="sidebar"');
      const hasTopNav = html.includes('.top-nav');
      const title = await page.title();
      
      // Check for 500 errors in content
      const bodyText = await page.locator('body').innerText();
      const has500 = bodyText.includes('500') || bodyText.includes('Internal Server Error');
      
      console.log(p + ':');
      console.log('  Title: ' + title.substring(0, 40));
      console.log('  Full HTML: ' + hasFullHtml);
      console.log('  Has Sidebar: ' + hasSidebar);
      console.log('  Has TopNav: ' + hasTopNav);
      console.log('  500 Error: ' + has500);
      console.log('');
      
    } catch (e) {
      console.log(p + ': ERROR - ' + e.message.substring(0, 50));
    }
  }
  
  await browser.close();
  console.log('=== Done ===');
})();