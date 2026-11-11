import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click Category dropdown box to open it
    console.log('Clicking Category box at (290, 420)');
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mousePressed',
        x: 290,
        y: 420,
        button: 'left',
        clickCount: 1
    });
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mouseReleased',
        x: 290,
        y: 420,
        button: 'left',
        clickCount: 1
    });
    await cdp.wait(1000);

    // 2. Click Business at (290, 555)
    console.log('Clicking Business at (290, 555)');
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mousePressed',
        x: 290,
        y: 555,
        button: 'left',
        clickCount: 1
    });
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mouseReleased',
        x: 290,
        y: 555,
        button: 'left',
        clickCount: 1
    });
    await cdp.wait(1500);

    await cdp.screenshot('after_business_click.png');

    // 3. Click Save button (around bottom right of modal)
    const saveRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => b.innerText.trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, disabled: saveBtn.disabled };
        }
        return { error: 'Save not found' };
    })()`);
    console.log('Save button result:', saveRes);
    await cdp.wait(3000);

    await cdp.screenshot('store_settings_after_cat.png');
    cdp.close();
}

main().catch(console.error);
