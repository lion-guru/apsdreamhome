import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        if (!dialog) return { error: 'No dialog found' };
        
        const inputs = Array.from(dialog.querySelectorAll('input')).map(i => ({
            ariaLabel: i.getAttribute('aria-label'),
            value: i.value,
            placeholder: i.placeholder
        }));
        
        const buttons = Array.from(dialog.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            ariaLabel: b.getAttribute('aria-label'),
            disabled: b.disabled
        }));
        
        const items = Array.from(dialog.querySelectorAll('div, tr, li, ess-cell, [role="row"]'))
            .filter(el => el.innerText && el.innerText.includes('@'))
            .map(el => el.innerText.trim().replace(/\\n/g, ' '));

        return { inputs, buttons, items: [...new Set(items)] };
    })()`);

    console.log('Dialog Info:', JSON.stringify(info, null, 2));
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
