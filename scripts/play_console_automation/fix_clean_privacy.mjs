import { CDPClient } from './cdp_helper.mjs';

async function fix() {
    const cdp = new CDPClient();
    await cdp.init();

    // Select all and replace
    await cdp.eval(`(() => {
        const input = document.querySelector('input');
        input.focus();
        input.select();
    })()`);

    // Backspace to clear
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyDown', windowsVirtualKeyCode: 8 });
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: 8 });
    await cdp.wait(500);

    // Insert clean URL
    await cdp.send('Input.insertText', { text: 'https://apsdreamhome.com/privacy' });
    await cdp.wait(1000);

    const state = await cdp.eval(`(() => {
        const input = document.querySelector('input');
        const saveBtn = document.querySelector('button[debug-id="main-button"]');
        return {
            val: input.value,
            saveDisabled: saveBtn.disabled
        };
    })()`);
    console.log('Fixed state:', state);

    if (!state.saveDisabled) {
        await cdp.eval(`document.querySelector('button[debug-id="main-button"]').click()`);
        console.log('Clicked Save!');
        await cdp.wait(3000);
    }

    await cdp.screenshot('privacy_clean_saved.png');
    cdp.close();
}

fix().catch(console.error);
