import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const buttons = await cdp.eval(`(() => {
        return Array.from(document.querySelectorAll('button, a[role="button"], a[href]')).map(b => ({
            tag: b.tagName,
            text: b.innerText.trim(),
            href: b.getAttribute('href'),
            role: b.getAttribute('role')
        })).filter(b => b.text.length > 0 && b.text.length < 50);
    })()`);

    console.log('Buttons on releases page:\n', JSON.stringify(buttons, null, 2));
    cdp.close();
}

main().catch(console.error);
