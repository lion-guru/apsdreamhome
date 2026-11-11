import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const dump = await cdp.eval(`(() => {
        return {
            url: window.location.href,
            bodyText: document.body.innerText,
            buttons: Array.from(document.querySelectorAll('button')).map(b => ({
                text: b.innerText.trim(),
                disabled: b.disabled,
                ariaDisabled: b.getAttribute('aria-disabled'),
                outerHTML: b.outerHTML
            })),
            inputs: Array.from(document.querySelectorAll('input')).map(i => ({
                outerHTML: i.outerHTML,
                value: i.value
            }))
        };
    })()`);

    console.log(JSON.stringify(dump, null, 2));
    cdp.close();
}

main().catch(console.error);
