import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/privacy-policy`);
    await cdp.wait(2000);

    const testSet = await cdp.eval(`(() => {
        const input = document.querySelector('input');
        if (!input) return { error: 'No input' };

        // Angular reactive form value setter
        const nativeSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        nativeSetter.call(input, 'https://apsdreamhome.com/privacy');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.dispatchEvent(new Event('blur', { bubbles: true }));

        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        return {
            inputValue: input.value,
            saveBtnFound: !!saveBtn,
            saveBtnDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);

    console.log('After native setter:', testSet);
    await cdp.wait(1000);

    // Click Save
    const clickSave = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn && !saveBtn.disabled) {
            saveBtn.click();
            return { clicked: true };
        }
        return { clicked: false, disabled: saveBtn?.disabled };
    })()`);

    console.log('Click save result:', clickSave);
    await cdp.wait(3000);

    await cdp.screenshot('privacy_after_save.png');
    cdp.close();
}

main().catch(console.error);
