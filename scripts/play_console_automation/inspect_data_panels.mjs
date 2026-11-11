import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const panels = await cdp.eval(`(() => {
        // Collect all expansion panels or section headers under the main content
        const main = document.querySelector('main, [role="main"]') || document.body;
        const headers = Array.from(main.querySelectorAll('h2, h3, h4, .expansion-header, [role="button"]')).map(el => ({
            tag: el.tagName,
            text: el.innerText.trim(),
            role: el.getAttribute('role')
        })).filter(h => h.text.length > 2 && h.text.length < 40);

        return headers;
    })()`);

    console.log('Panels found:\n', JSON.stringify(panels, null, 2));
    cdp.close();
}

main().catch(console.error);
