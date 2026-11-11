import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const trackId = '4701293369268175450'; // Internal testing track
    const url = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/${trackId}?tab=testers`;
    console.log('Navigating to Internal Testing testers tab:', url);
    await cdp.navigate(url);
    await cdp.wait(5000);

    const info = await cdp.eval(`(() => {
        const text = document.body.innerText;
        // Look for opt-in URL: https://play.google.com/apps/internaltest/...
        const match = text.match(/https:\/\/play\.google\.com\/apps\/internaltest\/[^\s\n]+/);
        const joinLink = match ? match[0] : null;

        // Check tester lists
        const checkboxes = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]')).map(cb => ({
            text: (cb.parentElement ? cb.parentElement.innerText : '').trim().replace(/\\n/g, ' '),
            checked: cb.getAttribute('aria-checked') === 'true' || cb.checked
        }));

        const btns = Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(t => t.length > 0);

        return {
            joinLink,
            checkboxes,
            btns,
            snippet: text.substring(0, 1500)
        };
    })()`);

    console.log('Internal Testing Info:\n', JSON.stringify(info, null, 2));
    await cdp.screenshot('internal_testing_testers_tab.png');

    cdp.close();
}

main().catch(console.error);
