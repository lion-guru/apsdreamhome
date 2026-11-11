import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const els = Array.from(document.querySelectorAll('*')).filter(el => (el.innerText || '').includes('AI image customization') && el.children.length === 0);
        return els.map(e => {
            let p = e;
            const chain = [];
            while (p && chain.length < 5) {
                chain.push({ tag: p.tagName, className: p.className, id: p.id });
                p = p.parentElement;
            }
            return { text: e.innerText, chain };
        });
    })()`);

    console.log('AI customization element:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
