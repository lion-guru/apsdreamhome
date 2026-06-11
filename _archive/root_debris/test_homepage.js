const { chromium } = require('@playwright/test');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.setViewportSize({ width: 1920, height: 1080 });
    
    console.log('========================================');
    console.log('  HOMEPAGE COMPREHENSIVE TEST');
    console.log('========================================\n');
    
    // Navigate to homepage
    console.log('[1] Loading homepage...');
    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(2000);
    
    // Take full screenshot
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\homepage_full.png', fullPage: true });
    console.log('  Screenshot saved: homepage_full.png\n');
    
    // Check page load
    const title = await page.title();
    console.log('[2] Page Title:', title);
    
    // Check header
    console.log('\n[3] HEADER ELEMENTS:');
    const headerElements = [
        { name: 'Logo', selector: '.navbar-brand img' },
        { name: 'Home Link', selector: 'a[href*="/"]' },
        { name: 'Properties', selector: 'a[href*="properties"]' },
        { name: 'Projects', selector: '.dropdown-toggle:has-text("Projects")' },
        { name: 'Register', selector: '#registerDropdown' },
        { name: 'Login', selector: '#loginDropdown' },
        { name: 'Phone Button', selector: 'a[href^="tel:"]' },
        { name: 'Admin Button', selector: 'a[href*="admin"]' },
    ];
    
    for (const el of headerElements) {
        const count = await page.locator(el.selector).count();
        const visible = count > 0 ? await page.locator(el.selector).first().isVisible() : false;
        console.log(`  ${visible ? '✓' : '✗'} ${el.name}: ${count > 0 ? 'Found' : 'Missing'}`);
    }
    
    // Check hero section
    console.log('\n[4] HERO SECTION:');
    const heroCheck = await page.evaluate(() => {
        const hero = document.querySelector('.hero-section, .py-5');
        return hero ? `Found (${hero.offsetHeight}px)` : 'Missing';
    });
    console.log('  Hero:', heroCheck);
    
    // Check content sections
    console.log('\n[5] CONTENT SECTIONS:');
    const sections = ['Featured Properties', 'Our Projects', 'About', 'Statistics', 'Contact'];
    for (const section of sections) {
        const found = await page.locator(`h2:has-text("${section}"), h3:has-text("${section}")`).count();
        console.log(`  ${found > 0 ? '✓' : '✗'} ${section}: ${found > 0 ? 'Found' : 'Missing'}`);
    }
    
    // Check footer
    console.log('\n[6] FOOTER:');
    const footer = await page.locator('footer').count();
    console.log(`  ${footer > 0 ? '✓' : '✗'} Footer: ${footer > 0 ? 'Found' : 'Missing'}`);
    
    // Check chat widget
    console.log('\n[7] CHAT WIDGET:');
    const chatBtn = await page.locator('.ai-float-btn, #aiFloatBtn').count();
    console.log(`  ${chatBtn > 0 ? '✓' : '✗'} AI Chat Button: ${chatBtn > 0 ? 'Found' : 'Missing'}`);
    
    // Check WhatsApp button
    const whatsapp = await page.locator('.whatsapp-float-btn').count();
    console.log(`  ${whatsapp > 0 ? '✓' : '✗'} WhatsApp Button: ${whatsapp > 0 ? 'Found' : 'Missing'}`);
    
    // Check for errors
    console.log('\n[8] ERRORS:');
    const errors = await page.evaluate(() => {
        const errors = [];
        window.addEventListener('error', (e) => errors.push(e.message));
        return errors;
    });
    if (errors.length > 0) {
        console.log('  Console Errors:', errors);
    } else {
        console.log('  ✓ No JavaScript errors');
    }
    
    // Performance check
    console.log('\n[9] PERFORMANCE:');
    const perf = await page.evaluate(() => {
        return {
            domReady: document.readyState,
            images: document.images.length,
            links: document.links.length
        };
    });
    console.log('  DOM Ready:', perf.domReady);
    console.log('  Images:', perf.images);
    console.log('  Links:', perf.links);
    
    console.log('\n========================================');
    console.log('  TEST COMPLETE!');
    console.log('========================================');
    
    await browser.close();
})();
