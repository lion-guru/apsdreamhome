// FINAL VALIDATION TEST
// Tests all improvements across the project
import fs from 'fs';
import { chromium } from 'playwright';
const { execSync } = require('child_process');

const BASE = 'http://localhost/apsdreamhome';
const REPORT = 'testing/visual_tests/FINAL_VALIDATION_REPORT.txt';
const screenshots = [];

function log(msg) {
  const ts = new Date().toISOString().slice(11, 19);
  console.log(`[${ts}] ${msg}`);
  fs.appendFileSync(REPORT, `[${ts}] ${msg}\n`);
}

async function testAdminRBAC() {
  log('=== TESTING ADMIN RBAC SYSTEM ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();

  try {
    // Test with admin login
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 60000 });

    // Check if RBAC sidebar is working
    await page.goto(`${BASE}/admin/dashboard`, { waitUntil: 'load', timeout: 60000 });

    const hasRbacSidebar = (await page.locator('#sidebarMenu').count()) > 0;
    const hasSidebarSections = (await page.locator('.sidebar-sec').count()) > 0;
    const menuItems = await page.locator('.sidebar-item').count();

    log(`RBAC Sidebar: ${hasRbacSidebar ? 'WORKING' : 'NOT WORKING'}`);
    log(`Sidebar Sections: ${hasSidebarSections ? 'PRESENT' : 'MISSING'}`);
    log(`Menu Items: ${menuItems}`);

    // Test different admin pages
    const pages = ['/admin/customers', '/admin/leads', '/admin/properties'];
    for (const url of pages) {
      await page.goto(`${BASE}${url}`, { waitUntil: 'load', timeout: 60000 });
      const hasSidebar = (await page.locator('.sidebar').count()) > 0;
      const usesUnified = (await page.locator('.top-nav').count()) > 0;
      log(`${url}: Sidebar=${hasSidebar ? 'YES' : 'NO'}, Unified=${usesUnified ? 'YES' : 'NO'}`);
    }

    await page.screenshot({ path: 'testing/visual_tests/final_admin_rbac.png', fullPage: false });
    screenshots.push('testing/visual_tests/final_admin_rbac.png');
  } catch (e) {
    log(`Admin RBAC Test Error: ${e.message}`);
  }

  await browser.close();
}

async function testLayoutConsistency() {
  log('=== TESTING LAYOUT CONSISTENCY ===');
  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
  const page = await browser.newPage();

  try {
    // Test customer portal layout
    await page.goto(`${BASE}/login`, { waitUntil: 'load', timeout: 60000 });
    const customerHasLayout = (await page.locator('form').count()) > 0;
    log(`Customer Login Page: ${customerHasLayout ? 'FORM PRESENT' : 'NO FORM'}`);

    // Test admin layout consistency
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 60000 });
    await page.goto(`${BASE}/admin/customers`, { waitUntil: 'load', timeout: 60000 });

    const hasTopNav = (await page.locator('.top-nav').count()) > 0;
    const hasPageContent = (await page.locator('.page-content').count()) > 0;
    const hasSidebar = (await page.locator('.sidebar').count()) > 0;

    log(
      `Admin Customers Page: TopNav=${hasTopNav ? 'YES' : 'NO'}, PageContent=${hasPageContent ? 'YES' : 'NO'}, Sidebar=${hasSidebar ? 'YES' : 'NO'}`
    );

    await page.screenshot({ path: 'testing/visual_tests/final_layout_consistency.png', fullPage: false });
    screenshots.push('testing/visual_tests/final_layout_consistency.png');
  } catch (e) {
    log(`Layout Consistency Test Error: ${e.message}`);
  }

  await browser.close();
}

async function testRBACPermissions() {
  log('=== TESTING RBAC PERMISSIONS ===');

  // Check database for RBAC permissions
  try {
    const execSync = require('child_process').execSync;
    const result = execSync('php tools/check_rbac_menu_system.php', { encoding: 'utf8' });
    log('RBAC Database Check:');
    log(result);
  } catch (e) {
    log(`RBAC Permissions Check Error: ${e.message}`);
  }
}

async function testPartialsExist() {
  log('=== TESTING PARTIALS EXISTENCE ===');

  const existsSync = require('fs').existsSync;

  const partials = [
    'app/views/admin/partials/search_bar.php',
    'app/views/admin/partials/export_buttons.php',
    'app/views/admin/partials/mobile_optimization.php',
    'app/views/admin/partials/realtime_updates.php',
  ];

  let allExist = true;
  for (const partial of partials) {
    const exists = existsSync(partial);
    log(`${partial}: ${exists ? 'EXISTS' : 'MISSING'}`);
    if (!exists) allExist = false;
  }

  log(`Partials Status: ${allExist ? 'ALL EXIST' : 'SOME MISSING'}`);
}

async function testUserPortals() {
  log('=== TESTING USER PORTAL AVAILABILITY ===');

  const existsSync = require('fs').existsSync;

  const layouts = [
    'app/views/customer/layouts/unified.php',
    'app/views/associate/layouts/unified.php',
    'app/views/agent/layouts/unified.php',
    'app/views/employee/layouts/unified.php',
  ];

  let allExist = true;
  for (const layout of layouts) {
    const exists = existsSync(layout);
    log(`${layout}: ${exists ? 'EXISTS' : 'MISSING'}`);
    if (!exists) allExist = false;
  }

  log(`Portal Layouts Status: ${allExist ? 'ALL EXIST' : 'SOME MISSING'}`);
}

async function main() {
  log('=== FINAL VALIDATION TEST ===');
  log('Testing all improvements across the project\n');

  // Clear previous report
  fs.writeFileSync(REPORT, '');

  await testAdminRBAC();
  await testLayoutConsistency();
  await testRBACPermissions();
  await testPartialsExist();
  await testUserPortals();

  log('=== VALIDATION COMPLETE ===');
  log(`Screenshots saved: ${screenshots.length}`);
  log(`Report saved to: ${REPORT}`);
  log('\nReview the detailed report for full analysis.');
}

main().catch(console.error);
