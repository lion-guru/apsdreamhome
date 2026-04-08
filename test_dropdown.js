const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle' });
    
    console.log('=== DROPDOWN TEST ===\n');
    
    // Test clicking Register dropdown
    console.log('1. Clicking Register dropdown...');
    await page.click('#registerDropdown');
    await page.waitForTimeout(500);
    
    const regDropHtml = await page.$eval('#registerDropdown', el => {
        const classes = el.className;
        const hasShow = classes.includes('show');
        const parent = el.closest('.dropdown');
        const menu = parent ? parent.querySelector('.dropdown-menu') : null;
        const menuStyles = menu ? window.getComputedStyle(menu) : null;
        return {
            classes,
            hasShow,
            menuDisplay: menuStyles ? menuStyles.display : 'no menu',
            menuVisibility: menuStyles ? menuStyles.visibility : 'no menu'
        };
    });
    console.log('Register dropdown:', JSON.stringify(regDropHtml, null, 2));
    
    // Check Bootstrap dropdown
    const bsDropState = await page.evaluate(() => {
        const el = document.querySelector('#registerDropdown');
        if (typeof bootstrap !== 'undefined') {
            const dropdown = bootstrap.Dropdown.getInstance(el);
            return dropdown ? 'initialized' : 'not initialized';
        }
        return 'Bootstrap not loaded';
    });
    console.log('\nBootstrap state:', bsDropState);
    
    // Test with JavaScript click
    console.log('\n2. Testing with JS click...');
    await page.evaluate(() => {
        document.querySelector('#registerDropdown').click();
    });
    await page.waitForTimeout(500);
    
    const menuItems = await page.$$eval('.dropdown-menu', menus => 
        menus.map(m => ({
            display: window.getComputedStyle(m).display,
            children: m.children.length
        }))
    );
    console.log('All dropdown menus:', menuItems);
    
    await browser.close();
    console.log('\nDone!');
})();
