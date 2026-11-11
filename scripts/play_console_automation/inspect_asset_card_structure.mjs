import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const structure = await cdp.eval(`(() => {
        // Find element containing screenshot_1_explore.png
        const all = Array.from(document.querySelectorAll('*'));
        const el = all.find(e => (e.innerText || '').includes('screenshot_1_explore.png') && e.children.length === 0);
        if (!el) return { error: 'screenshot_1_explore not found' };

        // Trace parents up 5 levels
        let curr = el;
        const hierarchy = [];
        for (let i = 0; i < 6 && curr; i++) {
            hierarchy.push({
                tag: curr.tagName,
                className: curr.className,
                role: curr.getAttribute('role'),
                tabIndex: curr.tabIndex,
                hasClick: typeof curr.onclick === 'function' || curr.getAttribute('role') === 'button' || curr.getAttribute('role') === 'checkbox',
                ariaLabel: curr.getAttribute('aria-label') || '',
                innerHTMLSnippet: curr.innerHTML.slice(0, 150)
            });
            curr = curr.parentElement;
        }

        return { hierarchy };
    })()`);

    console.log('Asset card structure:', JSON.stringify(structure, null, 2));
    cdp.close();
}

main().catch(console.error);
