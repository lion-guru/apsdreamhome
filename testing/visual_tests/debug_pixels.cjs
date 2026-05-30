const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });

    // Test on mobile viewport
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2 });
    const page = await ctx.newPage();

    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'load', timeout: 30000 });
    await page.waitForTimeout(2000);

    // 1. Check WhatsApp button pixel color (should be green #25D366)
    const waBtn = await page.$('.whatsapp-float-btn');
    if (waBtn) {
        const box = await waBtn.boundingBox();
        console.log('WA btn box:', JSON.stringify(box));
        if (box) {
            const centerX = box.x + box.width / 2;
            const centerY = box.y + box.height / 2;
            const pixel = await page.screenshot({ clip: { x: Math.round(centerX), y: Math.round(centerY), width: 1, height: 1 } });
            // Check if pixel exists 
            const style = await waBtn.evaluate(el => {
                const cs = getComputedStyle(el);
                return {
                    bg: cs.backgroundColor,
                    display: cs.display,
                    visibility: cs.visibility,
                    opacity: cs.opacity,
                    width: cs.width,
                    height: cs.height,
                    position: cs.position,
                    bottom: cs.bottom,
                    right: cs.right,
                    zIndex: cs.zIndex,
                    overflow: cs.overflow,
                    clip: cs.clip,
                    clipPath: cs.clipPath,
                };
            });
            console.log('WA computed style:', JSON.stringify(style, null, 2));

            // Check if the icon element exists and has styles
            const icon = await waBtn.$('i');
            if (icon) {
                const iconStyle = await icon.evaluate(el => {
                    const cs = getComputedStyle(el);
                    return {
                        color: cs.color,
                        fontSize: cs.fontSize,
                        display: cs.display,
                        visibility: cs.visibility,
                        opacity: cs.opacity,
                        fontFamily: cs.fontFamily,
                    };
                });
                console.log('WA icon style:', JSON.stringify(iconStyle, null, 2));
            }
        }
    }

    // 2. Check Chatbot float button
    const cbBtn = await page.$('#aiFloatBtn');
    if (cbBtn) {
        const box = await cbBtn.boundingBox();
        console.log('\nCB btn box:', JSON.stringify(box));
        const style = await cbBtn.evaluate(el => {
            const cs = getComputedStyle(el);
            return {
                bg: cs.backgroundColor,
                display: cs.display,
                visibility: cs.visibility,
                opacity: cs.opacity,
                width: cs.width,
                height: cs.height,
                position: cs.position,
                bottom: cs.bottom,
                left: cs.left,
                zIndex: cs.zIndex,
            };
        });
        console.log('CB computed style:', JSON.stringify(style, null, 2));

        const icon = await cbBtn.$('i');
        if (icon) {
            const iconStyle = await icon.evaluate(el => {
                const cs = getComputedStyle(el);
                return {
                    color: cs.color,
                    fontSize: cs.fontSize,
                    display: cs.display,
                    visibility: cs.visibility,
                    opacity: cs.opacity,
                    fontFamily: cs.fontFamily,
                };
            });
            console.log('CB icon style:', JSON.stringify(iconStyle, null, 2));
        }
    }

    // 3. Check if chatbot popup opens correctly
    await page.click('#aiFloatBtn');
    await page.waitForTimeout(500);
    const popup = await page.$('#chatPopup');
    if (popup) {
        const vis = await popup.isVisible();
        const box = await popup.boundingBox();
        console.log('\nPopup visible:', vis, 'box:', JSON.stringify(box));

        // Check child elements
        const header = await popup.$('.ai-chat-header');
        const body = await popup.$('.ai-chat-body');
        const footer = await popup.$('.ai-chat-footer');
        if (header) console.log('Popup header exists, visible:', await header.isVisible());
        if (body) console.log('Popup body exists, visible:', await body.isVisible(), 'childCount:', await body.evaluate(el => el.children.length));
        if (footer) {
            const fvis = await footer.isVisible();
            const fbox = await footer.boundingBox();
            console.log('Popup footer exists, visible:', fvis, 'box:', JSON.stringify(fbox));
        }

        // Check send button icon rendering
        const sendBtn = await popup.$('.ai-send-btn i');
        if (sendBtn) {
            const s = await sendBtn.evaluate(el => {
                const cs = getComputedStyle(el);
                return { display: cs.display, visibility: cs.visibility, color: cs.color, fontFamily: cs.fontFamily, content: cs.content };
            });
            console.log('Send icon style:', JSON.stringify(s, null, 2));
        }
    }

    // 4. Now click close and check float button reappears
    const closeBtn = await page.$('.ai-close-btn');
    if (closeBtn) {
        await closeBtn.click();
        await page.waitForTimeout(500);
        const btnVis = await page.$eval('#aiFloatBtn', el => {
            const cs = getComputedStyle(el);
            return { display: cs.display, visibility: cs.visibility, opacity: cs.opacity };
        });
        console.log('\nAfter close - float btn:', JSON.stringify(btnVis));
    }

    // 5. Check header mobile interactivity
    console.log('\n=== Header Mobile Test ===');
    const toggler = await page.$('#navbarToggler');
    if (toggler) {
        // Click to open
        await toggler.click();
        await page.waitForTimeout(500);
        const collapse = await page.$('#navbarNav');
        if (collapse) {
            const hasShow = await collapse.evaluate(el => el.classList.contains('show'));
            console.log('After toggle - collapse has show:', hasShow);
            const cvis = await collapse.isVisible();
            console.log('After toggle - collapse visible:', cvis);
            const cbox = await collapse.boundingBox();
            console.log('After toggle - collapse box:', JSON.stringify(cbox));
        }

        // Check backdrop
        const backdrop = await page.$('.nav-backdrop');
        if (backdrop) {
            const bvis = await backdrop.isVisible();
            const bstyle = await backdrop.evaluate(el => {
                const cs = getComputedStyle(el);
                return { display: cs.display, opacity: cs.opacity, zIndex: cs.zIndex, bg: cs.backgroundColor };
            });
            console.log('Backdrop visible:', bvis, 'style:', JSON.stringify(bstyle));
        }

        // Try clicking a dropdown toggle in the mobile menu
        const firstDropdown = await page.$('#navbarNav .dropdown-toggle');
        if (firstDropdown) {
            await firstDropdown.click();
            await page.waitForTimeout(300);
            const menu = await firstDropdown.evaluate(el => {
                const next = el.nextElementSibling;
                if (next) return { hasShowMobile: next.classList.contains('show-mobile'), maxHeight: getComputedStyle(next).maxHeight };
                return null;
            });
            console.log('Dropdown after click:', JSON.stringify(menu));
        }

        // Close menu
        await toggler.click();
        await page.waitForTimeout(500);
    }

    await browser.close();
})();
