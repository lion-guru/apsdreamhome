import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Check if dialog is open, if not open it
    const isOpen = await cdp.eval(`!!document.querySelector('[role="dialog"]')`);
    if (!isOpen) {
        console.log('Dialog not open, clicking Edit email list...');
        await cdp.eval(`(() => {
            const btn = document.querySelector('button[aria-label*="Internal Testers"], [aria-label="Edit email list Internal Testers"]');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
    }

    const state1 = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        return {
            exists: !!input,
            value: input?.value,
            focused: document.activeElement === input,
            rect: input ? input.getBoundingClientRect() : null
        };
    })()`);
    console.log('Input state before click:', state1);

    if (state1.rect) {
        const x = state1.rect.x + state1.rect.width / 2;
        const y = state1.rect.y + state1.rect.height / 2;
        console.log('Clicking at', x, y);
        await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x, y, button: 'left', clickCount: 1 });
        await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x, y, button: 'left', clickCount: 1 });
        await cdp.wait(500);
    }

    const state2 = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        return {
            focused: document.activeElement === input,
            activeTag: document.activeElement?.tagName,
            activeAria: document.activeElement?.getAttribute('aria-label')
        };
    })()`);
    console.log('Input state after click:', state2);

    // Now let's try typing "test@example.com" using Input.insertText
    console.log('Inserting text "subhamchaurasiyagkp@gmail.com" via Input.insertText...');
    await cdp.send('Input.insertText', { text: 'subhamchaurasiyagkp@gmail.com' });
    await cdp.wait(500);

    const state3 = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        return {
            val: input?.value,
            dialogText: document.querySelector('[role="dialog"]')?.innerText
        };
    })()`);
    console.log('Input state after insertText:', { val: state3.val });

    // Now let's test pressing Enter
    console.log('Pressing Enter...');
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

    const state4 = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        const dialog = document.querySelector('[role="dialog"]');
        return {
            inputVal: input?.value,
            dialogSnippet: dialog?.innerText.substring(0, 400),
            hasSubham: dialog?.innerText.includes('subhamchaurasiyagkp@gmail.com')
        };
    })()`);
    console.log('State after Enter:', state4);

    await cdp.screenshot('probe_after_enter.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
