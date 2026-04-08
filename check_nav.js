const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    
    // Take screenshot at 100% zoom (default)
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle' });
    
    // Screenshot at 100% zoom
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\preview_100.png', fullPage: false });
    
    // Take screenshot at 90% zoom
    await page.setViewportSize({ width: 1728, height: 972 });
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\preview_90.png', fullPage: false });
    
    // Check all nav items visibility
    console.log('\n=== NAVIGATION ITEMS ===');
    const navItems = await page.$$eval('.navbar-nav .nav-item', items => 
        items.map(item => ({
            text: item.innerText.trim().substring(0, 50),
            visible: item.offsetParent !== null,
            width: item.offsetWidth,
            height: item.offsetHeight
        }))
    );
    
    navItems.forEach((item, i) => {
        console.log(`${i+1}. "${item.text}" - W:${item.width} H:${item.height} - ${item.visible ? 'VISIBLE' : 'HIDDEN'}`);
    });
    
    // Check if items are cut off
    const navRect = await page.$eval('.navbar-nav', el => {
        const rect = el.getBoundingClientRect();
        return { right: rect.right, width: rect.width, windowWidth: window.innerWidth };
    });
    console.log('\nNav width:', navRect.width, 'Window:', navRect.windowWidth);
    console.log('Items cut off:', navRect.right > navRect.windowWidth ? 'YES' : 'NO');
    
    // Check dropdown items
    console.log('\n=== DROPDOWN CHECK ===');
    await page.hover('#registerDropdown');
    await page.waitForTimeout(500);
    const regDropVisible = await page.$eval('.dropdown-menu', el => window.getComputedStyle(el).display !== 'none');
    console.log('Register dropdown opens:', regDropVisible);
    
    console.log('\nScreenshots saved: preview_100.png, preview_90.png');
    await browser.close();
})();
