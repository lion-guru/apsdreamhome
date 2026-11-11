import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/data-privacy-security`);
    await cdp.wait(3000);

    const info = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        buttons: Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled,
            visible: b.offsetParent !== null
        })).filter(b => b.text.length > 0 && b.text.length < 30),
        bodySnippet: document.body.innerText.substring(0, 800)
    }))()`);

    console.log('Data safety state:\n', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
