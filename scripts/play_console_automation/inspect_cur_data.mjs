import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        drawerTitle: document.querySelector('mat-drawer, [role="dialog"], .drawer')?.innerText.substring(0, 100),
        buttons: Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled,
            visible: b.offsetParent !== null
        })).filter(b => b.text.length > 0 && b.text.length < 30)
    }))()`);

    console.log(JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
