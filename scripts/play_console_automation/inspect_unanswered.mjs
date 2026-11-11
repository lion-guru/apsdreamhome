import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const sectionsInfo = await cdp.eval(`(() => {
        // Collect each section text
        const sections = Array.from(document.querySelectorAll('section, [class*="section"], [class*="panel"], div[class*="content"]')).filter(s => {
            const t = s.innerText || '';
            return t.includes('User Content') || t.includes('Online Content') || t.includes('Promotion') || t.includes('Miscellaneous');
        });

        // Collect all question texts and their radios
        const questions = Array.from(document.querySelectorAll('h4, [class*="question"], div[class*="prompt"]')).map(q => ({
            text: q.innerText.trim(),
            radios: Array.from(q.parentElement?.querySelectorAll('material-radio, [role="radio"]') || []).map(r => ({
                label: r.innerText.trim(),
                checked: r.getAttribute('aria-checked')
            }))
        }));

        return {
            unansweredQuestions: questions.filter(q => q.radios.length > 0 && !q.radios.some(r => r.checked === 'true')),
            totalQuestions: questions.length,
            bodyText: document.body.innerText
        };
    })()`);

    console.log('Unanswered questions:\n', JSON.stringify(sectionsInfo.unansweredQuestions, null, 2));
    console.log('\nFull body:\n', sectionsInfo.bodyText.substring(0, 2500));
    cdp.close();
}

main().catch(console.error);
