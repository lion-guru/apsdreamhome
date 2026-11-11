import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const dump = await cdp.eval(`(() => {
        return {
            text: document.body.innerText,
            inputs: Array.from(document.querySelectorAll('input')).map(i => ({
                type: i.type,
                val: i.value,
                placeholder: i.placeholder,
                outer: i.outerHTML
            })),
            buttons: Array.from(document.querySelectorAll('button')).map(b => ({
                text: b.innerText.trim(),
                disabled: b.disabled
            }))
        };
    })()`);

    console.log(JSON.stringify(dump, null, 2));
    cdp.close();
}

main().catch(console.error);
