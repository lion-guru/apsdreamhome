import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Google Play Console via CDP to add new testers...');
    const cdp = new CDPClient();
    await cdp.init();

    const testersUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4701293369268175450?tab=testers`;
    console.log('Navigating to Internal Testing testers tab:', testersUrl);
    await cdp.navigate(testersUrl);
    await cdp.wait(5000);

    // 1. Click "Edit email list Internal Testers"
    console.log('Clicking "Edit email list Internal Testers"...');
    const clickEditRes = await cdp.eval(`(() => {
        const btn = document.querySelector('button[aria-label="Edit email list Internal Testers"], button[aria-label*="Internal Testers"]');
        if (btn) {
            btn.scrollIntoView({ block: 'center' });
            btn.click();
            return { clicked: true, aria: btn.getAttribute('aria-label') };
        }
        return { clicked: false };
    })()`);
    console.log('Click Edit result:', clickEditRes);

    await cdp.wait(3000);

    // 2. Add each email address
    const emailsToAdd = [
        'subhamchaurasiyagkp@gmail.com',
        'nandani87389@gmail.com'
    ];

    for (const email of emailsToAdd) {
        console.log(`Adding tester: ${email}...`);

        // Focus the input
        const focused = await cdp.eval(`(() => {
            const input = document.querySelector('input[aria-label="Add email addresses"]');
            if (input) {
                input.focus();
                return true;
            }
            return false;
        })()`);

        if (!focused) {
            console.error('Could not find or focus Add email addresses input!');
            continue;
        }

        await cdp.wait(300);

        // Type the email
        await cdp.send('Input.insertText', { text: email });
        await cdp.wait(400);

        // Press Enter
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

        await cdp.wait(1200);

        // Fallback: If not added as chip, try dispatching comma / Enter via JS
        const chipCheck = await cdp.eval(`(() => {
            const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
            return dialog ? dialog.innerText.includes('${email}') : false;
        })()`);

        if (!chipCheck) {
            console.log(`Email chip not detected directly, sending JS Enter event for ${email}...`);
            await cdp.eval(`(() => {
                const input = document.querySelector('input[aria-label="Add email addresses"]');
                if (input) {
                    input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', code: 'Enter', keyCode: 13, which: 13, bubbles: true }));
                    input.dispatchEvent(new KeyboardEvent('keyup', { key: 'Enter', code: 'Enter', keyCode: 13, which: 13, bubbles: true }));
                }
            })()`);
            await cdp.wait(1000);
        }
    }

    // 3. Inspect dialog contents
    const dialogState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { noDialog: true };
        const text = dialog.innerText;
        const hasSubham = text.includes('subhamchaurasiyagkp@gmail.com');
        const hasNandani = text.includes('nandani87389@gmail.com');
        const hasBindu = text.includes('bindudevi0530@gmail.com');
        const hasTechguru = text.includes('techguruabhay@gmail.com');
        const saveChangesBtn = Array.from(dialog.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            hasSubham,
            hasNandani,
            hasBindu,
            hasTechguru,
            saveChangesDisabled: saveChangesBtn ? saveChangesBtn.disabled : null
        };
    })()`);
    console.log('Dialog state after adding emails:', dialogState);

    await cdp.screenshot('testers_in_dialog.png');

    // 4. Click "Save changes" in dialog
    console.log('Clicking "Save changes" button in dialog...');
    const saveChangesRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        const saveChangesBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        if (saveChangesBtn && !saveChangesBtn.disabled) {
            saveChangesBtn.click();
            return { clicked: true };
        }
        return { clicked: false, disabled: saveChangesBtn ? saveChangesBtn.disabled : null };
    })()`);
    console.log('Click Save changes result:', saveChangesRes);

    await cdp.wait(4000);

    // 5. Check if main track page has an active Save button
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
        console.log('Track page saved successfully!');
    }

    // 6. Capture join link and take final screenshot
    const joinLink = await cdp.eval(`(() => {
        const a = document.querySelector('a[href*="internaltest"]');
        if (a) return a.href;
        const copyBtn = document.querySelector('button[aria-label*="Copy link"]');
        return copyBtn ? copyBtn.getAttribute('aria-label') : 'https://play.google.com/apps/internaltest/4701293369268175450';
    })()`);
    console.log('Internal Test Join Link:', joinLink);

    await cdp.screenshot('testers_final_saved.png');
    console.log('Saved screenshot testers_final_saved.png');

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
