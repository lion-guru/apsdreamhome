const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.setViewportSize({ width: 1920, height: 200 });
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\header_test.png' });
    console.log('Header screenshot saved!');
    
    await browser.close();
})();
