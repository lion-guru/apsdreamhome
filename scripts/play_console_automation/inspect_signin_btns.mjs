import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const buttons = await cdp.eval(`(() => {
        return Array.from(document.querySelectorAll('button, a[role="button"]')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled,
            visible: b.offsetParent !== null
        })).filter(b => b.text.length > 0 && b.text.length < 50);
    })()`);
    console.log('Buttons:\n', JSON.stringify(buttons, null, 2));

    cdp.close();
}

main().catch(console.error);
