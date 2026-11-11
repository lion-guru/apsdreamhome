import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const result = await cdp.eval(`(() => {
        const all = Array.from(document.body.querySelectorAll('*'));
        const catEls = all.filter(el => (el.innerText || '').includes('Category *') && el.children.length === 0);
        
        // Find the interactive control next to "Category *"
        const dropdownControls = all.filter(el => {
            const txt = (el.innerText || '').trim();
            const cls = el.getAttribute('class') || '';
            const r = el.getBoundingClientRect();
            return (txt === 'Not selected' || cls.includes('select') || el.getAttribute('role') === 'combobox') && r.width > 0 && r.height > 0;
        }).map(el => {
            const r = el.getBoundingClientRect();
            return {
                tag: el.tagName,
                className: el.getAttribute('class') || '',
                text: el.innerText.trim(),
                role: el.getAttribute('role'),
                rect: {
                    x: Math.round(r.x),
                    y: Math.round(r.y),
                    w: Math.round(r.width),
                    h: Math.round(r.height)
                }
            };
        });

        return {
            catLabels: catEls.map(e => ({ tag: e.tagName, text: e.innerText })),
            dropdownControls
        };
    })()`);

    console.log('Result:', JSON.stringify(result, null, 2));
    cdp.close();
}

main().catch(console.error);
