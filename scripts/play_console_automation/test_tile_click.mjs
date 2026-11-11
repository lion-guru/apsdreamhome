import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const testClick = await cdp.eval(`(() => {
        // Find asset-list-row that contains screenshot_1_explore.png
        const rows = Array.from(document.querySelectorAll('asset-list-row'));
        const row = rows.find(r => (r.innerText || '').includes('screenshot_1_explore.png'));
        if (!row) return { error: 'Row not found' };

        const tileContainer = row.querySelector('[debug-id="tile-container"]') || row.querySelector('.tile-container');
        const listRow = row.querySelector('.list-row-container');

        const beforeText = row.innerText.replace(/\\n/g, ' ');

        // Click tileContainer
        if (tileContainer) {
            tileContainer.scrollIntoView({ block: 'center' });
            tileContainer.click();
        } else if (listRow) {
            listRow.scrollIntoView({ block: 'center' });
            listRow.click();
        }

        const afterText = row.innerText.replace(/\\n/g, ' ');

        const bottomButtons = Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(t => t.includes('Add') || t.includes('Save') || t.includes('Select'));

        return { beforeText, afterText, bottomButtons };
    })()`);

    console.log('Tile click test:', JSON.stringify(testClick, null, 2));
    cdp.close();
}

main().catch(console.error);
