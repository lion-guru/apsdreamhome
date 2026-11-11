import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const dump = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map((r, i) => {
            const p = r.closest('div[class*="question"], question, section, div') || r.parentElement;
            return {
                idx: i,
                label: r.innerText.trim(),
                checked: r.getAttribute('aria-checked')
            };
        });
        const inputs = Array.from(document.querySelectorAll('input, textarea')).map(i => ({
            type: i.type,
            val: i.value,
            placeholder: i.placeholder
        }));
        return {
            radios,
            inputs,
            textSnippet: text.substring(0, 1500)
        };
    })()`);

    console.log(JSON.stringify(dump, null, 2));
    cdp.close();
}

main().catch(console.error);
