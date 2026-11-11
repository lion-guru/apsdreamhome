import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "Start questionnaire"
    const clickRes = await cdp.eval(`(() => {
        const btn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.includes('Start questionnaire'));
        if (btn) {
            btn.click();
            return { clicked: true };
        }
        return { error: 'Start questionnaire button not found' };
    })()`);
    console.log('Clicked Start questionnaire:', clickRes);
    await cdp.wait(3000);

    const stepInfo = await cdp.eval(`({
        title: document.title,
        url: window.location.href,
        inputs: Array.from(document.querySelectorAll('input')).map(i => ({
            type: i.type,
            placeholder: i.placeholder,
            ariaLabel: i.getAttribute('aria-label')
        })),
        radios: Array.from(document.querySelectorAll('material-radio, input[type="radio"], [role="radio"]')).map(r => ({
            text: r.innerText?.trim() || r.parentElement?.innerText?.trim(),
            checked: r.getAttribute('aria-checked') || r.checked
        })),
        buttons: Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled
        })).filter(b => b.text.length > 0 && b.text.length < 30)
    })`);

    console.log('Questionnaire step 1:\n', JSON.stringify(stepInfo, null, 2));
    await cdp.screenshot('content_rating_step1.png');
    cdp.close();
}

main().catch(console.error);
