const { chromium } = require('playwright');

async function testAdminWorkflow() {
    const browser = await chromium.launch({ headless: false }); // Show browser
    const context = await browser.newContext();
    const page = await context.newPage();
    
    console.log('\n🧪 ADMIN PANEL COMPREHENSIVE WORKFLOW TEST\n');
    console.log('='.repeat(60));
    
    try {
        // Step 1: Go to admin login
        console.log('\n📍 Step 1: Opening Admin Login...');
        await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1');
        await page.waitForLoadState('networkidle');
        
        // Wait a moment for any redirects
        await page.waitForTimeout(2000);
        
        const currentUrl = page.url();
        console.log(`   Current URL: ${currentUrl}`);
        
        // Step 2: Navigate to Dashboard via sidebar
        console.log('\n📍 Step 2: Clicking Dashboard in sidebar...');
        
        // Try to find and click dashboard link
        const dashboardLink = await page.$('a[href*="dashboard"]');
        if (dashboardLink) {
            await dashboardLink.click();
            await page.waitForLoadState('networkidle');
            console.log(`   ✅ Clicked dashboard`);
        }
        
        // Step 3: Find all sidebar menu items
        console.log('\n📍 Step 3: Finding all sidebar menu items...');
        
        const menuItems = await page.evaluate(() => {
            const items = [];
            const selectors = [
                'nav a', '.sidebar a', '.menu a', '.navbar a', 
                '[class*="sidebar"] a', '[class*="menu"] a', '[class*="nav"] a',
                '.admin-sidebar a', '.admin-menu a'
            ];
            
            for (const selector of selectors) {
                document.querySelectorAll(selector).forEach(a => {
                    const href = a.getAttribute('href');
                    const text = a.textContent.trim().substring(0, 40);
                    if (href && text && (href.includes('/admin') || href.includes('admin/'))) {
                        if (!items.find(i => i.href === href)) {
                            items.push({ href, text });
                        }
                    }
                });
            }
            return items;
        });
        
        console.log(`   Found ${menuItems.length} menu items`);
        menuItems.slice(0, 10).forEach((m, i) => console.log(`   ${i+1}. ${m.text} -> ${m.href}`));
        
        // Step 4: Test clicking on key menu items
        console.log('\n📍 Step 4: Testing key menu items...');
        
        const keyMenus = [
            { name: 'Properties', url: '/admin/properties' },
            { name: 'Leads', url: '/admin/leads' },
            { name: 'Users', url: '/admin/users' },
            { name: 'Projects', url: '/admin/projects' },
            { name: 'Services', url: '/admin/services' },
            { name: 'Deals', url: '/admin/deals' },
            { name: 'Bookings', url: '/admin/bookings' },
            { name: 'Settings', url: '/admin/settings' },
        ];
        
        for (const menu of keyMenus) {
            try {
                const fullUrl = `http://localhost/apsdreamhome${menu.url}`;
                await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 10000 });
                await page.waitForTimeout(500);
                
                const pageTitle = await page.title();
                const status = page.url().includes('404') ? '❌ 404' : '✅ OK';
                console.log(`   ${status} ${menu.name} - ${pageTitle}`);
                
                // Try to find any buttons/forms on the page
                const buttons = await page.$eval('body', body => {
                    const btns = body.querySelectorAll('button, .btn, a[class*="btn"]');
                    return btns.length;
                }).catch(() => 0);
                
                if (buttons > 0) console.log(`      (Has ${buttons} buttons/links)`);
                
            } catch (e) {
                console.log(`   ❌ ${menu.name} - ${e.message.substring(0, 30)}`);
            }
        }
        
        // Step 5: Try to find and click on a form
        console.log('\n📍 Step 5: Testing form interaction...');
        
        await page.goto('http://localhost/apsdreamhome/admin/properties/create');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
        
        // Check if form exists
        const formExists = await page.$('form');
        if (formExists) {
            console.log('   ✅ Form found on properties/create');
            
            // Try filling a field
            const input = await page.$('input[name="name"], input[name="title"], input[name="property_name"]');
            if (input) {
                await input.fill('Test Property');
                console.log('   ✅ Filled property name');
            }
            
            // Try clicking submit
            const submitBtn = await page.$('button[type="submit"], button:has-text("Submit"), button:has-text("Save")');
            if (submitBtn) {
                console.log('   ✅ Submit button found');
            }
        } else {
            console.log('   ⚠️  No form found on this page');
        }
        
        // Step 6: Test deals page
        console.log('\n📍 Step 6: Testing deals page workflow...');
        
        await page.goto('http://localhost/apsdreamhome/admin/deals');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
        
        const dealsPageContent = await page.content();
        if (dealsPageContent.includes('deal') || dealsPageContent.includes('Deal')) {
            console.log('   ✅ Deals page loaded with content');
        } else {
            console.log('   ⚠️  Deals page may be empty');
        }
        
        // Step 7: Check for any JavaScript errors in console
        console.log('\n📍 Step 7: Checking console for errors...');
        
        page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log(`   ⚠️  Console Error: ${msg.text().substring(0, 50)}`);
            }
        });
        
        // Step 8: Get final status of all routes
        console.log('\n📍 Step 8: Final route check...');
        
        const routesToCheck = [
            '/admin/dashboard',
            '/admin/properties',
            '/admin/leads', 
            '/admin/users',
            '/admin/projects',
            '/admin/services',
            '/admin/deals',
            '/admin/bookings',
            '/admin/plot-costs',
            '/admin/settings',
            '/admin/profile',
            '/admin/testimonials',
            '/admin/gallery',
            '/admin/campaigns',
            '/admin/mlm/network',
            '/admin/api-keys',
            '/admin/visits',
            '/admin/inquiries',
            '/admin/sites',
            '/admin/locations/states',
        ];
        
        let passed = 0;
        let failed = 0;
        
        for (const route of routesToCheck) {
            try {
                const res = await page.goto(`http://localhost/apsdreamhome${route}`, { 
                    waitUntil: 'domcontentloaded', 
                    timeout: 5000 
                });
                const status = res?.status() || 0;
                if (status === 200 || status === 302) {
                    passed++;
                } else {
                    failed++;
                    console.log(`   ❌ ${route} - ${status}`);
                }
            } catch (e) {
                failed++;
            }
        }
        
        console.log(`\n📊 FINAL RESULTS: ${passed} passed, ${failed} failed out of ${routesToCheck.length}`);
        
        // Final screenshot
        console.log('\n📍 Taking final screenshot...');
        await page.screenshot({ path: 'testing/visual_tests/admin_full_test.png', fullPage: true });
        console.log('   ✅ Screenshot saved');
        
    } catch (error) {
        console.log('\n❌ Test Error:', error.message);
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('Test complete! Browser will stay open for review.');
    console.log('Close browser when done.\n');
    
    // Keep browser open
    // await browser.close();
}

testAdminWorkflow();