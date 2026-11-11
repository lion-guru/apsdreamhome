import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const rows = Array.from(document.querySelectorAll('asset-list-row'));
        const row = rows.find(r => (r.innerText || '').includes('screenshot_5_associate.png'));
        if (!row) return { error: 'Row not found' };

        // Inspect all clickable elements or children
        const elements = Array.from(row.querySelectorAll('*')).map(el => ({
            tag: el.tagName,
            className: el.className,
            role: el.getAttribute('role'),
            ariaLabel: el.getAttribute('aria-label') || '',
            hasClick: typeof el.onclick === 'function' || el.hasAttribute('clickable')
        }));

        return { rowHtml: row.innerHTML, elements: elements.slice(0, 15) };
    })()`);

    console.log('Row details for screenshot_5:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
