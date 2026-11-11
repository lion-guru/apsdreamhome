import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Find and click the first "Start declaration" button
    const clickRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.includes('Start declaration'));
        if (btns.length === 0) return { error: 'No Start declaration button found' };
        
        // Find which card this button belongs to
        let card = btns[0];
        let cardTitle = '';
        for (let i = 0; i < 6; i++) {
            card = card?.parentElement;
            if (!card) break;
            const h = card.querySelector('h3, h2, div[role="heading"]');
            if (h) {
                cardTitle = h.innerText.trim();
                break;
            }
        }
        
        btns[0].scrollIntoView({ block: 'center' });
        btns[0].click();
        return { clicked: true, cardTitle, remaining: btns.length };
    })()`);

    console.log('Clicked first declaration button:', clickRes);
    await cdp.wait(4000);

    const info = await cdp.eval(`({
        title: document.title,
        url: window.location.href,
        radios: Array.from(document.querySelectorAll('material-radio, input[type="radio"], [role="radio"]')).map(r => ({
            text: r.innerText?.trim() || r.parentElement?.innerText?.trim(),
            checked: r.getAttribute('aria-checked') || r.checked
        })),
        checkboxes: Array.from(document.querySelectorAll('material-checkbox, input[type="checkbox"], [role="checkbox"]')).map(c => ({
            text: c.innerText?.trim() || c.parentElement?.innerText?.trim(),
            checked: c.getAttribute('aria-checked') || c.checked
        })),
        buttons: Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled
        })).filter(b => b.text.length > 0 && b.text.length < 30)
    })`);

    console.log('Opened declaration page info:\n', JSON.stringify(info, null, 2));
    await cdp.screenshot('current_declaration.png');
    cdp.close();
}

main().catch(console.error);
