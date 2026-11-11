import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const els = Array.from(document.querySelectorAll('*')).filter(el => 
            el.children.length === 0 && (el.innerText || '').toLowerCase().includes('privacy policy')
        );
        return els.map(el => {
            let p = el.parentElement;
            let cardInfo = null;
            for (let i = 0; i < 8 && p; i++) {
                const btns = Array.from(p.querySelectorAll('button, a')).map(b => b.innerText.trim());
                if (btns.length > 0) {
                    cardInfo = { depth: i, tag: p.tagName, btns, pText: p.innerText.substring(0, 150) };
                    break;
                }
                p = p.parentElement;
            }
            return {
                text: el.innerText.trim(),
                tag: el.tagName,
                cardInfo
            };
        });
    })()`);

    console.log('Privacy policy occurrences:\n', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
