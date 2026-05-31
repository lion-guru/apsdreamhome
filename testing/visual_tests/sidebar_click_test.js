// SIDEBAR CLICK FUNCTIONALITY TEST
// Tests if sidebar section clicks work properly
import { chromium } from 'playwright';
import fs from 'fs';

const BASE = 'http://localhost/apsdreamhome';
const REPORT = 'testing/visual_tests/SIDEBAR_CLICK_TEST_REPORT.txt';

function log(msg) {
  const ts = new Date().toISOString().slice(11, 19);
  console.log(`[${ts}] ${msg}`);
  fs.appendFileSync(REPORT, `[${ts}] ${msg}\n`);
}

async function testSidebarClick() {
  log('=== SIDEBAR CLICK FUNCTIONALITY TEST ===');
  log('Testing if sidebar section clicks work\n');

  // Clear previous report
  fs.writeFileSync(REPORT, '');

  const browser = await chromium.launch({ headless: false, args: ['--no-sandbox'] });
  const page = await browser.newPage();

  try {
    // Use test-login bypass for admin
    log('Navigating to admin login with test bypass...');
    await page.goto(`${BASE}/admin/login?test_login=1`, { waitUntil: 'load', timeout: 60000 });
    
    // Navigate to dashboard
    log('Navigating to dashboard...');
    await page.goto(`${BASE}/admin/dashboard`, { waitUntil: 'load', timeout: 60000 });
    
    // Wait for page to load
    await page.waitForTimeout(2000);

    // Check if toggleSidebarSection function exists
    const functionExists = await page.evaluate(() => {
      return typeof window.toggleSidebarSection === 'function';
    });
    log(`toggleSidebarSection function exists: ${functionExists ? 'YES' : 'NO'}`);

    // Check if toggleAllSidebarSections function exists
    const functionExists2 = await page.evaluate(() => {
      return typeof window.toggleAllSidebarSections === 'function';
    });
    log(`toggleAllSidebarSections function exists: ${functionExists2 ? 'YES' : 'NO'}`);

    // Find the first sidebar section
    const firstSection = await page.locator('.sidebar-sec').first();
    const hasSections = await page.locator('.sidebar-sec').count() > 0;
    log(`Has sidebar sections: ${hasSections ? 'YES' : 'NO'}`);

    if (hasSections) {
      const sectionCount = await page.locator('.sidebar-sec').count();
      log(`Section count: ${sectionCount}`);

      // Get the first fallback section (fallback-main)
      const fallbackMainSection = await page.locator('#arrow-fallback-main').count() > 0;
      log(`Has fallback-main section: ${fallbackMainSection ? 'YES' : 'NO'}`);

      if (fallbackMainSection) {
        // Test clicking on fallback-main section
        log('Clicking on fallback-main section...');
        await page.locator('#arrow-fallback-main').click();
        
        // Check if the menu expanded/collapsed
        await page.waitForTimeout(500);
        
        const fallbackMenuVisible = await page.locator('#fallback-main').isVisible();
        log(`fallback-main menu visible after click: ${fallbackMenuVisible ? 'YES' : 'NO'}`);

        // Click again to toggle
        log('Clicking again to toggle back...');
        await page.locator('#arrow-fallback-main').click();
        await page.waitForTimeout(500);
        
        const fallbackMenuVisible2 = await page.locator('#fallback-main').isVisible();
        log(`fallback-main menu visible after second click: ${fallbackMenuVisible2 ? 'YES' : 'NO'}`);
      } else {
        // Test with dynamic section
        log('Testing with dynamic section...');
        const firstArrow = await page.locator('.sidebar-sec-arrow').first();
        
        if (await firstArrow.count() > 0) {
          log('Clicking on first section arrow...');
          await firstArrow.click();
          await page.waitForTimeout(500);
          log('Click completed');
        }
      }
    }

    // Check for JavaScript errors
    const errors = await page.evaluate(() => {
      return window.errors || [];
    });
    
    if (errors.length > 0) {
      log(`❌ JavaScript errors detected: ${errors.join(', ')}`);
    } else {
      log('✅ No JavaScript errors detected');
    }

    // Take screenshot
    await page.screenshot({ path: 'testing/visual_tests/sidebar_click_test.png', fullPage: false });
    log('Screenshot saved');

  } catch (e) {
    log(`❌ ERROR: ${e.message}`);
  }

  await browser.close();
  log('\n=== TEST COMPLETED ===');
}

testSidebarClick().catch(console.error);
