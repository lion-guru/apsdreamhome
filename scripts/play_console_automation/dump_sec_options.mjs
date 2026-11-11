import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const formDump = await cdp.eval(`(() => {
        const items = [];
        document.querySelectorAll('material-radio, material-checkbox, [role="radio"], [role="checkbox"], label').forEach(el => {
            const text = (el.innerText || '').trim();
            if (text.length > 0 && text.length < 150) {
                items.push({
                    text,
                    type: el.getAttribute('role') || el.tagName,
                    checked: el.getAttribute('aria-checked') || el.checked
                });
            }
        });
        return items;
    })()`);

    console.log('Form options:\n', JSON.stringify(formDump, null, 2));
    cdp.close();
}

main().catch(console.error);
