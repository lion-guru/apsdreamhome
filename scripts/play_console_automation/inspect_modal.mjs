import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        return {
            url: window.location.href,
            modals: Array.from(document.querySelectorAll('mat-dialog-container, dialog, [role="dialog"]')).map(d => d.innerText),
            bodySnippet: document.body.innerText.substring(0, 1000)
        };
    })()`);

    console.log(JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
