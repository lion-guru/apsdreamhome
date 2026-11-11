import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Close current modal by clicking close button or pressing Escape
    console.log('Closing modal...');
    const closeRes = await cdp.eval(`(() => {
        const closeBtn = document.querySelector('button[aria-label="Close"], button[aria-label="close"], [debug-id="close-button"], material-button.close-button');
        if (closeBtn) {
            closeBtn.click();
            return { clicked: true };
        }
        // Try any icon with text 'close'
        const all = Array.from(document.querySelectorAll('*'));
        const closeIcon = all.find(el => el.innerText.trim() === 'close' && el.children.length === 0);
        if (closeIcon) {
            closeIcon.click();
            return { clickedIcon: true };
        }
        return { error: 'Close not found' };
    })()`);
    console.log('Close result:', closeRes);
    await cdp.wait(2000);

    // 2. Click Edit on "Store listing contact details"
    const clickEdit = await cdp.eval(`(() => {
        const allEdits = Array.from(document.querySelectorAll('button, a')).filter(b => b.innerText.trim() === 'Edit');
        // If there's 1 Edit now (the contact details one), click it
        if (allEdits.length > 0) {
            const target = allEdits[allEdits.length - 1];
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, totalEdits: allEdits.length };
        }
        return { error: 'No Edit buttons found' };
    })()`);
    console.log('Click Edit Contact result:', clickEdit);
    await cdp.wait(3000);

    await cdp.screenshot('contact_details_modal_open.png');

    // 3. Inspect inputs inside the contact details dialog
    const inputs = await cdp.eval(`(() => {
        const list = Array.from(document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="url"]')).map(i => {
            const r = i.getBoundingClientRect();
            return {
                id: i.id,
                name: i.name,
                placeholder: i.placeholder,
                ariaLabel: i.getAttribute('aria-label') || '',
                value: i.value,
                rect: {
                    x: Math.round(r.x),
                    y: Math.round(r.y),
                    w: Math.round(r.width),
                    h: Math.round(r.height)
                }
            };
        });
        return list.filter(i => i.rect.w > 0 && i.rect.h > 0);
    })()`);
    console.log('Contact inputs:', inputs);

    cdp.close();
}

main().catch(console.error);
