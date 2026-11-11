import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const noIndices = [6, 10, 14, 18, 22, 26, 30, 34];
    console.log('Clicking No for all remaining questions...');

    for (const idx of noIndices) {
        const clickRes = await cdp.eval(`(() => {
            const allRadios = Array.from(document.querySelectorAll('material-radio, [role="radio"]'));
            if (${idx} < allRadios.length) {
                const r = allRadios[${idx}];
                r.scrollIntoView({ block: 'center' });
                r.click();
                return { clickedIdx: ${idx}, text: r.innerText.trim() };
            }
            return { error: 'Index out of bounds: ' + ${idx} };
        })()`);
        console.log(`Radio ${idx}:`, clickRes);
        await cdp.wait(500);
    }

    // Wait 1 second
    await cdp.wait(1000);

    // Click Save
    const saveRes = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { saved: true, disabled: saveBtn.disabled };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Save result:', saveRes);
    await cdp.wait(2000);

    // Check Next button
    const nextRes = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        if (nextBtn) {
            const disabled = nextBtn.disabled;
            if (!disabled) nextBtn.click();
            return { nextFound: true, disabled, clicked: !disabled };
        }
        return { error: 'Next button not found' };
    })()`);
    console.log('Next button result:', nextRes);
    await cdp.wait(4000);

    await cdp.screenshot('rating_step3_summary_reached.png');

    const stepInfo = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        buttons: Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(b => b.length > 0 && b.length < 30),
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('Step reached:\n', JSON.stringify(stepInfo, null, 2));

    cdp.close();
}

main().catch(console.error);
