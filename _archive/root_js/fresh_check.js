const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.setViewportSize({ width: 1920, height: 1080 });
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle', timeout: 30000 });
    
    // Wait for page to fully load
    await page.waitForTimeout(1000);
    
    console.log('=== TAKING SCREENSHOT ===\n');
    await page.screenshot({ 
        path: 'C:\\xampp\\htdocs\\apsdreamhome\\fresh_header.png', 
        fullPage: false 
    });
    
    console.log('=== ANALYZING HEADER ===\n');
    
    // Get header bounding box
    const header = await page.$('.premium-header');
    if (header) {
        const box = await header.boundingBox();
        console.log('Header position:', JSON.stringify(box));
    }
    
    // Check all nav items with their exact positions
    const navInfo = await page.evaluate(() => {
        const nav = document.querySelector('.navbar-nav');
        const items = document.querySelectorAll('.navbar-nav .nav-item');
        const result = {
            navWidth: nav?.offsetWidth,
            navRight: nav ? nav.getBoundingClientRect().right : 0,
            windowWidth: window.innerWidth,
            items: []
        };
        
        items.forEach((item, i) => {
            const rect = item.getBoundingClientRect();
            result.items.push({
                index: i,
                text: item.innerText.substring(0, 30).trim(),
                left: Math.round(rect.left),
                right: Math.round(rect.right),
                width: Math.round(rect.width),
                visible: rect.width > 0 && rect.height > 0,
                overflow: rect.right > window.innerWidth
            });
        });
        
        return result;
    });
    
    console.log('Navigation Info:');
    console.log('  Nav Width:', navInfo.navWidth);
    console.log('  Nav Right:', navInfo.navRight);
    console.log('  Window Width:', navInfo.windowWidth);
    console.log('  Overflow:', navInfo.navRight > navInfo.windowWidth ? 'YES - items cut off!' : 'NO');
    
    console.log('\nNav Items:');
    navInfo.items.forEach(item => {
        const status = item.overflow ? '⚠️ CUT OFF' : (item.visible ? '✅' : '❌');
        console.log(`  ${status} ${item.index+1}. "${item.text}" - Left:${item.left} Right:${item.right} W:${item.width}`);
    });
    
    console.log('\nScreenshot saved: fresh_header.png');
    await browser.close();
})();
