import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container, aside, mat-sidenav');
        return {
            open: !!dialog,
            text: dialog ? dialog.innerText.slice(0, 500) : 'none'
        };
    })()`);

    console.log('Active drawer info:', info);
    cdp.close();
}

main().catch(console.error);
