import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting via CDP to add nandani87389 and bindudevi0530 and save changes...');
    const cdp = new CDPClient();
    await cdp.init();

    const emails = [
        'nandani87389@gmail.com',
        'bindudevi0530@gmail.com'
    ];

    for (const email of emails) {
        console.log(`\nAdding "${email}"...`);

        // Check if already in dialog
        const alreadyIn = await cdp.eval(`(() => {
            const dialog = document.querySelector('[role="dialog"]');
            return dialog ? dialog.innerText.includes('${email}') : false;
        })()`);

        if (alreadyIn) {
            console.log(`Email ${email} is already in the list!`);
            continue;
        }

        // 1. Get exact numeric coordinates of the email input
        const coords = await cdp.eval(`(() => {
            const input = document.querySelector('input[aria-label="Add email addresses"]');
            if (!input) return null;
            const r = input.getBoundingClientRect();
            return {
                x: Math.round(r.left + r.width / 2),
                y: Math.round(r.top + r.height / 2)
            };
        })()`);

        if (!coords) {
            throw new Error('Email input not found');
        }

        // 2. Click with real CDP mouse events
        await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
        await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
        await cdp.wait(300);

        // 3. Type email char by char
        console.log(`Typing "${email}"...`);
        for (const char of email) {
            await cdp.send('Input.dispatchKeyEvent', {
                type: 'keyDown',
                text: char,
                unmodifiedText: char
            });
            await cdp.send('Input.dispatchKeyEvent', {
                type: 'keyUp'
            });
            await cdp.wait(20);
        }
        await cdp.wait(400);

        // 4. Press Enter key
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

        await cdp.wait(1500);

        const check = await cdp.eval(`(() => {
            const dialog = document.querySelector('[role="dialog"]');
            return dialog ? dialog.innerText.includes('${email}') : false;
        })()`);
        console.log(`Verification for ${email}:`, check ? 'ADDED' : 'NOT FOUND');
    }

    // Inspect table state
    const tableState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"]');
        const text = dialog ? dialog.innerText : '';
        const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            hasTechguru: text.includes('techguruabhay@gmail.com'),
            hasSubham: text.includes('subhamchaurasiyagkp@gmail.com'),
            hasNandani: text.includes('nandani87389@gmail.com'),
            hasBindu: text.includes('bindudevi0530@gmail.com'),
            saveBtnDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);
    console.log('\nFinal Dialog Table State:\n', JSON.stringify(tableState, null, 2));

    await cdp.screenshot('all_testers_in_dialog_final.png');

    // 5. Click "Save changes" in dialog
    if (tableState.saveBtnDisabled === false) {
        console.log('Clicking "Save changes" button in dialog...');
        await cdp.eval(`(() => {
            const dialog = document.querySelector('[role="dialog"]');
            const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
            if (saveBtn) saveBtn.click();
        })()`);
        await cdp.wait(4000);
        console.log('Dialog saved!');
    }

    // 6. Check main track page
    const trackSaveState = await cdp.eval(`(() => {
        const mainSaveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => r.innerText.trim().replace(/\\n/g, ' ')).filter(r => r.includes('Internal Testers'));
        return {
            rows,
            saveDisabled: mainSaveBtn ? mainSaveBtn.disabled : null
        };
    })()`);
    console.log('\nMain track page state:', JSON.stringify(trackSaveState, null, 2));

    if (trackSaveState.saveDisabled === false) {
        console.log('Clicking Save on main track page...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
        console.log('Track page saved!');
    }

    // 7. Get final join link and screenshot
    const joinLink = await cdp.eval(`(() => {
        const copyBtn = document.querySelector('button[aria-label*="Copy link"]');
        return copyBtn ? copyBtn.getAttribute('aria-label') : 'https://play.google.com/apps/internaltest/4701293369268175450';
    })()`);
    console.log('\nInternal Test Join Link:', joinLink);

    await cdp.screenshot('all_testers_track_saved.png');
    console.log('Saved screenshot all_testers_track_saved.png');

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
