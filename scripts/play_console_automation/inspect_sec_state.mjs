import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const pageInspect = await cdp.eval(`(() => {
        // Find any alerts or invalid states
        const alerts = Array.from(document.querySelectorAll('[role="alert"], .error, mat-error, [aria-invalid="true"]')).map(a => a.innerText.trim());
        const uncheckedQuestions = [];
        document.querySelectorAll('question, .question, [class*="question"]').forEach(q => {
            const hasChecked = q.querySelector('[aria-checked="true"], input:checked');
            if (!hasChecked) {
                uncheckedQuestions.push(q.innerText.split('\\n')[0]);
            }
        });

        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        return {
            alerts,
            uncheckedQuestions,
            nextDisabled: nextBtn?.disabled,
            nextRect: nextBtn?.getBoundingClientRect()
        };
    })()`);

    console.log(JSON.stringify(pageInspect, null, 2));
    cdp.close();
}

main().catch(console.error);
