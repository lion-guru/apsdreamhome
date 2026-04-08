const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.setViewportSize({ width: 1920, height: 1200 });
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    
    console.log('=== DETAILED HEADER ANALYSIS ===\n');
    
    // Get header dimensions
    const headerInfo = await page.evaluate(() => {
        const header = document.querySelector('.premium-header');
        const navbar = document.querySelector('.navbar');
        const brand = document.querySelector('.navbar-brand');
        const navCollapse = document.querySelector('.navbar-collapse');
        const nav = document.querySelector('.navbar-nav');
        
        return {
            header: header ? {
                width: header.offsetWidth,
                height: header.offsetHeight,
                rect: header.getBoundingClientRect()
            } : null,
            navbar: navbar ? {
                width: navbar.offsetWidth,
                height: navbar.offsetHeight
            } : null,
            brand: brand ? {
                width: brand.offsetWidth,
                height: brand.offsetHeight,
                html: brand.innerHTML.substring(0, 100)
            } : null,
            navCollapse: navCollapse ? {
                width: navCollapse.offsetWidth,
                height: navCollapse.offsetHeight,
                display: window.getComputedStyle(navCollapse).display
            } : null,
            nav: nav ? {
                width: nav.offsetWidth,
                height: nav.offsetHeight,
                items: nav.children.length
            } : null,
            navItems: Array.from(document.querySelectorAll('.navbar-nav .nav-item')).map(item => ({
                text: item.innerText.substring(0, 30).trim(),
                width: item.offsetWidth,
                position: item.getBoundingClientRect()
            }))
        };
    });
    
    console.log('Header:', JSON.stringify(headerInfo.header, null, 2));
    console.log('\nNavbar:', JSON.stringify(headerInfo.navbar, null, 2));
    console.log('\nBrand:', JSON.stringify(headerInfo.brand, null, 2));
    console.log('\nNav Collapse:', JSON.stringify(headerInfo.navCollapse, null, 2));
    console.log('\nNav:', JSON.stringify(headerInfo.nav, null, 2));
    
    console.log('\n=== NAV ITEMS POSITIONS ===\n');
    headerInfo.navItems.forEach((item, i) => {
        console.log(`${i+1}. "${item.text}" - Width: ${item.width}, Left: ${Math.round(item.position.left)}, Top: ${Math.round(item.position.top)}`);
    });
    
    // Check window width
    const windowWidth = await page.evaluate(() => window.innerWidth);
    console.log('\nWindow Width:', windowWidth);
    
    // Calculate overflow
    const lastItem = headerInfo.navItems[headerInfo.navItems.length - 1];
    if (lastItem) {
        const overflow = (lastItem.position.left + lastItem.width) - windowWidth;
        console.log('Last item right edge:', lastItem.position.left + lastItem.width);
        console.log('Overflow:', overflow > 0 ? `YES - ${Math.round(overflow)}px overflow` : 'NO');
    }
    
    // Screenshot
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\header_detail.png', fullPage: false });
    console.log('\nScreenshot saved: header_detail.png');
    
    await browser.close();
})();
