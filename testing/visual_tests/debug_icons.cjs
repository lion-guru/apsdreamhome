const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 390, height: 844 },
        deviceScaleFactor: 2
    });
    const page = await context.newPage();

    // Collect console messages
    const errors = [];
    page.on('console', msg => {
        if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push('PAGE ERROR: ' + err.message));

    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'load', timeout: 30000 });
    await page.waitForTimeout(2000);

    // Screenshot
    await page.screenshot({ path: 'C:\\xampp\\htdocs\\apsdreamhome\\testing\\visual_tests\\debug_homepage.png', fullPage: false });

    console.log('=== CONSOLE ERRORS ===');
    errors.forEach(e => console.log('  ERR:', e));
    if (errors.length === 0) console.log('  (none)');

    // Check DOM elements
    const checks = [
        { name: 'ai-chatbot-container', selector: '#ai-chatbot' },
        { name: 'chat-popup', selector: '#chatPopup' },
        { name: 'chat-float-btn', selector: '#aiFloatBtn' },
        { name: 'whatsapp-btn', selector: '.whatsapp-float-btn' },
        { name: 'font-awesome icons', selector: '.fab, .fas' },
    ];

    console.log('\n=== DOM ELEMENT CHECKS ===');
    for (const c of checks) {
        const el = await page.$(c.selector);
        const visible = el ? await el.isVisible() : false;
        const box = el ? await el.boundingBox() : null;
        console.log(`  ${c.name} (${c.selector}): found=${!!el}, visible=${visible}, box=${JSON.stringify(box)}`);
    }

    // Check computed styles for float button
    console.log('\n=== COMPUTED STYLES ===');
    const floatBtn = await page.$('#aiFloatBtn');
    if (floatBtn) {
        const styles = await floatBtn.evaluate(el => {
            const cs = getComputedStyle(el);
            return {
                position: cs.position,
                bottom: cs.bottom,
                left: cs.left,
                display: cs.display,
                width: cs.width,
                height: cs.height,
                zIndex: cs.zIndex,
                opacity: cs.opacity,
                visibility: cs.visibility,
                transform: cs.transform
            };
        });
        console.log('  aiFloatBtn styles:', JSON.stringify(styles, null, 2));
    }

    const waBtn = await page.$('.whatsapp-float-btn');
    if (waBtn) {
        const styles2 = await waBtn.evaluate(el => {
            const cs = getComputedStyle(el);
            return {
                position: cs.position,
                bottom: cs.bottom,
                right: cs.right,
                display: cs.display,
                width: cs.width,
                height: cs.height,
                zIndex: cs.zIndex,
                opacity: cs.opacity,
                visibility: cs.visibility
            };
        });
        console.log('  whatsapp styles:', JSON.stringify(styles2, null, 2));
    }

    // Check container styles
    const container = await page.$('#ai-chatbot');
    if (container) {
        const s = await container.evaluate(el => {
            const cs = getComputedStyle(el);
            return {
                position: cs.position,
                bottom: cs.bottom,
                left: cs.left,
                zIndex: cs.zIndex,
                display: cs.display
            };
        });
        console.log('  container styles:', JSON.stringify(s, null, 2));
    }

    // Check header mobile menu
    const header = await page.$('header');
    if (header) {
        const h = await header.evaluate(el => {
            const cs = getComputedStyle(el);
            return { position: cs.position, top: cs.top, zIndex: cs.zIndex, height: cs.height };
        });
        console.log('  header styles:', JSON.stringify(h, null, 2));
    }

    // Check if header overlap fixes are working
    const main = await page.$('main');
    if (main) {
        const m = await main.evaluate(el => {
            const cs = getComputedStyle(el);
            return { paddingTop: cs.paddingTop };
        });
        console.log('  main padding-top:', JSON.stringify(m, null, 2));
    }

    // Check all <link> tags for CSS loading
    console.log('\n=== CSS LINK TAGS ===');
    const links = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(l => ({
            href: l.href,
            media: l.media
        }));
    });
    links.forEach(l => console.log('  ', l.href, l.media !== 'all' ? '('+l.media+')' : ''));

    console.log('\n=== SCRIPT TAGS (defer/async check) ===');
    const scripts = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('script')).map(s => ({
            src: s.src || '(inline)',
            defer: s.defer,
            onload: s.getAttribute('onload') || ''
        }));
    });
    scripts.forEach(s => console.log('  ', s.defer ? '[defer]' : '', s.src));

    await browser.close();
})();
