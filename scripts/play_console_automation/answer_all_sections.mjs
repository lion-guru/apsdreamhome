import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    for (let round = 0; round < 8; round++) {
        const stepRes = await cdp.eval(`(() => {
            // Find all material-radios that have label text "No" or are the second radio in a group
            // In Google Play Console questionnaire, radios often come in pairs: [Yes, No]
            // Let's find any radio with text 'No' or second radio inside a question block where neither radio is checked
            const questions = Array.from(document.querySelectorAll('question, .question, [class*="question-container"], .question-item, div[class*="question"]'));
            
            // Or simpler: find any radio with text 'No' where checked is not true
            const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"]'));
            const uncheckedNo = radios.find(r => {
                const text = (r.innerText || '').trim();
                return text === 'No' && r.getAttribute('aria-checked') !== 'true';
            });

            if (uncheckedNo) {
                uncheckedNo.scrollIntoView({ block: 'center' });
                uncheckedNo.click();
                return { action: 'clicked_named_no' };
            }

            // If "No" label is in a sibling or child, let's find input[type="radio"] whose parent text is 'No'
            const labels = Array.from(document.querySelectorAll('label, .mdc-radio, [class*="radio"]')).filter(l => (l.innerText || '').trim() === 'No');
            const uncheckLabel = labels.find(l => {
                const input = l.querySelector('input') || l;
                return !input.checked && input.getAttribute('aria-checked') !== 'true';
            });
            if (uncheckLabel) {
                uncheckLabel.scrollIntoView({ block: 'center' });
                uncheckLabel.click();
                return { action: 'clicked_label_no' };
            }

            // Or find headers that need to be expanded
            const headers = Array.from(document.querySelectorAll('.header-line, .header')).filter(h => {
                return (h.innerText || '').includes('User Content') || 
                       (h.innerText || '').includes('Online Content') || 
                       (h.innerText || '').includes('Promotion') || 
                       (h.innerText || '').includes('Miscellaneous');
            });
            for (const h of headers) {
                const parent = h.closest('section, div[class*="section"]') || h.parentElement;
                if (!parent?.innerText?.includes('Completed')) {
                    h.click();
                    return { action: 'expanded_header', text: h.innerText.trim() };
                }
            }

            return { action: 'done' };
        })()`);

        console.log(`Round ${round}:`, stepRes);
        await cdp.wait(1500);
        if (stepRes.action === 'done') break;
    }

    // Save
    await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn && !saveBtn.disabled) saveBtn.click();
    })()`);
    await cdp.wait(2000);

    const checkState = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        return {
            bodySnippet: document.body.innerText.substring(0, 1000),
            nextDisabled: nextBtn?.disabled
        };
    })()`);
    console.log('After answer rounds:', checkState);

    await cdp.screenshot('rating_after_rounds.png');
    cdp.close();
}

main().catch(console.error);
