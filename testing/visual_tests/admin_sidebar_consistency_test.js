// ADMIN SIDEBAR CONSISTENCY TEST
// Tests sidebar menu rendering across different admin pages
import { chromium } from 'playwright';
import fs from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const REPORT = 'testing/visual_tests/ADMIN_SIDEBAR_CONSISTENCY_REPORT.txt';
const screenshots = [];

function log(msg) {
  const ts = new Date().toISOString().slice(11, 19);
  console.log(`[${ts}] ${msg}`);
  fs.appendFileSync(REPORT, `[${ts}] ${msg}\n`);
}

async function testAdminPage(url, pageName) {
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  try {
    // Use test-login bypass for admin
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 60000 });
    
    // Navigate to target page
    await page.goto(`${BASE}${url}`, { waitUntil: 'load', timeout: 60000 });
    
    const content = await page.content();
    const title = await page.title();
    
    // Check for sidebar
    const hasSidebar = await page.locator('.sidebar').count() > 0;
    const hasRbacSidebar = await page.locator('#sidebarMenu').count() > 0;
    const hasMenuItems = await page.locator('.sidebar-menu').count() > 0;
    const hasSidebarSections = await page.locator('.sidebar-sec').count() > 0;
    
    log(`${pageName}:`);
    log(`  Title: ${title}`);
    log(`  Has sidebar: ${hasSidebar ? 'YES' : 'NO'}`);
    log(`  Has RBAC sidebar (#sidebarMenu): ${hasRbacSidebar ? 'YES' : 'NO'}`);
    log(`  Has menu items: ${hasMenuItems ? 'YES' : 'NO'}`);
    log(`  Has sidebar sections: ${hasSidebarSections ? 'YES' : 'NO'}`);
    
    if (hasSidebarSections) {
      const sectionCount = await page.locator('.sidebar-sec').count();
      log(`  Section count: ${sectionCount}`);
      
      // Get section names
      const sections = await page.locator('.sidebar-sec').allTextContents();
      log(`  Sections: ${sections.join(', ')}`);
    }
    
    // Count menu items
    if (hasMenuItems) {
      const menuItemCount = await page.locator('.sidebar-item').count();
      log(`  Menu items: ${menuItemCount}`);
    }
    
    // Check for fallback menu
    const hasFallback = content.includes('Fallback menu') || content.includes('Dashboard') && content.includes('Analytics');
    if (hasFallback) {
      log(`  ⚠️  Using FALLBACK MENU (RBAC system not working)`);
    }
    
    // Check for layout consistency
    const hasUnifiedLayout = content.includes('unified.php') || content.includes('rbac_sidebar.php');
    const hasTopNav = await page.locator('.top-nav').count() > 0;
    const hasPageContent = await page.locator('.page-content').count() > 0;
    
    log(`  Uses unified layout: ${hasUnifiedLayout ? 'YES' : 'NO'}`);
    log(`  Has top navigation: ${hasTopNav ? 'YES' : 'NO'}`);
    log(`  Has page content area: ${hasPageContent ? 'YES' : 'NO'}`);
    
    // Take screenshot
    const screenshotPath = `testing/visual_tests/sidebar_${pageName.replace(/\s+/g, '_').toLowerCase()}.png`;
    await page.screenshot({ path: screenshotPath, fullPage: false });
    screenshots.push(screenshotPath);
    
    // Check for errors
    if (content.includes('Fatal error') || content.includes('Parse error')) {
      log(`  ❌ PHP ERROR DETECTED`);
    }
    
    log(`  ✅ Page loaded successfully`);
    
  } catch (e) {
    log(`${pageName}: ❌ ERROR -> ${e.message}`);
  }
  
  await browser.close();
}

async function main() {
  log('=== ADMIN SIDEBAR CONSISTENCY TEST ===');
  log('Testing sidebar menu rendering across admin pages\n');
  
  // Clear previous report
  fs.writeFileSync(REPORT, '');
  
  // Test different admin pages
  const testPages = [
    { url: '/admin/dashboard', name: 'Main Dashboard' },
    { url: '/admin/customers', name: 'Customers' },
    { url: '/admin/leads', name: 'Leads' },
    { url: '/admin/properties', name: 'Properties' },
    { url: '/admin/projects', name: 'Projects' },
    { url: '/admin/settings', name: 'Settings' },
    { url: '/admin/users', name: 'Users' },
    { url: '/admin/reports', name: 'Reports' },
  ];
  
  for (const page of testPages) {
    await testAdminPage(page.url, page.name);
    log('');
  }
  
  log('=== SUMMARY ===');
  log(`Screenshots saved: ${screenshots.length}`);
  log(`Report saved to: ${REPORT}`);
}

main().catch(console.error);