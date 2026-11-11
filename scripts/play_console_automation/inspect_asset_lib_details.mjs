import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const lib = document.querySelector('.asset-library-container');
        if (!lib) return { error: 'No asset-library-container found' };

        // Find all rows in the library
        const rows = Array.from(lib.querySelectorAll('asset-list-row, [role="listitem"]')).map(r => ({
            text: r.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: r.getAttribute('aria-label') || ''
        }));

        // Find buttons in the library
        const buttons = Array.from(lib.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: b.getAttribute('aria-label') || '',
            debugId: b.getAttribute('debug-id') || '',
            disabled: b.disabled
        }));

        return {
            visible: lib.offsetParent !== null,
            rowCount: rows.length,
            rows,
            buttons
        };
    })()`);

    console.log('Asset library container info:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
