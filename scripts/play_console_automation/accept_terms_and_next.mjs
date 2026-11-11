import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const cbInfo = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('input[type="checkbox"], material-checkbox, [role="checkbox"]'));
        return cbs.map(c => {
            const p = c.closest('label, div') || c.parentElement;
            return {
                text: p?.innerText.trim(),
                checked: c.checked || c.getAttribute('aria-checked')
            };
        });
    })()`);
    console.log('Checkboxes on step 1:\n', JSON.stringify(cbInfo, null, 2));

    // Check the Terms checkbox
    const checkRes = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('input[type="checkbox"], material-checkbox, [role="checkbox"]'));
        if (cbs.length > 0) {
            cbs[0].scrollIntoView({ block: 'center' });
            cbs[0].click();
            return { checked: true, text: cbs[0].parentElement?.innerText?.trim() };
        }
        return { error: 'No checkbox found' };
    })()`);
    console.log('Check result:', checkRes);
    await cdp.wait(1000);

    // Now click Next button
    const nextRes = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        if (nextBtn) {
            nextBtn.click();
            return { clicked: true, disabled: nextBtn.disabled };
        }
        return { error: 'Next button not found' };
    })()`);
    console.log('Clicked Next:', nextRes);
    await cdp.wait(4000);

    await cdp.screenshot('rating_step2_questions.png');

    const step2Info = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('Step 2 loaded info:\n', JSON.stringify(step2Info, null, 2));

    cdp.close();
}

main().catch(console.error);
