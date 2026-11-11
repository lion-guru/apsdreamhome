import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Check Publishing Overview
    console.log('Checking Publishing overview...');
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/publishing`);
    await cdp.wait(5000);

    const pubOverview = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled,
            visible: b.getBoundingClientRect().width > 0
        })).filter(b => b.visible);

        return {
            title: document.title,
            btns,
            snippet: text.substring(0, 1500)
        };
    })()`);
    console.log('Publishing Overview:\n', JSON.stringify(pubOverview, null, 2));
    await cdp.screenshot('publishing_overview_page.png');

    // 2. Check Releases overview
    console.log('Checking Releases overview...');
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/releases-overview`);
    await cdp.wait(5000);

    const relOverview = await cdp.eval(`(() => {
        return {
            title: document.title,
            snippet: document.body.innerText.substring(0, 1500)
        };
    })()`);
    console.log('Releases Overview:\n', JSON.stringify(relOverview, null, 2));
    await cdp.screenshot('releases_overview_page.png');

    // 3. Check Production Track
    console.log('Checking Production track...');
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/production`);
    await cdp.wait(5000);

    const prodTrack = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button, a')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled || false,
            visible: b.getBoundingClientRect().width > 0
        })).filter(b => b.visible);

        return {
            title: document.title,
            btns,
            snippet: document.body.innerText.substring(0, 1500)
        };
    })()`);
    console.log('Production Track:\n', JSON.stringify(prodTrack, null, 2));
    await cdp.screenshot('production_track_page.png');

    cdp.close();
}

main().catch(console.error);
