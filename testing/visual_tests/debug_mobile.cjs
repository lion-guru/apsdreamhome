const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 390, height: 844 },
        deviceScaleFactor: 2
    });
    const page = await context.newPage();

    const errors = [];
    page.on('console', msg => {
        if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push('PAGE ERROR: ' + err.message));

    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'load', timeout: 30000 });
    await page.waitForTimeout(3000);

    // Full page screenshot
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\testing\\visual_tests\\mobile_full.png', fullPage: true });
    console.log('Screenshot saved: mobile_full.png');

    // Viewport-only screenshot (what user sees)
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\testing\\visual_tests\\mobile_viewport.png', fullPage: false });
    console.log('Screenshot saved: mobile_viewport.png');

    // Check hamburger button
    const toggler = await page.$('.navbar-toggler, .navbar-toggle, button[aria-label*="toggle"]');
    if (toggler) {
        console.log('Toggler found, visible:', await toggler.isVisible());
        const box = await toggler.boundingBox();
        console.log('Toggler box:', JSON.stringify(box));
    } else {
        console.log('NO TOGGLER FOUND');
    }

    // Check if the mobile menu collapse is present
    const collapse = await page.$('.navbar-collapse');
    if (collapse) {
        console.log('Collapse found, visible:', await collapse.isVisible());
        const hasShow = await collapse.evaluate(el => el.classList.contains('show'));
        console.log('Collapse has "show" class:', hasShow);
    }

    // Check header structure
    const headerHTML = await page.evaluate(() => {
        const h = document.querySelector('header');
        if (!h) return 'NO HEADER';
        return h.outerHTML.substring(0, 3000);
    });
    console.log('\n=== HEADER HTML (first 3000 chars) ===');
    console.log(headerHTML);

    // Check chatbot footer
    const footer = await page.$('.ai-chat-footer');
    if (footer) {
        console.log('\n=== CHAT FOOTER ===');
        console.log('visible:', await footer.isVisible());
    }
    const chatInput = await page.$('#chatInput');
    if (chatInput) {
        const ph = await chatInput.getAttribute('placeholder');
        console.log('chatInput placeholder:', ph);
    }

    // Open the chat popup
    await page.click('#aiFloatBtn');
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\testing\\visual_tests\\mobile_chat_open.png', fullPage: false });
    console.log('\nChat opened screenshot saved.');

    // Check popup position
    const popup = await page.$('#chatPopup');
    if (popup) {
        const vis = await popup.isVisible();
        const box = await popup.boundingBox();
        console.log('Popup visible:', vis, 'box:', JSON.stringify(box));
        // Check footer inside popup
        const footer2 = await page.$('.ai-chat-footer');
        if (footer2) {
            const fb = await footer2.boundingBox();
            const content = await footer2.textContent();
            console.log('Footer box:', JSON.stringify(fb), 'content:', content.substring(0, 100));
        }
    }

    console.log('\n=== CONSOLE ERRORS ===');
    errors.forEach(e => console.log('  ERR:', e));
    if (errors.length === 0) console.log('  (none)');

    await browser.close();
})();
