import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4701293369268175450?tab=testers`);
    await cdp.wait(4000);

    const info = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const copyBtn = document.querySelector('button[aria-label*="Copy link"]');
        const link = copyBtn ? copyBtn.getAttribute('aria-label') : '';
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => r.innerText.trim().replace(/\\n/g, ' ')).filter(r => r.includes('Internal Testers'));

        return {
            rows,
            link,
            bodySnippet: body.split('\\n').filter(l => l.includes('Internal') || l.includes('Tester') || l.includes('link')).join(' | ')
        };
    })()`);

    console.log('Final Verification:', JSON.stringify(info, null, 2));
    await cdp.screenshot('track_testers_final_verified.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
