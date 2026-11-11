import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const fi = document.querySelector('input[type="file"]');
        const uploadArea = document.querySelector('[class*="upload"], drop-target, mat-card');
        return {
            hasFileInput: !!fi,
            fileInputOuter: fi?.outerHTML,
            bodySnippet: document.body.innerText.substring(0, 1000)
        };
    })()`);

    console.log(JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
