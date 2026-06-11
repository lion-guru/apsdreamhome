const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.setViewportSize({ width: 1920, height: 1080 });
    
    console.log('Opening homepage...');
    
    // Try with different wait strategy
    const response = await page.goto('http://localhost/apsdreamhome/', { 
        waitUntil: 'domcontentloaded',
        timeout: 30000 
    });
    
    console.log('Status:', response.status());
    
    // Wait for content to render
    await page.waitForTimeout(3000);
    
    // Wait for body to be visible
    await page.waitForSelector('body', { state: 'visible', timeout: 5000 });
    
    // Check if body has content
    const bodyContent = await page.evaluate(() => {
        const body = document.body;
        return {
            hasContent: body.innerText.length > 100,
            textLength: body.innerText.length,
            childCount: body.children.length
        };
    });
    
    console.log('Body check:', JSON.stringify(bodyContent));
    
    // Take screenshot
    await page.screenshot({ 
        path: 'C:\\xampp\\htdocs\\apsdreamhome\\homepage_test.png',
        type: 'png'
    });
    
    console.log('Screenshot saved!');
    
    // Get visible text
    const visibleText = await page.evaluate(() => {
        return document.body.innerText.substring(0, 500);
    });
    
    console.log('\nVisible text:\n', visibleText);
    
    await browser.close();
})();
