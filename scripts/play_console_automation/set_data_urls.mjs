import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const setRes = await cdp.eval(`(() => {
        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        
        const accInput = document.querySelector('input[aria-label="Delete account URL"]');
        const datInput = document.querySelector('input[aria-label="Delete data URL"]');

        if (accInput) {
            nativeSet.call(accInput, 'https://apsdreamhome.com/privacy');
            accInput.dispatchEvent(new Event('input', { bubbles: true }));
            accInput.dispatchEvent(new Event('change', { bubbles: true }));
            accInput.dispatchEvent(new Event('blur', { bubbles: true }));
        }

        if (datInput) {
            nativeSet.call(datInput, 'https://apsdreamhome.com/privacy');
            datInput.dispatchEvent(new Event('input', { bubbles: true }));
            datInput.dispatchEvent(new Event('change', { bubbles: true }));
            datInput.dispatchEvent(new Event('blur', { bubbles: true }));
        }

        return {
            accVal: accInput?.value,
            datVal: datInput?.value
        };
    })()`);

    console.log('Set URLs result:', setRes);
    await cdp.wait(1000);

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

    await cdp.screenshot('data_safety_step3_loaded.png');

    const state = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('Current state:\n', JSON.stringify(state, null, 2));

    cdp.close();
}

main().catch(console.error);
