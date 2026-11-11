import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Navigating to closed-testing...');
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/closed-testing`);
    await cdp.wait(5000);

    const closedPage = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button, a')).map(b => ({
            text: b.innerText.trim(),
            tag: b.tagName,
            href: b.getAttribute('href'),
            disabled: b.disabled || false,
            visible: b.getBoundingClientRect().width > 0
        })).filter(b => b.visible);

        return {
            title: document.title,
            snippet: text.substring(0, 1500),
            btns
        };
    })()`);

    console.log('Closed testing page:\n', JSON.stringify(closedPage, null, 2));
    await cdp.screenshot('closed_testing_page.png');

    cdp.close();
}

main().catch(console.error);
