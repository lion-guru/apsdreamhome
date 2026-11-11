import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Fix Delete account URL
    await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Delete account URL"]');
        if (input) {
            input.focus();
            input.select();
        }
    })()`);
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyDown', windowsVirtualKeyCode: 8 });
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: 8 });
    await cdp.send('Input.insertText', { text: 'https://apsdreamhome.com/privacy' });
    await cdp.wait(500);

    // Fix Delete data URL
    await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Delete data URL"]');
        if (input) {
            input.focus();
            input.select();
        }
    })()`);
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyDown', windowsVirtualKeyCode: 8 });
    await cdp.send('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: 8 });
    await cdp.send('Input.insertText', { text: 'https://apsdreamhome.com/privacy' });
    await cdp.wait(500);

    const values = await cdp.eval(`(() => {
        const acc = document.querySelector('input[aria-label="Delete account URL"]')?.value;
        const dat = document.querySelector('input[aria-label="Delete data URL"]')?.value;
        return { acc, dat };
    })()`);
    console.log('Cleaned input values:', values);

    // Click Next
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next button not clickable' };
    })()`);
    console.log('Clicked Next:', clickNext);
    await cdp.wait(4000);

    await cdp.screenshot('data_sec_after_clean_next.png');

    const nextState = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        header: document.querySelector('h1, h2, h3')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('Current state after Next:\n', JSON.stringify(nextState, null, 2));

    cdp.close();
}

main().catch(console.error);
