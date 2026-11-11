import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Get exact rect of the Category button/box
    const rect = await cdp.eval(`(() => {
        const catVal = document.querySelector('[debug-id="category-value"]');
        if (!catVal) return { error: 'Not found' };
        const r = catVal.getBoundingClientRect();
        return { x: r.x, y: r.y, width: r.width, height: r.height };
    })()`);
    console.log('Category value rect:', rect);

    // Mouse click on category value
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mousePressed',
        x: rect.x + rect.width / 2,
        y: rect.y + rect.height / 2,
        button: 'left',
        clickCount: 1
    });
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mouseReleased',
        x: rect.x + rect.width / 2,
        y: rect.y + rect.height / 2,
        button: 'left',
        clickCount: 1
    });
    console.log('Dispatched mouse click on Category.');
    await cdp.wait(2000);

    await cdp.screenshot('category_menu_opened.png');

    // Find all visible menu items
    const menuItems = await cdp.eval(`(() => {
        const items = Array.from(document.querySelectorAll('material-select-item, [role="option"], .menu-item, .popup-content *')).filter(el => {
            const txt = (el.innerText || '').trim();
            return txt.length > 2 && txt.length < 30 && el.children.length === 0;
        });
        return items.map(el => {
            const r = el.getBoundingClientRect();
            return {
                text: el.innerText.trim(),
                x: r.x + r.width / 2,
                y: r.y + r.height / 2
            };
        });
    })()`);
    console.log('Visible menu items:', menuItems.slice(0, 15));

    cdp.close();
}

main().catch(console.error);
