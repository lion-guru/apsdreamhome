import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const title = document.title;
        const url = window.location.href;
        const headings = Array.from(document.querySelectorAll('h1, h2, h3')).map(h => h.innerText.trim());
        const buttons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled
        })).filter(b => b.text.length > 0 && b.text.length < 50);
        const alerts = Array.from(document.querySelectorAll('[role="alert"], .callout, [class*="banner"], [class*="warning"], [class*="status"]')).map(a => a.innerText.trim());
        const bodySnippet = document.body.innerText.slice(0, 1500).replace(/\\n+/g, ' \\n ');
        return { title, url, headings, buttons, alerts, bodySnippet };
    })()`);

    console.log('Play Console Info:', JSON.stringify(info, null, 2));
    await cdp.screenshot('publishing_overview_state.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
