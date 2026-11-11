import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Iterate through accordion sections
    for (let step = 0; step < 10; step++) {
        const loopRes = await cdp.eval(`(() => {
            // Find all unchecked "No" radios
            const allRadios = Array.from(document.querySelectorAll('material-radio, [role="radio"]'));
            const uncheckedNo = allRadios.filter(r => {
                const text = (r.innerText || '').trim();
                const isChecked = r.getAttribute('aria-checked') === 'true';
                return text === 'No' && !isChecked;
            });

            if (uncheckedNo.length > 0) {
                uncheckedNo[0].scrollIntoView({ block: 'center' });
                uncheckedNo[0].click();
                return { action: 'clicked_no', remaining: uncheckedNo.length - 1 };
            }

            // Check if any sections (accordions) need to be clicked to expand
            const uncompletedSections = Array.from(document.querySelectorAll('mat-expansion-panel-header, .section-header, [role="button"]')).filter(s => {
                return (s.innerText || '').includes('User Content') || 
                       (s.innerText || '').includes('Online Content') || 
                       (s.innerText || '').includes('Promotion or Sale') || 
                       (s.innerText || '').includes('Miscellaneous');
            });

            return { action: 'none_found', uncompletedSections: uncompletedSections.map(s => s.innerText.trim()) };
        })()`);

        console.log(`Step ${step}:`, loopRes);
        await cdp.wait(1500);

        if (loopRes.action === 'none_found') {
            break;
        }
    }

    // Save
    await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn && !saveBtn.disabled) saveBtn.click();
    })()`);
    await cdp.wait(2000);

    // Next
    const nextRes = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        if (nextBtn) {
            nextBtn.click();
            return { nextClicked: true, disabled: nextBtn.disabled };
        }
        return { error: 'Next not found' };
    })()`);
    console.log('Next button:', nextRes);
    await cdp.wait(4000);

    await cdp.screenshot('rating_after_loop.png');

    const state = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('State after loop:\n', JSON.stringify(state, null, 2));

    cdp.close();
}

main().catch(console.error);
