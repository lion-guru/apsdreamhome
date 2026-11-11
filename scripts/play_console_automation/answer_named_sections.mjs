import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const sections = [
        'User Content Sharing',
        'Online Content',
        'Promotion or Sale of Age-Restricted Products or Activities',
        'Miscellaneous'
    ];

    for (const secName of sections) {
        console.log(`\n--- Processing section: ${secName} ---`);
        const secRes = await cdp.eval(`(() => {
            // Find element containing secName
            const allHeaders = Array.from(document.querySelectorAll('*')).filter(el => 
                el.children.length === 0 && el.innerText.trim() === '${secName}'
            );
            if (allHeaders.length === 0) return { error: 'Header not found' };

            // Find parent container
            let container = allHeaders[0];
            for (let i = 0; i < 8 && container; i++) {
                if (container.querySelectorAll('material-radio, [role="radio"]').length > 0) {
                    break;
                }
                container = container.parentElement;
            }

            // Click the header to open if not already open
            allHeaders[0].click();

            // Find all "No" radios inside this container
            const radios = Array.from(container.querySelectorAll('material-radio, [role="radio"], label'));
            const noRadios = radios.filter(r => (r.innerText || '').trim() === 'No');
            
            const clicked = [];
            for (const nr of noRadios) {
                if (nr.getAttribute('aria-checked') !== 'true') {
                    nr.scrollIntoView({ block: 'center' });
                    nr.click();
                    clicked.push(true);
                }
            }

            return {
                headerClicked: true,
                noRadiosCount: noRadios.length,
                newlyClicked: clicked.length,
                containerTextSnippet: container.innerText.substring(0, 150)
            };
        })()`);

        console.log('Section result:', secRes);
        await cdp.wait(2000);
    }

    // After clicking all sections, let's see if any sub-questions appeared
    for (let round = 0; round < 5; round++) {
        const subRes = await cdp.eval(`(() => {
            const allNo = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).filter(r => 
                (r.innerText || '').trim() === 'No' && r.getAttribute('aria-checked') !== 'true'
            );
            if (allNo.length > 0) {
                allNo[0].scrollIntoView({ block: 'center' });
                allNo[0].click();
                return { clickedUncheckedNo: true, remaining: allNo.length - 1 };
            }
            return { allNoChecked: true };
        })()`);
        console.log(`Sub-check round ${round}:`, subRes);
        await cdp.wait(1500);
        if (subRes.allNoChecked) break;
    }

    // Save
    await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn && !saveBtn.disabled) saveBtn.click();
    })()`);
    await cdp.wait(2000);

    // Check Next button
    const nextState = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        return {
            nextFound: !!nextBtn,
            nextDisabled: nextBtn?.disabled
        };
    })()`);
    console.log('Next button state:', nextState);

    if (nextState.nextFound && !nextState.nextDisabled) {
        await cdp.eval(`Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next').click()`);
        console.log('Clicked Next!');
        await cdp.wait(4000);
    }

    await cdp.screenshot('rating_sections_done.png');

    const finalState = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 800)
    }))()`);
    console.log('Final questionnaire state:\n', JSON.stringify(finalState, null, 2));

    cdp.close();
}

main().catch(console.error);
