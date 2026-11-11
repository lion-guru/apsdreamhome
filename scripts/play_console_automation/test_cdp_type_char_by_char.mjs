import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click directly inside the email input to focus it with real mouse event
    const box = await cdp.eval(`(() => {
        const input = document.querySelector('[debug-id="email-input"] input, input[type="email"]');
        if (!input) return null;
        input.focus();
        input.value = '';
        const r = input.getBoundingClientRect();
        return { x: r.x + r.width / 2, y: r.y + r.height / 2 };
    })()`);

    if (!box) {
        throw new Error('Input box not found');
    }

    console.log('Clicking input box at:', box);
    await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: box.x, y: box.y, button: 'left', clickCount: 1 });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: box.x, y: box.y, button: 'left', clickCount: 1 });
    await cdp.wait(500);

    const email = 'bindudevi0530@gmail.com';
    console.log(`Typing "${email}" character by character...`);
    for (const char of email) {
        await cdp.send('Input.dispatchKeyEvent', {
            type: 'keyDown',
            text: char,
            unmodifiedText: char
        });
        await cdp.send('Input.dispatchKeyEvent', {
            type: 'keyUp'
        });
        await cdp.wait(30);
    }

    await cdp.wait(500);

    console.log('Pressing Enter key...');
    await cdp.send('Input.dispatchKeyEvent', {
        type: 'rawKeyDown',
        windowsVirtualKeyCode: 13,
        nativeVirtualKeyCode: 13,
        key: 'Enter',
        code: 'Enter'
    });
    await cdp.send('Input.dispatchKeyEvent', {
        type: 'char',
        text: '\r',
        unmodifiedText: '\r'
    });
    await cdp.send('Input.dispatchKeyEvent', {
        type: 'keyUp',
        windowsVirtualKeyCode: 13,
        nativeVirtualKeyCode: 13,
        key: 'Enter',
        code: 'Enter'
    });

    await cdp.wait(2000);

    // Check table rows
    const tableState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        const rows = Array.from(dialog ? dialog.querySelectorAll('.particle-table-row, ess-cell[essfield="emailAddress"]') : []).map(r => r.innerText.trim());
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            rows,
            saveDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);

    console.log('Table state after typing:', JSON.stringify(tableState, null, 2));

    if (tableState.saveDisabled === false) {
        console.log('Save changes button is active! Clicking it...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
        console.log('Saved changes successfully!');
    }

    cdp.close();
}

main().catch(console.error);
