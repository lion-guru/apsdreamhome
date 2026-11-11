import { CDPClient } from './cdp_helper.mjs';

async function addEmail(cdp, email) {
    console.log(`Adding ${email}...`);
    const coords = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        if (!input) return null;
        const r = input.getBoundingClientRect();
        return { x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2) };
    })()`);
    if (!coords) throw new Error('Input not found');

    // Click input
    await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
    await cdp.wait(200);

    // Type email
    for (const char of email) {
        await cdp.send('Input.dispatchKeyEvent', { type: 'keyDown', text: char, unmodifiedText: char });
        await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp' });
        await cdp.wait(15);
    }
    await cdp.wait(200);

    // Press Enter
    await cdp.send('Input.dispatchKeyEvent', { type: 'rawKeyDown', windowsVirtualKeyCode: 13, nativeVirtualKeyCode: 13, key: 'Enter', code: 'Enter' });
    await cdp.send('Input.dispatchKeyEvent', { type: 'char', text: '\r', unmodifiedText: '\r' });
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: 13, nativeVirtualKeyCode: 13, key: 'Enter', code: 'Enter' });
    await cdp.wait(1000);
}

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Add techguruabhay@gmail.com
    await addEmail(cdp, 'techguruabhay@gmail.com');

    // 2. Add bindudevi0530@gmail.com
    await addEmail(cdp, 'bindudevi0530@gmail.com');

    // 3. Add subhamchaurasiyagkp@gmail.com
    await addEmail(cdp, 'subhamchaurasiyagkp@gmail.com');

    // 4. Inspect current list items
    const listState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        const items = Array.from(dialog.querySelectorAll('div, tr, li, [role="row"]'))
            .filter(el => el.querySelector('button[aria-label*="Remove"]'))
            .map(el => {
                const btn = el.querySelector('button[aria-label*="Remove"]');
                return btn.getAttribute('aria-label');
            });
        const saveBtn = Array.from(dialog.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            items: [...new Set(items)],
            saveBtnDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);
    console.log('List state after typing all:', JSON.stringify(listState, null, 2));

    await cdp.screenshot('all_testers_entered.png');

    // 5. Try clicking "Save changes"
    console.log('Clicking Save changes...');
    await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        const saveBtn = Array.from(dialog.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        if (saveBtn) saveBtn.click();
    })()`);
    await cdp.wait(3000);

    // 6. Check what happened
    const saveOutcome = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        if (!dialog) return { dialogClosed: true };
        const text = dialog.innerText;
        const errorElements = Array.from(dialog.querySelectorAll('.error-message, mat-error, [role="alert"], [aria-invalid="true"], .error, [class*="error"]')).map(e => e.innerText.trim()).filter(Boolean);
        return {
            dialogClosed: false,
            dialogTextSnippet: text.slice(-400).replace(/\\n/g, ' '),
            errors: errorElements
        };
    })()`);
    console.log('Save outcome:', JSON.stringify(saveOutcome, null, 2));

    await cdp.screenshot('after_save_outcome.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
