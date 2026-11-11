import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const result = await cdp.eval(`(() => {
        const input = document.querySelector('[debug-id="email-input"] input, input[type="email"]');
        if (!input) return { error: 'No input' };

        input.focus();
        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        nativeSet.call(input, 'bindudevi0530@gmail.com');
        input.dispatchEvent(new Event('input', { bubbles: true, composed: true }));
        input.dispatchEvent(new Event('change', { bubbles: true, composed: true }));

        // Now dispatch keydown, keypress, keyup for Enter
        const enterDown = new KeyboardEvent('keydown', {
            key: 'Enter',
            code: 'Enter',
            keyCode: 13,
            which: 13,
            charCode: 13,
            bubbles: true,
            cancelable: true,
            composed: true
        });
        const enterUp = new KeyboardEvent('keyup', {
            key: 'Enter',
            code: 'Enter',
            keyCode: 13,
            which: 13,
            charCode: 13,
            bubbles: true,
            cancelable: true,
            composed: true
        });

        input.dispatchEvent(enterDown);
        input.dispatchEvent(enterUp);

        return { dispatched: true, val: input.value };
    })()`);

    console.log('Dispatch result:', result);
    await cdp.wait(2000);

    const dialogState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        const text = dialog ? dialog.innerText : '';
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            hasNewEmail: text.includes('bindudevi0530@gmail.com'),
            textSnippet: text.slice(0, 500).replace(/\\n/g, ' '),
            saveDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);

    console.log('Dialog state:', JSON.stringify(dialogState, null, 2));

    if (dialogState.saveDisabled === false) {
        console.log('Clicking Save changes...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
            if (btn) btn.click();
        })()`);
        await cdp.wait(2000);
    }

    cdp.close();
}

main().catch(console.error);
