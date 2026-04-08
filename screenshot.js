const { chromium } = require('@playwright/test');

(async () => {
    console.log('Launching browser...');
    const browser = await chromium.launch({ headless: true });
    
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1920, height: 1080 });
    
    console.log('Navigating to http://localhost/apsdreamhome/...');
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle' });
    
    console.log('Taking screenshot...');
    await page.screenshot({ 
        path: 'C:\\xampp\\htdocs\\apsdreamhome\\preview.png', 
        fullPage: false 
    });
    
    console.log('Screenshot saved to preview.png');
    
    // Check header elements
    console.log('\n=== HEADER ELEMENTS CHECK ===');
    
    const elements = [
        { name: 'Navbar Brand', selector: '.navbar-brand' },
        { name: 'Register Dropdown', selector: '#registerDropdown' },
        { name: 'Login Dropdown', selector: '#loginDropdown' },
        { name: 'Phone Button', selector: 'a[href^="tel:"]' },
        { name: 'Admin Button', selector: 'a[href*="admin/login"]' },
    ];
    
    for (const el of elements) {
        const count = await page.locator(el.selector).count();
        const visible = count > 0 ? await page.locator(el.selector).first().isVisible() : false;
        console.log(`  ${el.name}: found=${count}, visible=${visible ? 'YES' : 'NO'}`);
    }
    
    await browser.close();
    console.log('\nDone!');
})();
