import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Select "No" on all questions
    const selectAllNo = await cdp.eval(`(() => {
        const results = [];
        // In Material radio groups, find all radios whose label text is "No"
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"], label'));
        const noRadios = radios.filter(r => (r.innerText || '').trim() === 'No');
        
        for (const nr of noRadios) {
            nr.scrollIntoView({ block: 'center' });
            nr.click();
            results.push(true);
        }

        // Also check native input[type="radio"] whose value is 'no' or parent contains 'No'
        const nativeRadios = Array.from(document.querySelectorAll('input[type="radio"]'));
        const nativeNo = nativeRadios.filter(r => {
            const p = r.closest('label, material-radio, [role="radio"]') || r.parentElement;
            return (p?.innerText || '').trim() === 'No';
        });
        for (const nr of nativeNo) {
            if (!nr.checked) {
                nr.click();
            }
        }

        return {
            noRadiosFound: noRadios.length,
            nativeNoFound: nativeNo.length,
            clickedCount: results.length
        };
    })()`);

    console.log('Select all "No" result:', selectAllNo);
    await cdp.wait(2000);

    // Save button
    const clickSave = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, disabled: saveBtn.disabled };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Clicked Save:', clickSave);
    await cdp.wait(2000);

    // Next button
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        if (nextBtn) {
            nextBtn.click();
            return { clicked: true, disabled: nextBtn.disabled };
        }
        return { error: 'Next button not found' };
    })()`);
    console.log('Clicked Next:', clickNext);
    await cdp.wait(4000);

    await cdp.screenshot('rating_step3_summary.png');

    const step3 = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        buttons: Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(b => b.length > 0 && b.length < 30),
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('Step 3 summary:\n', JSON.stringify(step3, null, 2));

    cdp.close();
}

main().catch(console.error);
