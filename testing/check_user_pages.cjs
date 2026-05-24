const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

    // First login
    await page.goto('http://localhost/apsdreamhome/login', { waitUntil: 'load', timeout: 30000 });
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'testing/screenshots/login_page.png' });
    
    // Check if login page loaded
    const loginText = await page.evaluate(() => document.body.innerText.substring(0, 500));
    console.log('Login page text:', loginText);
    
    // Try filling the form
    const identityInput = await page.$('input[name="identity"]');
    if (identityInput) {
        console.log('Found identity input');
        await identityInput.fill('test@aps.com');
    } else {
        const emailInput = await page.$('input[name="email"]');
        if (emailInput) {
            console.log('Found email input');
            await emailInput.fill('test@aps.com');
        } else {
            const allInputs = await page.$$('input[type="email"], input[type="text"]');
            console.log('All text inputs:', allInputs.length);
        }
    }
    
    const passInput = await page.$('input[name="password"]');
    if (passInput) {
        console.log('Found password input');
        await passInput.fill('test123');
    }
    
    // Submit
    const submitBtn = await page.$('button[type="submit"], input[type="submit"]');
    if (submitBtn) {
        await submitBtn.click();
    } else {
        console.log('No submit button found');
    }
    
    await page.waitForTimeout(3000);
    console.log('After login URL:', page.url());
    
    // Try user pages
    for (const path of ['/user/properties', '/user/inquiries', '/user/profile']) {
        await page.goto('http://localhost/apsdreamhome' + path, { waitUntil: 'load', timeout: 30000 });
        await page.waitForTimeout(1000);
        const text = await page.evaluate(() => document.body.innerText.substring(0, 150));
        console.log(path, '->', page.url(), ':', text.substring(0, 100));
    }

    await browser.close();
})();
