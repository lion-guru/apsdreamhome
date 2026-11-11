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

    // Clear input first
    await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        if (input) {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    })()`);
    await cdp.wait(100);

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

    // Add subhamchaurasiyagkp@gmail.com
    await addEmail(cdp, 'subhamchaurasiyagkp@gmail.com');

    // Also add techguruabhay@gmail.com and bindudevi0530@gmail.com
    await addEmail(cdp, 'techguruabhay@gmail.com');
    await addEmail(cdp, 'bindudevi0530@gmail.com');

    // Check list
    const check = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button[aria-label*="Remove email address"]'))
            .map(b => b.getAttribute('aria-label'));
        const inputVal = document.querySelector('input[aria-label="Add email addresses"]')?.value;
        return { items: [...new Set(btns)], inputVal };
    })()`);
    console.log('List before saving:', JSON.stringify(check, null, 2));

    await cdp.screenshot('before_save_subham_check.png');

    // Click Save changes
    console.log('Clicking Save changes...');
    await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        if (saveBtn) saveBtn.click();
    })()`);
    await cdp.wait(3000);

    // Check errors or closure
    const result = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        if (!dialog) return { dialogClosed: true };
        const errors = Array.from(dialog.querySelectorAll('.error-message, mat-error, [role="alert"], [aria-invalid="true"], .error, [class*="error"]')).map(e => e.innerText.trim()).filter(Boolean);
        const text = dialog.innerText;
        return {
            dialogClosed: false,
            errors,
            textSnippet: text.slice(-400).replace(/\\n/g, ' ')
        };
    })()`);
    console.log('Result after Save changes:', JSON.stringify(result, null, 2));

    await cdp.screenshot('after_save_subham_check.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
