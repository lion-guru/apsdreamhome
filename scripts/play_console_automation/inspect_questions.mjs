import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const sections = await cdp.eval(`(() => {
        // Find each question block
        const questions = [];
        const questionElements = document.querySelectorAll('h3, [role="heading"], .question-container, mat-expansion-panel');
        
        // Find all radio elements
        const allRadios = Array.from(document.querySelectorAll('material-radio, [role="radio"], input[type="radio"]')).map((r, i) => {
            const label = r.innerText.trim() || r.parentElement?.innerText.trim();
            let p = r.parentElement;
            let questionTitle = '';
            for (let j = 0; j < 6 && p; j++) {
                const h = p.querySelector('h2, h3, h4, [role="heading"]');
                if (h && h.innerText.trim() !== questionTitle) {
                    questionTitle = h.innerText.trim();
                }
                p = p.parentElement;
            }
            return {
                idx: i,
                label,
                questionTitle: questionTitle.substring(0, 100),
                checked: r.getAttribute('aria-checked') || r.checked
            };
        });

        return allRadios;
    })()`);

    console.log('Questionnaire radios:\n', JSON.stringify(sections, null, 2));
    cdp.close();
}

main().catch(console.error);
