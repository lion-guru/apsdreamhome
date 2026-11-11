import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const internalTrackId = '4701293369268175450';
    const url = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/${internalTrackId}?tab=testers`;
    console.log('Navigating to Internal track testers:', url);
    await cdp.navigate(url);
    await cdp.wait(5000);

    const info = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => ({
            text: r.innerText.trim().replace(/\\n/g, ' '),
            buttons: Array.from(r.querySelectorAll('button, a')).map(b => ({
                text: b.innerText.trim(),
                ariaLabel: b.getAttribute('aria-label') || '',
                tag: b.tagName
            }))
        })).filter(r => r.text.includes('Tester') || r.text.includes('@') || r.text.includes('list'));

        const links = Array.from(document.querySelectorAll('a')).map(a => ({
            text: a.innerText.trim(),
            href: a.href
        })).filter(a => a.href.includes('testing') || a.text.includes('link') || a.text.includes('test'));

        return { rows, links, bodySnippet: body.slice(0, 500).replace(/\\n/g, ' ') };
    })()`);

    console.log('Internal track testers info:', JSON.stringify(info, null, 2));
    await cdp.screenshot('internal_track_testers_tab.png');
    cdp.close();
}

main().catch(console.error);
