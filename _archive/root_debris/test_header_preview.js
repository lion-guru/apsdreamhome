const { chromium } = require('@playwright/test');

(async () => {
    console.log('Starting browser...');
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.setViewportSize({ width: 1920, height: 1080 });
    
    console.log('Opening http://localhost/apsdreamhome/...');
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Taking screenshot...');
    await page.screenshot({ 
        path: 'C:\\xampp\\htdocs\\apsdreamhome\\header_preview.png', 
        fullPage: false 
    });
    
    console.log('\n=== CHECKING HEADER ELEMENTS ===\n');
    
    const checks = [
        { name: 'Navbar Brand', selector: '.navbar-brand', required: true },
        { name: 'Register Dropdown', selector: '#registerDropdown', required: true },
        { name: 'Login Dropdown', selector: '#loginDropdown', required: true },
        { name: 'Phone Button', selector: 'a[href^="tel:"]', required: true },
        { name: 'Admin Button', selector: 'a[href*="admin/login"]', required: true },
        { name: 'Home Link', selector: '.nav-link:has-text("Home")', required: false },
        { name: 'Projects Dropdown', selector: '.nav-link:has-text("Projects")', required: false },
    ];
    
    for (const check of checks) {
        try {
            const count = await page.locator(check.selector).count();
            if (count > 0) {
                const visible = await page.locator(check.selector).first().isVisible();
                console.log(`[${visible ? 'OK' : 'HIDDEN'}] ${check.name}: found=${count}, visible=${visible}`);
            } else {
                console.log(`[MISSING] ${check.name}: NOT FOUND`);
            }
        } catch (e) {
            console.log(`[ERROR] ${check.name}: ${e.message}`);
        }
    }
    
    console.log('\n=== CHECKING CSS ISSUES ===\n');
    
    // Check header CSS
    const headerStyles = await page.evaluate(() => {
        const header = document.querySelector('.premium-header');
        if (!header) return 'Header not found';
        const styles = window.getComputedStyle(header);
        return {
            position: styles.position,
            zIndex: styles.zIndex,
            overflow: styles.overflow,
            visibility: styles.visibility,
            display: styles.display,
            transform: styles.transform
        };
    });
    console.log('Header styles:', JSON.stringify(headerStyles, null, 2));
    
    // Check navbar-collapse
    const collapseStyles = await page.evaluate(() => {
        const collapse = document.querySelector('.navbar-collapse');
        if (!collapse) return 'Navbar collapse not found';
        const styles = window.getComputedStyle(collapse);
        return {
            display: styles.display,
            overflow: styles.overflow,
            visibility: styles.visibility,
            maxHeight: styles.maxHeight
        };
    });
    console.log('\nNavbar-collapse styles:', JSON.stringify(collapseStyles, null, 2));
    
    console.log('\nScreenshot saved: header_preview.png');
    await browser.close();
})();
