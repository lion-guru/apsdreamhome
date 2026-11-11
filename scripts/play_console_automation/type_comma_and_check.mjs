import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Focus input
    await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        if (input) input.focus();
    })()`);
    await cdp.wait(500);

    // 2. Type comma
    console.log('Inserting comma...');
    await cdp.send('Input.insertText', { text: ',' });
    await cdp.wait(1000);

    // 3. Check what dialog shows now
    const state = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        const text = dialog ? dialog.innerText : '';
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            dialogSnippet: text.slice(0, 400).replace(/\\n/g, ' '),
            saveDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);

    console.log('State after comma:', JSON.stringify(state, null, 2));

    if (state.saveDisabled === false) {
        console.log('Save changes is enabled! Clicking it...');
        await cdp.eval(`(() => {
            const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
            if (saveBtn) saveBtn.click();
        })()`);
        await cdp.wait(2000);
    }

    cdp.close();
}

main().catch(console.error);
