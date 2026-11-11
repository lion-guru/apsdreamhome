import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/privacy-policy`);
    await cdp.wait(3000);

    const state = await cdp.eval(`({
        inputVal: document.querySelector('input')?.value,
        saveBtnDisabled: document.querySelector('button[aria-label="Save"], button:not([disabled])')?.innerText
    })`);
    console.log('Privacy Policy page state:', state);
    cdp.close();
}

main().catch(console.error);
