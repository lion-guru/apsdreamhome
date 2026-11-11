import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const hierarchy = await cdp.eval(`(() => {
        const input = document.querySelector('input[type="email"], input[aria-label="Add email addresses"]');
        if (!input) return { error: 'No input' };

        const parents = [];
        let p = input;
        while (p && parents.length < 8) {
            parents.push({
                tag: p.tagName,
                className: p.className,
                id: p.id,
                name: p.getAttribute('name'),
                debugId: p.getAttribute('debug-id')
            });
            p = p.parentElement;
        }

        return { parents };
    })()`);

    console.log('Hierarchy:', JSON.stringify(hierarchy, null, 2));
    cdp.close();
}

main().catch(console.error);
