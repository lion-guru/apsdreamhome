import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const items = await cdp.eval(`(() => {
        const allItems = Array.from(document.querySelectorAll('div[role="listitem"], li, mat-list-item, div[class*="asset"]')).map(el => {
            return {
                text: el.innerText.trim().replace(/\\n/g, ' '),
                classes: el.className
            };
        }).filter(el => el.text.includes('.png'));

        const buttons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: b.getAttribute('aria-label') || '',
            disabled: b.disabled
        })).filter(b => b.text.length < 30);

        return { itemsCount: allItems.length, items: allItems, buttons };
    })()`);

    console.log('Current Library State:', JSON.stringify(items, null, 2));
    cdp.close();
}

main().catch(console.error);
