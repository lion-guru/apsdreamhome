import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const trackUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804`;
    console.log('Navigating to Closed testing Alpha track:', trackUrl);
    await cdp.navigate(trackUrl);
    await cdp.wait(5000);

    const trackInfo = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button, a')).map(b => ({
            text: b.innerText.trim(),
            tag: b.tagName,
            disabled: b.disabled || false,
            visible: b.getBoundingClientRect().width > 0
        })).filter(b => b.visible);

        return {
            title: document.title,
            snippet: text.substring(0, 1500),
            btns
        };
    })()`);

    console.log('Alpha Track Info:\n', JSON.stringify(trackInfo, null, 2));
    await cdp.screenshot('closed_testing_alpha_track.png');

    cdp.close();
}

main().catch(console.error);
