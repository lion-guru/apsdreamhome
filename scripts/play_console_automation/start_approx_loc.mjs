import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "Start" on Approximate location
    const clickStart = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button, a[role="button"]'));
        const startBtn = btns.find(b => b.innerText.trim() === 'Start' || b.innerText.includes('Start'));
        if (startBtn) {
            startBtn.scrollIntoView({ block: 'center' });
            startBtn.click();
            return { clicked: true, text: startBtn.innerText.trim() };
        }
        return { error: 'Start button not found' };
    })()`);
    console.log('Clicked Start on Approximate location:', clickStart);
    await cdp.wait(3000);

    const questions = await cdp.eval(`(() => {
        return {
            title: document.title,
            radios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
                text: r.innerText.trim(),
                checked: r.getAttribute('aria-checked')
            })),
            checkboxes: Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"]')).map(c => ({
                text: c.innerText.trim(),
                checked: c.getAttribute('aria-checked')
            })),
            bodySnippet: document.body.innerText.substring(0, 1500)
        };
    })()`);

    console.log('Approximate location questions:\n', JSON.stringify(questions, null, 2));
    await cdp.screenshot('approx_location_questions.png');
    cdp.close();
}

main().catch(console.error);
