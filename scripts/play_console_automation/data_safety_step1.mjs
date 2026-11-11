import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "Next"
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next button not found' };
    })()`);
    console.log('Clicked Next:', clickNext);
    await cdp.wait(3000);

    const step1Info = await cdp.eval(`(() => {
        const questions = Array.from(document.querySelectorAll('h3, [role="heading"], div[class*="question"]')).map(q => q.innerText.trim()).filter(t => t.length > 5);
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
            text: r.innerText.trim(),
            checked: r.getAttribute('aria-checked')
        }));
        return {
            title: document.title,
            questions,
            radiosCount: radios.length,
            sampleRadios: radios.slice(0, 10),
            bodySnippet: document.body.innerText.substring(0, 1000)
        };
    })()`);

    console.log('Data safety step 1 info:\n', JSON.stringify(step1Info, null, 2));
    await cdp.screenshot('data_safety_step1.png');
    cdp.close();
}

main().catch(console.error);
