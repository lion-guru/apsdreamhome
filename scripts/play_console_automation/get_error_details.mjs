import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const errorDetails = await cdp.eval(`(() => {
        const els = Array.from(document.querySelectorAll('[aria-invalid="true"], .error, [role="alert"]'));
        return els.map(el => {
            const p = el.closest('div, section') || el;
            return {
                tag: el.tagName,
                text: el.innerText.trim(),
                parentText: p.innerText.substring(0, 100)
            };
        });
    })()`);

    console.log(JSON.stringify(errorDetails, null, 2));
    cdp.close();
}

main().catch(console.error);
