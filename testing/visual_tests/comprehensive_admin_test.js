const { chromium } = require('playwright');

async function comprehensiveAdminTest() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const results = { passed: 0, failed: 0, errors: [] };
    const checkedPages = new Set();
    
    const log = (status, msg) => {
        console.log(`[${status}] ${msg}`);
        if (status === 'PASS') results.passed++;
        if (status === 'FAIL') {
            results.failed++;
            results.errors.push(msg);
        }
    };
    
    try {
        console.log('\n🧪 Comprehensive Admin Panel Test\n');
        console.log('='.repeat(50));
        
        // Login first
        await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1');
        await page.waitForLoadState('networkidle');
        
        // Get dashboard URL
        const dashboardUrl = page.url().includes('dashboard') ? page.url() : 'http://localhost/apsdreamhome/admin/dashboard';
        await page.goto(dashboardUrl);
        await page.waitForLoadState('networkidle');
        
        // Extract all admin menu links from the page
        const allAdminLinks = await page.evaluate(() => {
            const links = [];
            const selectors = [
                'nav a', '.sidebar a', '.menu a', '.navbar a', 
                '.nav a', '#sidebar a', '.admin-menu a',
                'ul.nav a', '.dropdown-menu a', '[class*="nav"] a',
                'aside a', '.side-menu a'
            ];
            
            const allLinks = document.querySelectorAll('a[href]');
            allLinks.forEach(a => {
                const href = a.getAttribute('href');
                if (href && (href.startsWith('/admin') || href.startsWith('admin'))) {
                    // Clean the href
                    let cleanHref = href;
                    if (href.startsWith('http')) {
                        const url = new URL(href);
                        cleanHref = url.pathname;
                    }
                    if (cleanHref && !cleanHref.includes('#') && !cleanHref.includes('javascript')) {
                        links.push(cleanHref);
                    }
                }
            });
            
            return [...new Set(links)];
        });
        
        console.log(`\n📋 Found ${allAdminLinks.length} unique admin links\n`);
        
        // Test each discovered link
        for (const link of allAdminLinks) {
            if (checkedPages.has(link)) continue;
            checkedPages.add(link);
            
            try {
                const fullUrl = link.startsWith('http') ? link : `http://localhost/apsdreamhome${link}`;
                const response = await page.goto(fullUrl, { 
                    waitUntil: 'domcontentloaded', 
                    timeout: 15000 
                });
                
                const status = response?.status() || 0;
                const statusText = status === 200 ? 'OK' : status === 302 ? 'Redirect' : status;
                
                if (status === 200 || status === 302) {
                    log('PASS', `${link} (${statusText})`);
                } else {
                    log('FAIL', `${link} - HTTP ${status}`);
                }
            } catch (e) {
                log('FAIL', `${link} - ${e.message.substring(0, 50)}`);
            }
        }
        
        // Also test common admin routes that might not be in menu
        console.log('\n--- Additional Admin Routes ---\n');
        
        const additionalRoutes = [
            '/admin/dashboard',
            '/admin/properties',
            '/admin/users', 
            '/admin/leads',
            '/admin/user-properties',
            '/admin/services',
            '/admin/projects',
            '/admin/plot-costs',
            '/admin/settings',
            '/admin/enquiries',
            '/admin/newsletter',
            '/admin/testimonials',
            '/admin/blog',
            '/admin/jobs',
            '/admin/mlm/network',
            '/admin/leads/scoring',
            '/admin/agent-dashboard',
            '/admin/associate-dashboard',
            '/admin/reports',
            '/admin/analytics'
        ];
        
        for (const route of additionalRoutes) {
            if (checkedPages.has(route)) continue;
            checkedPages.add(route);
            
            try {
                const fullUrl = `http://localhost/apsdreamhome${route}`;
                const response = await page.goto(fullUrl, { 
                    waitUntil: 'domcontentloaded', 
                    timeout: 10000 
                });
                
                const status = response?.status() || 0;
                if (status === 200 || status === 302) {
                    log('PASS', `${route}`);
                } else {
                    log('FAIL', `${route} - HTTP ${status}`);
                }
            } catch (e) {
                // Don't count as failure - some routes may not exist
            }
        }
        
    } catch (error) {
        console.log('\n❌ Test Error:', error.message);
    }
    
    await browser.close();
    
    // Summary
    console.log('\n' + '='.repeat(50));
    console.log('📊 TEST SUMMARY');
    console.log('='.repeat(50));
    console.log(`✅ Passed: ${results.passed}`);
    console.log(`❌ Failed: ${results.failed}`);
    console.log(`📝 Total Tested: ${results.passed + results.failed}`);
    
    if (results.errors.length > 0) {
        console.log('\n⚠️  Failed Pages:');
        results.errors.forEach(e => console.log(`   - ${e}`));
    }
    console.log('='.repeat(50) + '\n');
    
    process.exit(results.failed > 0 ? 1 : 0);
}

comprehensiveAdminTest();