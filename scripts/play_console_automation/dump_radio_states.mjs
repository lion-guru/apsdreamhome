import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const dump = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map((r, i) => {
            const p = r.closest('div[class*="question"], question, section, div') || r.parentElement;
            return {
                idx: i,
                label: (r.innerText || '').trim(),
                checked: r.getAttribute('aria-checked'),
                context: (p?.innerText || '').substring(0, 100).replace(/\\s+/g, ' ')
            };
        });
        return {
            totalRadios: radios.length,
            radios
        };
    })()`);

    console.log(JSON.stringify(dump, null, 2));
    cdp.close();
}

main().catch(console.error);
