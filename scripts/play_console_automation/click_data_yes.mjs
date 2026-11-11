import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "Yes" on first question
    const clickYes = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"], label'));
        const yesRadio = radios.find(r => (r.innerText || '').trim() === 'Yes');
        if (yesRadio) {
            yesRadio.scrollIntoView({ block: 'center' });
            yesRadio.click();
            return { clicked: true };
        }
        return { error: 'Yes not found' };
    })()`);
    console.log('Clicked Yes:', clickYes);
    await cdp.wait(2000);

    const followUps = await cdp.eval(`(() => {
        return {
            radios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map((r, i) => ({
                idx: i,
                text: r.innerText.trim(),
                checked: r.getAttribute('aria-checked')
            })),
            text: document.body.innerText
        };
    })()`);

    console.log('Follow-ups after Yes:\n', JSON.stringify(followUps.radios, null, 2));
    await cdp.screenshot('data_sec_after_yes.png');
    cdp.close();
}

main().catch(console.error);
