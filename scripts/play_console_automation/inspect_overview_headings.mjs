import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const titles = await cdp.eval(`(() => {
        const headings = Array.from(document.querySelectorAll('h3, h2, [role="heading"]')).map(h => h.innerText.trim()).filter(t => t.length > 2 && t.length < 50);
        return headings;
    })()`);

    console.log('Current headings on overview:\n', JSON.stringify(titles, null, 2));
    cdp.close();
}

main().catch(console.error);
