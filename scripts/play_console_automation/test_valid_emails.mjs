import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Delete the invalid subhamchaurasiyagkp@gmail.com entry
    console.log('Deleting invalid entry...');
    await cdp.eval(`(() => {
        const rows = Array.from(document.querySelectorAll('div, tr, ess-cell')).filter(el => el.innerText.includes('subhamchaurasiyagkp@gmail.com'));
        const deleteBtn = document.querySelector('button[aria-label*="subhamchaurasiyagkp"]');
        if (deleteBtn) {
            deleteBtn.click();
            return true;
        }
        // Fallback: click first delete button in email addresses added
        const allDeleteBtns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.trim() === 'delete');
        if (allDeleteBtns.length > 0) {
            allDeleteBtns[0].click();
            return true;
        }
        return false;
    })()`);
    await cdp.wait(2000);

    // 2. Check if nandani87389@gmail.com works
    console.log('Testing nandani87389@gmail.com...');
    const coords = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        if (!input) return null;
        const r = input.getBoundingClientRect();
        return { x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2) };
    })()`);

    if (coords) {
        await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
        await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
        await cdp.wait(200);

        for (const char of 'nandani87389@gmail.com') {
            await cdp.send('Input.dispatchKeyEvent', { type: 'keyDown', text: char, unmodifiedText: char });
            await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp' });
            await cdp.wait(15);
        }
        await cdp.wait(200);

        await cdp.send('Input.dispatchKeyEvent', { type: 'rawKeyDown', windowsVirtualKeyCode: 13, nativeVirtualKeyCode: 13, key: 'Enter', code: 'Enter' });
        await cdp.send('Input.dispatchKeyEvent', { type: 'char', text: '\r', unmodifiedText: '\r' });
        await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: 13, nativeVirtualKeyCode: 13, key: 'Enter', code: 'Enter' });
        await cdp.wait(2000);
    }

    // 3. Inspect dialog errors
    const checkState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        const text = dialog ? dialog.innerText : '';
        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"], [aria-invalid="true"]')).map(e => e.innerText.trim()).filter(Boolean);
        const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            textSnippet: text.slice(0, 500).replace(/\\n/g, ' '),
            errors,
            saveDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);
    console.log('State with nandani87389@gmail.com:\n', JSON.stringify(checkState, null, 2));

    await cdp.screenshot('nandani_test_result.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
