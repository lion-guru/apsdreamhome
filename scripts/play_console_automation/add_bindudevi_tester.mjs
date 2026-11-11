import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Focus the 'Add email addresses' input
    console.log('Focusing Add email addresses input...');
    const focused = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        if (input) {
            input.focus();
            return true;
        }
        return false;
    })()`);

    if (!focused) {
        throw new Error('Add email addresses input not found or not focused');
    }

    await cdp.wait(500);

    // 2. Type email using CDP Input.insertText
    const targetEmail = 'bindudevi0530@gmail.com';
    console.log(`Typing email: ${targetEmail}...`);
    await cdp.send('Input.insertText', { text: targetEmail });
    await cdp.wait(500);

    // 3. Press Enter to add the email chip
    console.log('Dispatching Enter key...');
    await cdp.send('Input.dispatchKeyEvent', {
        type: 'keyDown',
        key: 'Enter',
        code: 'Enter',
        windowsVirtualKeyCode: 13,
        nativeVirtualKeyCode: 13
    });
    await cdp.send('Input.dispatchKeyEvent', {
        type: 'keyUp',
        key: 'Enter',
        code: 'Enter',
        windowsVirtualKeyCode: 13,
        nativeVirtualKeyCode: 13
    });

    await cdp.wait(1500);

    // Also trigger comma or space fallback if needed
    const chipState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        const text = dialog ? dialog.innerText : '';
        const hasEmail = text.includes('bindudevi0530@gmail.com');
        const saveChangesBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            hasEmail,
            saveDisabled: saveChangesBtn ? saveChangesBtn.disabled : null
        };
    })()`);

    console.log('Chip state after Enter:', chipState);

    // If not added yet, dispatch blur or change
    if (!chipState.hasEmail) {
        console.log('Trying Enter via JS event...');
        await cdp.eval(`(() => {
            const input = document.querySelector('input[aria-label="Add email addresses"]');
            if (input) {
                input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', code: 'Enter', keyCode: 13, which: 13, bubbles: true }));
                input.dispatchEvent(new KeyboardEvent('keyup', { key: 'Enter', code: 'Enter', keyCode: 13, which: 13, bubbles: true }));
            }
        })()`);
        await cdp.wait(1000);
    }

    // 4. Click Save changes in dialog
    console.log('Clicking Save changes button...');
    const clickSaveRes = await cdp.eval(`(() => {
        const saveChangesBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        if (saveChangesBtn && !saveChangesBtn.disabled) {
            saveChangesBtn.click();
            return { clicked: true };
        }
        return { clicked: false, disabled: saveChangesBtn ? saveChangesBtn.disabled : null };
    })()`);

    console.log('Click Save changes result:', clickSaveRes);
    await cdp.wait(3000);

    // 5. Verify email in list and click Save on main track page if needed
    const verifyState = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const mainSaveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        return {
            hasTesterCount: body.includes('Internal Testers 2') || body.includes('Internal Testers\n2'),
            bodySnippet: body.slice(0, 600).replace(/\\n/g, ' '),
            mainSaveDisabled: mainSaveBtn ? mainSaveBtn.disabled : null
        };
    })()`);

    console.log('Verify state:', JSON.stringify(verifyState, null, 2));

    // If main page Save button is active, click it
    if (verifyState.mainSaveDisabled === false) {
        console.log('Clicking Save on track page...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
    }

    cdp.close();
}

main().catch(console.error);
