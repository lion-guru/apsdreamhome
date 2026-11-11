import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Connected to tab:', cdp.pageUrl);

    // Let's inspect all delete buttons and file inputs
    const info = await cdp.eval(`(() => {
        const fileInputs = Array.from(document.querySelectorAll('input[type="file"]')).map(el => ({
            id: el.id,
            name: el.name,
            accept: el.accept,
            multiple: el.multiple,
            ariaLabel: el.getAttribute('aria-label') || '',
            parentText: el.parentElement ? el.parentElement.innerText.slice(0, 100) : ''
        }));

        const deleteButtons = Array.from(document.querySelectorAll('button[debug-id="delete-button"], [aria-label*="Remove"], [aria-label*="Delete"]')).map(el => ({
            ariaLabel: el.getAttribute('aria-label') || '',
            debugId: el.getAttribute('debug-id') || '',
            innerText: el.innerText.trim(),
            title: el.getAttribute('title') || ''
        }));

        const headings = Array.from(document.querySelectorAll('h1, h2, h3, [role="heading"]')).map(h => h.innerText.trim());

        const saveButtons = Array.from(document.querySelectorAll('button')).filter(b => {
            const txt = (b.innerText || '').toLowerCase();
            return txt.includes('save') || txt.includes('submit') || txt.includes('review');
        }).map(b => ({
            text: b.innerText.trim(),
            debugId: b.getAttribute('debug-id') || '',
            disabled: b.disabled
        }));

        return { fileInputs, deleteButtons, headings: headings.slice(0, 20), saveButtons };
    })()`);

    console.log('DOM Info:', JSON.stringify(info, null, 2));

    await cdp.screenshot('store_listing_initial.png');
    cdp.close();
}

main().catch(console.error);
