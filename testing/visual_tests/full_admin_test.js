const { chromium } = require('playwright');

async function comprehensiveAdminTest() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const results = { passed: 0, failed: 0, errors: [] };
    const tested = new Set();
    
    const log = (status, msg) => {
        console.log(`[${status}] ${msg}`);
        if (status === 'PASS') results.passed++;
        if (status === 'FAIL') { results.failed++; results.errors.push(msg); }
    };
    
    try {
        console.log('\n🧪 COMPREHENSIVE ADMIN TEST\n' + '='.repeat(50));
        
        // Login
        await page.goto('http://localhost/apsdreamhome/admin/login?test_login=1');
        await page.waitForLoadState('networkidle');
        await page.goto('http://localhost/apsdreamhome/admin/dashboard');
        await page.waitForLoadState('networkidle');
        
        // Test all admin routes
        const routes = [
            // Core
            '/admin/dashboard', '/admin/settings', '/admin/profile',
            // Users & Properties
            '/admin/users', '/admin/properties', '/admin/user-properties',
            // Leads & Deals
            '/admin/leads', '/admin/leads/create', '/admin/leads/scoring',
            '/admin/deals', '/admin/deals/kanban',
            // Services & Projects
            '/admin/services', '/admin/projects', '/admin/sites',
            // Plot & Costs
            '/admin/plot-costs', '/admin/plots',
            // Bookings & Payments
            '/admin/bookings', '/admin/payments', '/admin/emi',
            // Enquiries & Visits
            '/admin/inquiries', '/admin/visits', '/admin/visits/calendar',
            // Commissions & MLM
            '/admin/commissions', '/admin/mlm/network',
            // Testimonials, Blog, Jobs
            '/admin/testimonials', '/admin/blog', '/admin/jobs',
            // Locations
            '/admin/locations/states', '/admin/locations/districts', '/admin/locations/colonies',
            // Campaigns & Reports
            '/admin/campaigns', '/admin/reports', '/admin/analytics',
            // Other
            '/admin/gallery', '/admin/newsletter', '/admin/api-keys',
            // Role Dashboards
            '/admin/dashboard/superadmin', '/admin/dashboard/ceo', '/admin/dashboard/cfo',
            '/admin/dashboard/agent', '/admin/dashboard/sales', '/admin/dashboard/marketing',
            // Legacy
            '/admin/enquiries', '/admin/newsletter', '/admin/testimonials/show/1'
        ];
        
        console.log('\n--- Testing Routes ---\n');
        
        for (const route of routes) {
            if (tested.has(route)) continue;
            tested.add(route);
            
            try {
                const fullUrl = `http://localhost/apsdreamhome${route}`;
                const response = await page.goto(fullUrl, { 
                    waitUntil: 'domcontentloaded', 
                    timeout: 10000 
                });
                
                const status = response?.status() || 0;
                if (status === 200) {
                    log('PASS', route);
                } else if (status === 302) {
                    log('PASS', `${route} (redirect)`);
                } else {
                    log('FAIL', `${route} - HTTP ${status}`);
                }
            } catch (e) {
                const errMsg = e.message.substring(0, 40);
                log('FAIL', `${route} - ${errMsg}`);
            }
        }
        
        // Get sidebar menu items
        console.log('\n--- Checking Sidebar Menu ---\n');
        
        const menuItems = await page.evaluate(() => {
            const items = [];
            document.querySelectorAll('nav a, .sidebar a, .menu a, [class*="nav"] a').forEach(a => {
                const href = a.getAttribute('href');
                const text = a.textContent.trim().substring(0, 30);
                if (href && text && href.startsWith('/admin')) {
                    items.push({ href, text });
                }
            });
            return items;
        });
        
        console.log(`Found ${menuItems.length} menu items in sidebar`);
        
    } catch (error) {
        console.log('\n❌ Error:', error.message);
    }
    
    await browser.close();
    
    console.log('\n' + '='.repeat(50));
    console.log('📊 RESULTS: ' + results.passed + ' passed, ' + results.failed + ' failed');
    console.log('='.repeat(50));
    
    if (results.errors.length > 0) {
        console.log('\n⚠️  FAILED:');
        results.errors.forEach(e => console.log('   ' + e));
    }
    
    console.log('\n');
    process.exit(results.failed > 0 ? 1 : 0);
}

comprehensiveAdminTest();