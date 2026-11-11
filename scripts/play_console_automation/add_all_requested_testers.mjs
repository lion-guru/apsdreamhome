import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Play Console via CDP to finalize all testers...');
    const cdp = new CDPClient();
    await cdp.init();

    const emails = [
        'nandani87389@gmail.com',
        'bindudevi0530@gmail.com'
    ];

    for (const email of emails) {
        console.log(`\nAdding ${email}...`);

        // Check if already present
        const alreadyIn = await cdp.eval(`document.querySelector('[role="dialog"]')?.innerText.includes('${email}')`);
        if (alreadyIn) {
            console.log(`Email ${email} is already present in dialog.`);
            continue;
        }

        // 1. Set clean email in input
        const setRes = await cdp.eval(`(() => {
            const input = document.querySelector('input[aria-label="Add email addresses"]');
            if (!input) return { error: 'No input' };

            input.focus();
            const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
            nativeSet.call(input, '${email}');
            input.dispatchEvent(new Event('input', { bubbles: true, composed: true }));
            input.dispatchEvent(new Event('change', { bubbles: true, composed: true }));

            return { val: input.value };
        })()`);
        console.log('Set email result:', setRes);
        await cdp.wait(400);

        // 2. Dispatch CDP Enter keydown, char, keyup
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
        await cdp.wait(800);

        // 3. Synthetic KeyboardEvent dispatch
        await cdp.eval(`(() => {
            const input = document.querySelector('input[aria-label="Add email addresses"]');
            const mat = input?.closest('material-input');
            const evt = new KeyboardEvent('keyup', {
                key: 'Enter',
                code: 'Enter',
                keyCode: 13,
                which: 13,
                bubbles: true,
                composed: true
            });
            if (input) input.dispatchEvent(evt);
            if (mat) mat.dispatchEvent(evt);
        })()`);
        await cdp.wait(1500);

        const check = await cdp.eval(`document.querySelector('[role="dialog"]')?.innerText.includes('${email}')`);
        console.log(`Email ${email} added:`, check);
    }

    // 4. Inspect final list in dialog
    const dialogSummary = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"]');
        const text = dialog ? dialog.innerText : '';
        const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            hasSubham: text.includes('subhamchaurasiyagkp@gmail.com'),
            hasNandani: text.includes('nandani87389@gmail.com'),
            hasBindu: text.includes('bindudevi0530@gmail.com'),
            hasTechguru: text.includes('techguruabhay@gmail.com'),
            saveBtnDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);
    console.log('\nAll Testers In Dialog Summary:\n', JSON.stringify(dialogSummary, null, 2));

    await cdp.screenshot('all_testers_verified_in_dialog.png');

    // 5. Click "Save changes" in dialog
    if (dialogSummary.saveBtnDisabled === false) {
        console.log('Clicking "Save changes" in dialog...');
        await cdp.eval(`(() => {
            const dialog = document.querySelector('[role="dialog"]');
            const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
            if (saveBtn) saveBtn.click();
        })()`);
        await cdp.wait(4000);
        console.log('Dialog saved successfully!');
    }

    // 6. Check main track page
    const trackState = await cdp.eval(`(() => {
        const mainSaveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => r.innerText.trim().replace(/\\n/g, ' ')).filter(r => r.includes('Internal Testers'));
        return {
            rows,
            mainSaveDisabled: mainSaveBtn ? mainSaveBtn.disabled : null
        };
    })()`);
    console.log('Main track page state:', JSON.stringify(trackState, null, 2));

    if (trackState.mainSaveDisabled === false) {
        console.log('Clicking Save on track page...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
        console.log('Track page saved!');
    }

    await cdp.screenshot('track_testers_final_all.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
