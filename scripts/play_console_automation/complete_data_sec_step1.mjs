import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Delete account URL
    await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Delete account URL"]') || document.querySelectorAll('input[type="text"]')[0];
        if (input) {
            input.focus();
            input.select();
        }
    })()`);
    await cdp.send('Input.insertText', { text: 'https://apsdreamhome.com/privacy' });
    await cdp.wait(500);

    // 2. Select Yes for "Do you provide a way for users to request that their data be deleted?"
    const deleteYes = await cdp.eval(`(() => {
        const allRadios = Array.from(document.querySelectorAll('material-radio, [role="radio"]'));
        // Find radio with text Yes that is not checked yet
        const uncheckedYes = allRadios.filter(r => (r.innerText || '').trim() === 'Yes' && r.getAttribute('aria-checked') !== 'true');
        if (uncheckedYes.length > 0) {
            uncheckedYes[0].scrollIntoView({ block: 'center' });
            uncheckedYes[0].click();
            return { clicked: true };
        }
        return { error: 'Unchecked Yes radio not found' };
    })()`);
    console.log('Selected Yes for data deletion:', deleteYes);
    await cdp.wait(1000);

    // 3. Delete data URL if present
    await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Delete data URL"]') || document.querySelectorAll('input[type="text"]')[1];
        if (input) {
            input.focus();
            input.select();
        }
    })()`);
    await cdp.send('Input.insertText', { text: 'https://apsdreamhome.com/privacy' });
    await cdp.wait(500);

    // 4. Click Next
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        if (nextBtn && !nextBtn.disabled) {
            nextBtn.click();
            return { clicked: true };
        }
        return { clicked: false, disabled: nextBtn?.disabled };
    })()`);
    console.log('Clicked Next:', clickNext);
    await cdp.wait(4000);

    await cdp.screenshot('data_safety_step2_types.png');

    const nextStepState = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('Next step state:\n', JSON.stringify(nextStepState, null, 2));

    cdp.close();
}

main().catch(console.error);
