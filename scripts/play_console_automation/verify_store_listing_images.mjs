import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/store-listings/default/edit`);
    await cdp.wait(4000);

    const images = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        return dels;
    })()`);

    console.log('Verified store listing images:', JSON.stringify(images, null, 2));
    cdp.close();
}

main().catch(console.error);
