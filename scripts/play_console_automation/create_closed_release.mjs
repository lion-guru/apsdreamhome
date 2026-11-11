import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const trackId = '4700085989411864804';
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/${trackId}`);
    await cdp.wait(5000);

    // Click "Create new release" button
    const clickRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled && (b.innerText || '').trim() === 'Create new release';
        });

        if (btns.length > 0) {
            btns[0].click();
            return { clicked: true, text: btns[0].innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Click Create new release result:', clickRes);
    await cdp.wait(6000);

    const afterUrl = await cdp.eval(`window.location.href`);
    console.log('URL after clicking Create new release:', afterUrl);
    await cdp.screenshot('create_closed_release_opened.png');

    // Inspect what's on the release page
    const pageText = await cdp.eval(`document.body.innerText.substring(0, 1500)`);
    console.log('Page snippet:\n', pageText);

    cdp.close();
}

main().catch(console.error);
