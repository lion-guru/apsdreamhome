import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const email = 'subhamchaurasiyagkp@gmail.com';

    // 1. Clear input and set exact clean email
    const setRes = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        if (!input) return { error: 'No input' };

        input.focus();
        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        nativeSet.call(input, '${email}');
        input.dispatchEvent(new Event('input', { bubbles: true, composed: true }));
        input.dispatchEvent(new Event('change', { bubbles: true, composed: true }));

        return { val: input.value, invalid: input.closest('material-input')?.classList.contains('invalid') };
    })()`);
    console.log('Set clean email:', setRes);

    await cdp.wait(500);

    // 2. Dispatch real CDP Enter key
    console.log('Dispatching CDP Enter keydown, char, keyup...');
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

    // 3. Also dispatch synthetic KeyboardEvents with bubbles: true on input and material-input
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

    await cdp.wait(2000);

    // 4. Check if added to list
    const check = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"]');
        const text = dialog ? dialog.innerText : '';
        const input = document.querySelector('input[aria-label="Add email addresses"]');
        return {
            inputVal: input?.value,
            hasSubham: text.includes('${email}'),
            dialogSnippet: text.slice(0, 400).replace(/\\n/g, ' ')
        };
    })()`);
    console.log('Result after Enter:', check);

    await cdp.screenshot('test_clean_enter.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
