import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.send('Network.enable');

    cdp.ws.addEventListener('message', (evt) => {
        const msg = JSON.parse(evt.data);
        if (msg.method === 'Network.responseReceived') {
            const url = msg.params.response.url;
            if (url.includes('play.google.com') && !url.includes('gstatic') && !url.includes('googleusercontent')) {
                console.log('Network Response:', msg.params.response.status, url.split('?')[0]);
            }
        }
    });

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

    console.log('Input coords:', coords);
    if (!coords) throw new Error('No input');

    // 2. Click input
    await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
    await cdp.wait(300);

    // 3. Type subham
    const email = 'subhamchaurasiyagkp@gmail.com';
    console.log(`Typing "${email}"...`);
    for (const char of email) {
        await cdp.send('Input.dispatchKeyEvent', { type: 'keyDown', text: char, unmodifiedText: char });
        await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp' });
        await cdp.wait(20);
    }
    await cdp.wait(300);

    // 4. Enter
    await cdp.send('Input.dispatchKeyEvent', { type: 'rawKeyDown', windowsVirtualKeyCode: 13, nativeVirtualKeyCode: 13, key: 'Enter', code: 'Enter' });
    await cdp.send('Input.dispatchKeyEvent', { type: 'char', text: '\r', unmodifiedText: '\r' });
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: 13, nativeVirtualKeyCode: 13, key: 'Enter', code: 'Enter' });
    await cdp.wait(1500);

    // 5. Check Save changes button coordinates
    const btnCoords = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"]');
        const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        if (!saveBtn) return { notFound: true };
        const r = saveBtn.getBoundingClientRect();
        return {
            disabled: saveBtn.disabled,
            x: Math.round(r.left + r.width / 2),
            y: Math.round(r.top + r.height / 2)
        };
    })()`);
    console.log('Save changes button info:', btnCoords);

    if (btnCoords && !btnCoords.disabled) {
        console.log('Clicking Save changes button with CDP mouse click at', btnCoords.x, btnCoords.y);
        await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: btnCoords.x, y: btnCoords.y, button: 'left', clickCount: 1 });
        await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: btnCoords.x, y: btnCoords.y, button: 'left', clickCount: 1 });
        await cdp.wait(5000);
    }

    // Inspect dialog and main page state
    const after = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"]');
        const mainSave = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => r.innerText.trim().replace(/\\n/g, ' ')).filter(r => r.includes('Internal Testers'));
        return {
            dialogStillOpen: !!dialog,
            dialogText: dialog ? dialog.innerText.slice(0, 300) : null,
            mainSaveDisabled: mainSave ? mainSave.disabled : null,
            rows
        };
    })()`);
    console.log('After Save changes clicked:', JSON.stringify(after, null, 2));

    await cdp.screenshot('after_save_changes_click.png');

    // If main save button is enabled, click it
    if (after.mainSaveDisabled === false) {
        console.log('Main page Save button is enabled! Clicking it...');
        const mainSaveCoords = await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
            if (!btn) return null;
            const r = btn.getBoundingClientRect();
            return { x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2) };
        })()`);
        if (mainSaveCoords) {
            await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: mainSaveCoords.x, y: mainSaveCoords.y, button: 'left', clickCount: 1 });
            await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: mainSaveCoords.x, y: mainSaveCoords.y, button: 'left', clickCount: 1 });
            await cdp.wait(4000);
            console.log('Main Save clicked!');
        }
    }

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
