import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const links = await cdp.eval(`(() => {
        window.scrollTo(0, document.body.scrollHeight);
        const anchors = Array.from(document.querySelectorAll('a')).map(a => ({ text: a.innerText.trim(), href: a.href }));
        const copyBtns = Array.from(document.querySelectorAll('button[aria-label*="Copy link"], button')).filter(b => (b.innerText || '').includes('Copy link')).map(b => b.outerHTML);
        return {
            anchors: anchors.filter(a => a.href && (a.href.includes('join') || a.href.includes('test') || a.href.includes('play.google'))),
            copyBtns
        };
    })()`);
    console.log('Testing links on closed track:', JSON.stringify(links, null, 2));

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
