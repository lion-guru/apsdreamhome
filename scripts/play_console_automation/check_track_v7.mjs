import { CDPClient } from './cdp_helper.mjs';

async function checkTrack() {
    try {
        const cdp = new CDPClient();
        await cdp.init();

        console.log('Navigating to internal testing track releases tab...');
        await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4701293369268175450?tab=releases`);
        await cdp.wait(6000);

        const pageData = await cdp.eval(`(() => {
            return {
                title: document.title,
                url: window.location.href,
                bodySnippet: document.body.innerText.substring(0, 1500)
            };
        })()`);

        console.log('Track page data:\n', pageData.bodySnippet);
        await cdp.screenshot('v7_track_status.png');
        console.log('Saved v7_track_status.png');

        cdp.close();
    } catch (e) {
        console.error('Error checking track:', e);
    }
}

checkTrack();
