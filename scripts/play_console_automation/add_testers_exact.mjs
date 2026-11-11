import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting via CDP to add testers character by character...');
    const cdp = new CDPClient();
    await cdp.init();

    const emails = [
        'bindudevi0530@gmail.com',
        'subhamchaurasiyagkp@gmail.com',
        'nandani87389@gmail.com'
    ];

    for (const email of emails) {
        console.log(`\n--- Processing email: ${email} ---`);

        // Check if already in list
        const alreadyIn = await cdp.eval(`document.body.innerText.includes('${email}')`);
        if (alreadyIn) {
            console.log(`Email ${email} is already in the list! Skipping.`);
            continue;
        }

        // 1. Click directly inside the email input to focus it with real mouse event
        const box = await cdp.eval(`(() => {
            const input = document.querySelector('input[aria-label="Add email addresses"], input[type="email"]');
            if (!input) return null;
            input.focus();
            input.value = '';
            const r = input.getBoundingClientRect();
            return { x: r.x + r.width / 2, y: r.y + r.height / 2 };
        })()`);

        if (!box) {
            throw new Error('Input box not found in dialog');
        }

        console.log('Clicking input box at coordinates:', box);
        await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: box.x, y: box.y, button: 'left', clickCount: 1 });
        await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: box.x, y: box.y, button: 'left', clickCount: 1 });
        await cdp.wait(400);

        // 2. Type email character by character
        console.log(`Typing "${email}" char by char...`);
        for (const char of email) {
            await cdp.send('Input.dispatchKeyEvent', {
                type: 'keyDown',
                text: char,
                unmodifiedText: char
            });
            await cdp.send('Input.dispatchKeyEvent', {
                type: 'keyUp'
            });
            await cdp.wait(25);
        }
        await cdp.wait(300);

        // 3. Send comma
        console.log('Sending comma character...');
        await cdp.send('Input.dispatchKeyEvent', {
            type: 'keyDown',
            text: ',',
            unmodifiedText: ','
        });
        await cdp.send('Input.dispatchKeyEvent', {
            type: 'char',
            text: ',',
            unmodifiedText: ','
        });
        await cdp.send('Input.dispatchKeyEvent', {
            type: 'keyUp'
        });
        await cdp.wait(300);

        // 4. Send Enter
        console.log('Sending Enter key...');
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

        await cdp.wait(1500);

        // 5. Verify email was added
        const verified = await cdp.eval(`(() => {
            const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
            return dialog ? dialog.innerText.includes('${email}') : false;
        })()`);
        console.log(`Verification for ${email}:`, verified ? 'ADDED SUCCESSFULLY' : 'NOT FOUND');
    }

    // Inspect table and Save changes button
    const dialogState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { noDialog: true };
        const text = dialog.innerText;
        const saveBtn = Array.from(dialog.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            hasTechguru: text.includes('techguruabhay@gmail.com'),
            hasBindu: text.includes('bindudevi0530@gmail.com'),
            hasSubham: text.includes('subhamchaurasiyagkp@gmail.com'),
            hasNandani: text.includes('nandani87389@gmail.com'),
            saveBtnDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);

    console.log('\nFinal Dialog State:\n', JSON.stringify(dialogState, null, 2));
    await cdp.screenshot('all_testers_in_dialog.png');

    // Click "Save changes" in dialog
    if (dialogState.saveBtnDisabled === false) {
        console.log('Clicking "Save changes" button...');
        await cdp.eval(`(() => {
            const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
            const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
            if (saveBtn) saveBtn.click();
        })()`);
        await cdp.wait(4000);
        console.log('Dialog saved!');
    }

    // Check main track page
    const trackSaveState = await cdp.eval(`(() => {
        const mainSaveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => r.innerText.trim().replace(/\\n/g, ' ')).filter(r => r.includes('Internal Testers'));
        return {
            rows,
            saveDisabled: mainSaveBtn ? mainSaveBtn.disabled : null
        };
    })()`);
    console.log('Track page state after dialog closed:', trackSaveState);

    if (trackSaveState.saveDisabled === false) {
        console.log('Clicking Save on main track page...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
        console.log('Track page saved!');
    }

    await cdp.screenshot('track_testers_final.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
