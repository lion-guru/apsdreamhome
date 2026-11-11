import { CDPClient } from './cdp_helper.mjs';

async function test() {
    const cdp = new CDPClient();
    await cdp.init();

    // Wait until loading indicator is gone or input appears
    let retries = 10;
    let inputExists = false;
    while (retries-- > 0) {
        inputExists = await cdp.eval('!!document.querySelector("input")');
        if (inputExists) break;
        await cdp.wait(1000);
    }

    const state = await cdp.eval(`(() => {
        const input = document.querySelector('input');
        return {
            exists: !!input,
            val: input?.value,
            placeholder: input?.placeholder,
            saveBtnText: document.querySelector('button[debug-id="main-button"]')?.innerText,
            saveDisabled: document.querySelector('button[debug-id="main-button"]')?.disabled
        };
    })()`);
    console.log('Page state:', state);

    if (state.exists) {
        // Click the input to focus
        await cdp.eval(`document.querySelector('input').focus()`);
        // Send key events for typing
        await cdp.send('Input.insertText', { text: 'https://apsdreamhome.com/privacy' });
        await cdp.wait(1000);

        const afterType = await cdp.eval(`(() => {
            const input = document.querySelector('input');
            const saveBtn = document.querySelector('button[debug-id="main-button"]');
            return {
                val: input.value,
                saveDisabled: saveBtn.disabled
            };
        })()`);
        console.log('After typing:', afterType);

        if (!afterType.saveDisabled) {
            await cdp.eval(`document.querySelector('button[debug-id="main-button"]').click()`);
            console.log('Clicked Save!');
            await cdp.wait(3000);
        }
    }

    await cdp.screenshot('privacy_final.png');
    cdp.close();
}

test().catch(console.error);
