import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click Category box at (643, 408)
    console.log('Clicking Category box at (643, 408)');
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mousePressed',
        x: 643,
        y: 408,
        button: 'left',
        clickCount: 1
    });
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mouseReleased',
        x: 643,
        y: 408,
        button: 'left',
        clickCount: 1
    });
    await cdp.wait(1500);

    // 2. Find the visible Business option
    const busItem = await cdp.eval(`(() => {
        const all = Array.from(document.querySelectorAll('*'));
        const bus = all.filter(el => {
            const txt = (el.innerText || '').trim();
            const r = el.getBoundingClientRect();
            return txt === 'Business' && r.width > 20 && r.height > 10;
        });
        return bus.map(el => {
            const r = el.getBoundingClientRect();
            return {
                tag: el.tagName,
                cls: el.getAttribute('class') || '',
                x: Math.round(r.x + r.width / 2),
                y: Math.round(r.y + r.height / 2),
                w: Math.round(r.width),
                h: Math.round(r.height)
            };
        });
    })()`);

    console.log('Visible Business elements:', busItem);

    if (busItem.length > 0) {
        const target = busItem[0];
        console.log(`Clicking Business at (${target.x}, ${target.y})`);
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mousePressed',
            x: target.x,
            y: target.y,
            button: 'left',
            clickCount: 1
        });
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mouseReleased',
            x: target.x,
            y: target.y,
            button: 'left',
            clickCount: 1
        });
        await cdp.wait(2000);

        // Click Save
        const saveRes = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const saveBtn = btns.find(b => b.innerText.trim() === 'Save');
            if (saveBtn) {
                saveBtn.click();
                return { clicked: true, disabled: saveBtn.disabled };
            }
            return { error: 'Save not found' };
        })()`);
        console.log('Save result:', saveRes);
        await cdp.wait(3000);
    }

    await cdp.screenshot('cat_final_check.png');
    cdp.close();
}

main().catch(console.error);
