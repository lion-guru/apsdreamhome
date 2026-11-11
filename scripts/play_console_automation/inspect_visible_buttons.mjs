import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const uploader = document.querySelector('simple-uploader, asset-uploader');
        const container = uploader ? uploader.closest('div[class*="container"], [role="dialog"], aside, mat-sidenav') : null;
        const allButtons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            debugId: b.getAttribute('debug-id') || '',
            ariaLabel: b.getAttribute('aria-label') || '',
            className: b.className,
            visible: b.offsetParent !== null
        })).filter(b => b.visible);

        return {
            containerClass: container ? container.className : 'null',
            visibleButtons: allButtons
        };
    })()`);

    console.log('Visible buttons in DOM:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
