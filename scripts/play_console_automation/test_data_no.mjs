import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Next on Step 1
    await cdp.eval(`Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled)?.click()`);
    await cdp.wait(2000);

    // Select "No" on Step 2
    const selectNo = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"], label'));
        const noRadio = radios.find(r => (r.innerText || '').trim() === 'No');
        if (noRadio) {
            noRadio.scrollIntoView({ block: 'center' });
            noRadio.click();
            return { clicked: true };
        }
        return { error: 'No radio not found' };
    })()`);
    console.log('Selected No on Step 2:', selectNo);
    await cdp.wait(1000);

    // Click Next
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next not clickable' };
    })()`);
    console.log('Clicked Next after No:', clickNext);
    await cdp.wait(3000);

    await cdp.screenshot('data_safety_after_no.png');

    const state = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        buttons: Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled
        })).filter(b => b.text.length > 0 && b.text.length < 30)
    }))()`);
    console.log('State after selecting No:\n', JSON.stringify(state, null, 2));

    cdp.close();
}

main().catch(console.error);
