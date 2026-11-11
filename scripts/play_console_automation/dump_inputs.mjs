import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const inputs = await cdp.eval(`(() => {
        return Array.from(document.querySelectorAll('input')).map(i => ({
            tag: i.tagName,
            type: i.type,
            val: i.value,
            id: i.id,
            name: i.name,
            ariaLabel: i.getAttribute('aria-label'),
            outer: i.outerHTML
        }));
    })()`);

    console.log(JSON.stringify(inputs, null, 2));
    cdp.close();
}

main().catch(console.error);
