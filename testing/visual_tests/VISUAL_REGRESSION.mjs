import { chromium } from 'playwright';
import { existsSync, mkdirSync } from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const SCREENSHOT_DIR = 'C:\\xampp\\htdocs\\apsdreamhome\\testing\\visual_tests\\screenshots';

// Ensure screenshot directory exists
if (!existsSync(SCREENSHOT_DIR)) {
  mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

// Key pages to capture (admin + public)
const PAGES = [
  // Admin pages
  { path: '/admin/login?test_login=1', name: '01-admin-login', wait: 2000 },
  { path: '/admin/dashboard', name: '02-admin-dashboard', wait: 1500 },
  { path: '/admin/erp', name: '03-erp-dashboard', wait: 1500 },
  { path: '/admin/colonies', name: '04-colonies-list', wait: 1000 },
  { path: '/admin/plots', name: '05-plots-list', wait: 1000 },
  { path: '/admin/bookings', name: '06-bookings-list', wait: 1000 },
  { path: '/admin/sales', name: '07-sales-dashboard', wait: 1000 },
  { path: '/admin/mlm', name: '08-mlm-dashboard', wait: 1000 },
  { path: '/admin/leads', name: '09-leads-list', wait: 1000 },
  { path: '/admin/crm', name: '10-crm-dashboard', wait: 1000 },
  { path: '/admin/ai', name: '11-ai-dashboard', wait: 1000 },
  { path: '/admin/finance/cash-book', name: '12-finance-cashbook', wait: 1000 },
  { path: '/admin/backoffice', name: '13-backoffice', wait: 1000 },
  { path: '/admin/settings', name: '14-settings', wait: 1000 },
  // Public pages
  { path: '/', name: '15-homepage', wait: 1500, logout: true },
  { path: '/properties', name: '16-properties', wait: 1000, logout: true },
  { path: '/colonies', name: '17-colonies', wait: 1000, logout: true },
  { path: '/services', name: '18-services', wait: 1500, logout: true },
  { path: '/contact', name: '19-contact', wait: 1000, logout: true },
  { path: '/careers', name: '20-careers', wait: 1000, logout: true },
];

async function run() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  let captured = 0;
  let failed = 0;

  console.log('=== APS DREAM HOME - VISUAL REGRESSION SCREENSHOTS ===\n');
  console.log(`Capturing ${PAGES.length} pages to ${SCREENSHOT_DIR}\n`);

  for (const { path, name, wait, logout } of PAGES) {
    try {
      // Logout before public pages if needed
      if (logout) {
        await page.goto(`${BASE}/admin/logout`, { waitUntil: 'load', timeout: 10000 }).catch(() => {});
        await page.waitForTimeout(500);
      }

      const url = path.includes('test_login=1') || path.startsWith('/admin') ? `${BASE}${path}` : `${BASE}${path}`;

      const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });

      if (response && (response.status() === 200 || response.status() === 302)) {
        await page.waitForTimeout(wait || 1000);

        const screenshotPath = `${SCREENSHOT_DIR}/${name}.png`;
        await page.screenshot({ path: screenshotPath, fullPage: false });

        console.log(`  CAPTURED ${name} (${response.status()})`);
        captured++;
      } else {
        console.log(`  SKIP ${name} (status: ${response?.status()})`);
        failed++;
      }

      // Re-login for admin pages
      if (logout) {
        await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 10000 });
        await page.waitForTimeout(500);
      }
    } catch (err) {
      console.log(`  ERROR ${name}: ${err.message}`);
      failed++;

      // Try to recover
      try {
        await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 10000 });
        await page.waitForTimeout(500);
      } catch (e) {
        // Browser might be dead
      }
    }
  }

  await browser.close();

  console.log(`\n${'='.repeat(50)}`);
  console.log(`VISUAL REGRESSION: ${captured} captured, ${failed} failed`);
  console.log(`Screenshots saved to: ${SCREENSHOT_DIR}`);
  console.log('='.repeat(50));
}

run().catch(err => {
  console.error('FATAL:', err);
  process.exit(1);
});
