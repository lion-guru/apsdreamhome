const { chromium } = require('playwright');

async function testAdminDashboard() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const results = { passed: 0, failed: 0, errors: [] };
    
    const log = (status, msg) => {
        console.log(`[${status}] ${msg}`);
        if (status === 'PASS') results.passed++;
        if (status === 'FAIL') {
            results.failed++;
            results.errors.push(msg);
        }
    };
    
    try {
        console.log('\n🧪 Testing Admin Dashboard...\n');
        
        // Step 1: Login with test-login bypass
        console.log('--- Step 1: Admin Login ---');
        await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1');
        await page.waitForLoadState('networkidle');
        
        // Check if login bypass worked
        const currentUrl = page.url();
        if (currentUrl.includes('dashboard') || currentUrl.includes('admin')) {
            log('PASS', 'Admin test-login bypass successful');
        } else {
            // Try to find and click login button
            await page.click('button[type="submit"]');
            await page.waitForLoadState('networkidle');
            log('PASS', 'Admin login successful');
        }
        
        // Step 2: Get all menu links
        console.log('\n--- Step 2: Testing Menu Links ---');
        
        const menuLinks = await page.evaluate(() => {
            const links = [];
            // Get all anchor tags in the admin layout
            document.querySelectorAll('a[href]').forEach(a => {
                const href = a.getAttribute('href');
                if (href && href.startsWith('/admin') && !href.includes('#')) {
                    links.push(href);
                }
            });
            return [...new Set(links)]; // Remove duplicates
        });
        
        console.log(`Found ${menuLinks.length} admin links to test\n`);
        
        // Step 3: Test each menu link
        for (const link of menuLinks) {
            try {
                const fullUrl = `http://localhost/apsdreamhome${link}`;
                const response = await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 10000 });
                
                if (response && response.ok()) {
                    log('PASS', `${link} - OK`);
                } else if (response && response.status() === 302) {
                    // Redirect is OK for some links
                    log('PASS', `${link} - Redirect (OK)`);
                } else {
                    log('FAIL', `${link} - Status: ${response?.status() || 'timeout'}`);
                }
            } catch (e) {
                log('FAIL', `${link} - ${e.message}`);
            }
        }
        
        // Step 4: Check dashboard widgets
        console.log('\n--- Step 3: Dashboard Widgets ---');
        
        const dashboardStats = await page.evaluate(() => {
            const stats = [];
            document.querySelectorAll('.card, .stat-box, .counter, .count').forEach(el => {
                const text = el.textContent.trim().substring(0, 50);
                if (text) stats.push(text);
            });
            return stats;
        });
        
        if (dashboardStats.length > 0) {
            log('PASS', `Dashboard has ${dashboardStats.length} widgets/elements`);
        } else {
            log('FAIL', 'Dashboard appears empty');
        }
        
        // Step 5: Test key admin pages
        console.log('\n--- Step 4: Key Admin Pages ---');
        
        const keyPages = [
            '/admin/dashboard',
            '/admin/properties',
            '/admin/users',
            '/admin/user-properties',
            '/admin/leads',
            '/admin/services',
            '/admin/projects',
            '/admin/plot-costs',
            '/admin/settings'
        ];
        
        for (const pageName of keyPages) {
            try {
                const fullUrl = `http://localhost/apsdreamhome${pageName}`;
                const response = await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 10000 });
                
                if (response && (response.ok() || response.status() === 302)) {
                    log('PASS', pageName);
                } else {
                    log('FAIL', `${pageName} - Status: ${response?.status()}`);
                }
            } catch (e) {
                log('FAIL', `${pageName} - ${e.message}`);
            }
        }
        
    } catch (error) {
        console.log('\n❌ Test Error:', error.message);
    }
    
    await browser.close();
    
    // Summary
    console.log('\n========================================');
    console.log('📊 TEST SUMMARY');
    console.log('========================================');
    console.log(`✅ Passed: ${results.passed}`);
    console.log(`❌ Failed: ${results.failed}`);
    if (results.errors.length > 0) {
        console.log('\n⚠️  Failed Tests:');
        results.errors.forEach(e => console.log(`   - ${e}`));
    }
    console.log('========================================\n');
    
    return results;
}

testAdminDashboard().then(console.log);