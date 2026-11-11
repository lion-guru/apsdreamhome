import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Select "Yes" radio button
    const clickYes = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"], label'));
        const yesTarget = radios.find(r => (r.innerText || '').toLowerCase().startsWith('yes'));
        if (yesTarget) {
            yesTarget.scrollIntoView({ block: 'center' });
            yesTarget.click();
            return { clicked: true, text: yesTarget.innerText.substring(0, 30) };
        }
        return { error: 'Yes radio not found' };
    })()`);
    console.log('Click Yes result:', clickYes);
    await cdp.wait(2000);

    // Inspect visible inputs on the page
    const visibleInputs = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input, textarea'));
        return inputs.map(i => ({
            tag: i.tagName,
            type: i.type,
            placeholder: i.placeholder,
            ariaLabel: i.getAttribute('aria-label'),
            visible: i.offsetParent !== null
        }));
    })()`);
    console.log('Visible inputs after clicking Yes:\n', JSON.stringify(visibleInputs, null, 2));

    await cdp.screenshot('signin_after_yes.png');
    cdp.close();
}

main().catch(console.error);
