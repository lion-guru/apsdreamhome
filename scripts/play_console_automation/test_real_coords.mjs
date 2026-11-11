import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

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
    console.log('Input exact coords:', coords);

    if (!coords) {
        throw new Error('Input not found');
    }

    // 2. Click with real CDP mouse events
    console.log('Clicking input at', coords.x, coords.y);
    await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: coords.x, y: coords.y, button: 'left', clickCount: 1 });
    await cdp.wait(300);

    // Check what is currently in the input and select-all / backspace to clear
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyDown', commands: ['selectAll'] });
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp', commands: ['selectAll'] });
    await cdp.wait(100);

    // 3. Type email char by char
    const email = 'subhamchaurasiyagkp@gmail.com';
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

    await cdp.wait(2000);

    // 5. Inspect result
    const result = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"]');
        const text = dialog ? dialog.innerText : '';
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        const saveBtn = Array.from((dialog || document).querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save changes');
        return {
            inputVal: input?.value,
            hasSubham: text.includes('subhamchaurasiyagkp@gmail.com'),
            saveBtnDisabled: saveBtn ? saveBtn.disabled : null,
            dialogSnippet: text.slice(0, 500).replace(/\\n/g, ' ')
        };
    })()`);
    console.log('Result after typing with real mouse click:', JSON.stringify(result, null, 2));

    await cdp.screenshot('real_click_result.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
