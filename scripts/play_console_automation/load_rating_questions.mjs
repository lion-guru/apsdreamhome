import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

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

    const questionsPage = await cdp.eval(`(() => {
        return {
            title: document.title,
            header: document.querySelector('h1, h2, h3')?.innerText,
            radioGroups: Array.from(document.querySelectorAll('material-radio-group, [role="radiogroup"], fieldset, .question-container')).map(g => {
                const qText = g.innerText?.split('\\n')?.[0] || '';
                const options = Array.from(g.querySelectorAll('material-radio, [role="radio"], label')).map(r => r.innerText?.trim()).filter(Boolean);
                return { qText, options };
            }),
            allRadios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
                text: r.innerText.trim(),
                checked: r.getAttribute('aria-checked')
            })),
            buttons: Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(b => b.length > 0 && b.length < 30)
        };
    })()`);
    console.log('Questions on Step 2:\n', JSON.stringify(questionsPage, null, 2));

    await cdp.screenshot('rating_questions_loaded.png');
    cdp.close();
}

main().catch(console.error);
