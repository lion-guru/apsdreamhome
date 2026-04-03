const { chromium } = require('playwright');

async function testUI() {
    console.log('Starting UI Test...\n');
    
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    let results = [];
    
    // Test 1: Homepage Logo Check
    console.log('1. Testing Homepage...');
    try {
        await page.goto('http://localhost/apsdreamhome/', { timeout: 30000 });
        await page.waitForLoadState('domcontentloaded');
        
        // Check logo
        const logo = await page.$('img[alt*="logo" i], .navbar-brand img, .logo');
        const logoText = await page.$eval('.navbar-brand, .logo, header', el => el.innerText).catch(() => 'Not found');
        
        results.push({
            test: 'Homepage Logo',
            status: logo ? 'FOUND' : 'NOT FOUND',
            details: logoText.substring(0, 50)
        });
        
        // Check for broken images
        const images = await page.$$eval('img', imgs => imgs.map(img => ({
            src: img.src,
            loaded: img.complete && img.naturalHeight > 0
        })));
        
        const brokenImages = images.filter(img => !img.loaded);
        results.push({
            test: 'Broken Images',
            status: brokenImages.length === 0 ? 'NONE' : `${brokenImages.length} FOUND`,
            details: brokenImages.map(i => i.src).slice(0, 3)
        });
        
        // Check navigation links
        const navLinks = await page.$$eval('a[href]', links => links.slice(0, 10).map(a => a.href));
        results.push({
            test: 'Navigation Links',
            status: 'OK',
            details: `${navLinks.length} links found`
        });
        
    } catch (e) {
        results.push({ test: 'Homepage', status: 'ERROR', details: e.message });
    }
    
    // Test 2: Login Page
    console.log('2. Testing Login Page...');
    try {
        await page.goto('http://localhost/apsdreamhome/login', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        // Check form elements
        const emailInput = await page.$('input[type="email"], input[name="email"], input[name="username"]');
        const passwordInput = await page.$('input[type="password"]');
        const loginBtn = await page.$('button[type="submit"], input[type="submit"]');
        
        results.push({
            test: 'Login Form Elements',
            status: (emailInput && passwordInput && loginBtn) ? 'ALL FOUND' : 'MISSING',
            details: `Email: ${emailInput ? 'OK' : 'MISSING'}, Password: ${passwordInput ? 'OK' : 'MISSING'}, Button: ${loginBtn ? 'OK' : 'MISSING'}`
        });
        
        // Try login with test credentials
        if (emailInput && passwordInput && loginBtn) {
            await emailInput.fill('test@test.com');
            await passwordInput.fill('test123');
            await loginBtn.click();
            await page.waitForTimeout(2000);
            
            const errorMsg = await page.$('.alert-danger, .error, .text-danger');
            results.push({
                test: 'Login Form Submission',
                status: errorMsg ? 'ERROR SHOWN' : 'NO ERROR',
                details: 'Form submitted'
            });
        }
        
    } catch (e) {
        results.push({ test: 'Login Page', status: 'ERROR', details: e.message });
    }
    
    // Test 3: Register Page
    console.log('3. Testing Register Page...');
    try {
        await page.goto('http://localhost/apsdreamhome/register', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        const formFields = await page.$$eval('input, select, textarea', fields => 
            fields.map(f => ({ name: f.name || f.id, type: f.type || f.tagName })).slice(0, 10)
        );
        
        results.push({
            test: 'Register Form Fields',
            status: 'OK',
            details: `${formFields.length} fields found`
        });
        
    } catch (e) {
        results.push({ test: 'Register Page', status: 'ERROR', details: e.message });
    }
    
    // Test 4: Properties Page
    console.log('4. Testing Properties Page...');
    try {
        await page.goto('http://localhost/apsdreamhome/properties', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        const propertyCards = await page.$$('.property-card, .property-item, .listing-item, article');
        const searchBox = await page.$('input[type="search"], input[name="search"], input[name="q"]');
        
        results.push({
            test: 'Properties Page',
            status: propertyCards.length > 0 ? 'HAS LISTINGS' : 'EMPTY',
            details: `${propertyCards.length} property cards, Search: ${searchBox ? 'YES' : 'NO'}`
        });
        
    } catch (e) {
        results.push({ test: 'Properties Page', status: 'ERROR', details: e.message });
    }
    
    // Test 5: Contact Page Form
    console.log('5. Testing Contact Page...');
    try {
        await page.goto('http://localhost/apsdreamhome/contact', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        const contactForm = await page.$('form');
        const submitBtn = await page.$('button[type="submit"]');
        
        if (contactForm && submitBtn) {
            // Fill form
            const nameInput = await page.$('input[name="name"], input[id="name"]');
            const emailInput = await page.$('input[name="email"]');
            const messageInput = await page.$('textarea[name="message"], textarea[id="message"]');
            
            if (nameInput) await nameInput.fill('Test User');
            if (emailInput) await emailInput.fill('test@example.com');
            if (messageInput) await messageInput.fill('This is a test message');
            
            await submitBtn.click();
            await page.waitForTimeout(2000);
            
            const successMsg = await page.$('.alert-success, .success, .text-success');
            results.push({
                test: 'Contact Form Submit',
                status: successMsg ? 'SUCCESS' : 'NO CONFIRMATION',
                details: 'Form submitted'
            });
        }
        
    } catch (e) {
        results.push({ test: 'Contact Page', status: 'ERROR', details: e.message });
    }
    
    // Test 6: Compare Page
    console.log('6. Testing Compare Page...');
    try {
        await page.goto('http://localhost/apsdreamhome/compare', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        const compareCheckboxes = await page.$$('input[type="checkbox"], .compare-checkbox');
        const compareBtn = await page.$('button.compare-btn, .btn-compare');
        
        results.push({
            test: 'Compare Page',
            status: 'OK',
            details: `${compareCheckboxes.length} checkboxes, Button: ${compareBtn ? 'YES' : 'NO'}`
        });
        
    } catch (e) {
        results.push({ test: 'Compare Page', status: 'ERROR', details: e.message });
    }
    
    // Test 7: AI Valuation Page
    console.log('7. Testing AI Valuation Page...');
    try {
        await page.goto('http://localhost/apsdreamhome/ai-valuation', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        const form = await page.$('form');
        const submitBtn = await page.$('button[type="submit"], button:has-text("Valuation"), button:has-text("Generate")');
        
        results.push({
            test: 'AI Valuation Form',
            status: form ? 'FORM FOUND' : 'NO FORM',
            details: submitBtn ? 'Submit button found' : 'No submit button'
        });
        
        // Fill and submit form
        if (form) {
            const locationSelect = await page.$('select[name="location"], select[id="location"]');
            const areaInput = await page.$('input[name="area"], input[name="area_sqft"]');
            
            if (locationSelect) await locationSelect.selectOption({ index: 1 });
            if (areaInput) await areaInput.fill('1000');
            
            results.push({
                test: 'AI Valuation Fill',
                status: 'OK',
                details: 'Form filled'
            });
        }
        
    } catch (e) {
        results.push({ test: 'AI Valuation Page', status: 'ERROR', details: e.message });
    }
    
    // Test 8: Footer Links
    console.log('8. Testing Footer Links...');
    try {
        await page.goto('http://localhost/apsdreamhome/', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        const footerLinks = await page.$$eval('footer a, .footer a', links => 
            links.map(a => ({ href: a.href, text: a.innerText.trim() })).slice(0, 10)
        );
        
        results.push({
            test: 'Footer Links',
            status: 'OK',
            details: `${footerLinks.length} footer links found`
        });
        
    } catch (e) {
        results.push({ test: 'Footer Links', status: 'ERROR', details: e.message });
    }
    
    // Test 9: Mobile Responsive Check
    console.log('9. Testing Mobile View...');
    try {
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('http://localhost/apsdreamhome/', { timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        
        const mobileMenu = await page.$('.navbar-toggler, .hamburger, .mobile-menu');
        const bodyWidth = await page.evaluate(() => document.body.clientWidth);
        
        results.push({
            test: 'Mobile Responsive',
            status: bodyWidth <= 375 ? 'RESPONSIVE' : 'NOT RESPONSIVE',
            details: `Viewport: 375px, Body: ${bodyWidth}px, Menu: ${mobileMenu ? 'YES' : 'NO'}`
        });
        
    } catch (e) {
        results.push({ test: 'Mobile View', status: 'ERROR', details: e.message });
    }
    
    await browser.close();
    
    // Print Results
    console.log('\n=== UI TEST RESULTS ===\n');
    results.forEach((r, i) => {
        const status = r.status.includes('ERROR') || r.status.includes('NOT') ? '❌' : '✅';
        console.log(`${status} ${r.test}`);
        console.log(`   ${r.details}`);
        console.log('');
    });
    
    return results;
}

testUI().catch(console.error);
