import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const allButtons = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button, a[role="button"], [role="button"], a[href]'));
        return btns.map((b, idx) => {
            const rect = b.getBoundingClientRect();
            // Get nearest heading or card text
            let p = b.parentElement;
            let context = '';
            for (let i = 0; i < 5 && p; i++) {
                if (p.innerText && p.innerText.length > context.length && p.innerText.length < 300) {
                    context = p.innerText.replace(/\\s+/g, ' ').trim();
                }
                p = p.parentElement;
            }
            return {
                idx,
                tag: b.tagName,
                text: b.innerText.trim(),
                href: b.getAttribute('href'),
                top: Math.round(rect.top),
                left: Math.round(rect.left),
                context: context.substring(0, 120)
            };
        }).filter(b => b.text.includes('declaration') || b.text.includes('Start') || b.text.includes('Manage'));
    })()`);

    console.log('Action buttons:\n', JSON.stringify(allButtons, null, 2));
    cdp.close();
}

main().catch(console.error);
