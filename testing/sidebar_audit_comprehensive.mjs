import { chromium } from 'playwright';

const BASE = 'http://localhost/apsdreamhome';

async function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

(async () => {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await ctx.newPage();

  // 1. Login as admin via test-login bypass
  console.log('=== Admin Login ===');
  await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'networkidle' });
  await sleep(2000);
  console.log('URL after login:', page.url());
  await page.screenshot({ path: 'testing/screenshots/admin_after_login.png', fullPage: false });

  // 2. Capture full sidebar HTML structure
  const sidebarHtml = await page.evaluate(() => {
    const sidebar = document.querySelector('.sidebar, #sidebar, .main-sidebar, nav.sidebar, [class*="sidebar"]');
    if (!sidebar) return 'NO SIDEBAR FOUND';
    // Get all top-level nav items
    const items = sidebar.querySelectorAll('li.nav-item, li.menu-item, li, a.nav-link, a.menu-link');
    const result = [];
    items.forEach(item => {
      const text = item.textContent.trim().substring(0, 60);
      const href = item.getAttribute('href') || '';
      const cls = item.className;
      const hasSubmenu = item.querySelector('ul, .submenu, .collapse, .dropdown-menu') !== null;
      result.push({ text, href: href.substring(0, 60), cls: cls.substring(0, 40), hasSubmenu });
    });
    return result;
  });

  console.log(`\n=== Sidebar Items (${sidebarHtml.length} found) ===`);
  sidebarHtml.forEach((item, i) => {
    const sub = item.hasSubmenu ? ' [HAS SUBMENU]' : '';
    console.log(`  ${i+1}. ${item.text}${sub}`);
  });

  // 3. Get all sections from sidebar
  const sections = await page.evaluate(() => {
    const sidebar = document.querySelector('.sidebar, #sidebar, .main-sidebar, nav.sidebar, [class*="sidebar"]');
    if (!sidebar) return [];
    const headings = sidebar.querySelectorAll('h3, h4, h5, .nav-header, .header, .menu-header, [class*="header"], [class*="heading"]');
    const result = [];
    headings.forEach(h => result.push(h.textContent.trim()));
    return result;
  });

  console.log(`\n=== Sidebar Sections (${sections.length}) ===`);
  sections.forEach((s, i) => console.log(`  ${i+1}. ${s}`));

  // 4. Visit critical parent menus to check submenus render
  const parentMenus = [
    '/admin/leads',
    '/admin/locations/states',
    '/admin/mlm/associates',
    '/admin/commission',
    '/admin/payouts',
    '/admin/properties',
    '/admin/colonies',
    '/admin/sites',
    '/admin/plots',
    '/admin/bookings',
    '/admin/payments',
    '/admin/invoices',
    '/admin/expenses',
    '/admin/hrm/employees',
    '/admin/roles',
    '/admin/users',
    '/admin/reports',
    '/admin/analytics',
    '/admin/ai_settings',
    '/admin/godmode',
  ];

  console.log(`\n=== Visiting ${parentMenus.length} parent menu pages ===`);
  for (const url of parentMenus) {
    try {
      await page.goto(`${BASE}${url}`, { waitUntil: 'networkidle', timeout: 10000 });
      await sleep(1000);
      const title = await page.title();
      const bodyText = await page.evaluate(() => document.body?.innerText?.substring(0, 100) || 'EMPTY');
      console.log(`  ${url} => ${page.url().substring(0, 80)} | ${title.substring(0, 50)} | ${bodyText.substring(0, 50)}`);
    } catch (e) {
      console.log(`  ${url} => ERROR: ${e.message.substring(0, 80)}`);
    }
  }

  await browser.close();
  console.log('\n=== Audit Complete ===');
})();
