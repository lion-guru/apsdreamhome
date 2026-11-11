import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const buttons = await cdp.eval(`(() => {
        return Array.from(document.querySelectorAll('button')).map((b, i) => ({
            i,
            text: b.innerText.trim(),
            debugId: b.getAttribute('debug-id') || '',
            ariaLabel: b.getAttribute('aria-label') || '',
            visible: b.offsetParent !== null
        })).filter(b => b.visible);
    })()`);

    console.log('All visible buttons:', JSON.stringify(buttons, null, 2));
    cdp.close();
}

main().catch(console.error);
