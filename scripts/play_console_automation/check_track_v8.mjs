import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();
    await cdp.wait(2000);
    const data = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const idx = body.indexOf('Releases');
        const snippet = idx !== -1 ? body.substring(idx, idx + 1000) : body.substring(0, 1000);
        return {
            title: document.title,
            url: window.location.href,
            snippet
        };
    })()`);
    console.log(JSON.stringify(data, null, 2));
    await cdp.screenshot('v8_published_track_releases.png');
    console.log('Saved v8_published_track_releases.png');
    cdp.close();
}

main().catch(console.error);
