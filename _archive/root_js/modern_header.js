const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    // Take full viewport screenshot
    await page.setViewportSize({ width: 1920, height: 200 });
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);
    
    await page.screenshot({ 
        path: 'C:\\xampp\\htdocs\\apsdreamhome\\modern_header.png',
        fullPage: false
    });
    
    console.log('Modern header screenshot saved!');
    await browser.close();
})();
