import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const dialogDetails = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { error: 'No dialog found' };

        const buttons = Array.from(dialog.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            debugId: b.getAttribute('debug-id') || '',
            ariaLabel: b.getAttribute('aria-label') || '',
            disabled: b.disabled
        }));

        const tabs = Array.from(dialog.querySelectorAll('[role="tab"]')).map(t => ({
            text: t.innerText.trim(),
            selected: t.getAttribute('aria-selected') === 'true'
        }));

        const items = Array.from(dialog.querySelectorAll('[role="listitem"], .asset-item, mat-grid-tile')).map(item => ({
            text: item.innerText.slice(0, 50),
            ariaLabel: item.getAttribute('aria-label') || ''
        }));

        return {
            title: dialog.querySelector('h1, h2, h3, [role="heading"]')?.innerText || '',
            buttons,
            tabs,
            itemsCount: items.length
        };
    })()`);

    console.log('Dialog Details:', JSON.stringify(dialogDetails, null, 2));
    cdp.close();
}

main().catch(console.error);
