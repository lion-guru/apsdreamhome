import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click button 1 (Financial features)
    const clickBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.includes('Start declaration'));
        if (btns.length > 1) {
            btns[1].scrollIntoView({ block: 'center' });
            btns[1].click();
            return { clicked: true, text: btns[1].innerText.trim() };
        }
        return { error: 'Button 1 not found' };
    })()`);
    console.log('Clicked Button 1 (Financial features):', clickBtn);
    await cdp.wait(3000);

    const info = await cdp.eval(`({
        url: window.location.href,
        title: document.title,
        checkboxes: Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"]')).map(c => ({
            text: c.innerText.trim(),
            checked: c.getAttribute('aria-checked')
        })),
        radios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
            text: r.innerText.trim(),
            checked: r.getAttribute('aria-checked')
        })),
        bodySnippet: document.body.innerText.substring(0, 1000)
    })`);
    console.log('Financial features page:\n', JSON.stringify(info, null, 2));
    await cdp.screenshot('financial_features_opened.png');
    cdp.close();
}

main().catch(console.error);
