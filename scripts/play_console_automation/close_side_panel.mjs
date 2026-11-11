import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.eval(`(() => {
        const closeBtn = document.querySelector('button[aria-label="Close side panel"]');
        if (closeBtn) closeBtn.click();
    })()`);

    console.log('Closed side panel');
    await cdp.wait(1000);
    cdp.close();
}

main().catch(console.error);
