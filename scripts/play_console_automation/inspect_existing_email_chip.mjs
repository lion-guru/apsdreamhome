import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { error: 'No dialog' };

        // Find element containing techguruabhay@gmail.com
        const el = Array.from(dialog.querySelectorAll('*')).find(e => (e.innerText || '').includes('techguruabhay@gmail.com') && e.children.length === 0);
        if (!el) return { error: 'techguruabhay not found' };

        let curr = el;
        const chain = [];
        while (curr && curr !== dialog && chain.length < 6) {
            chain.push({
                tag: curr.tagName,
                className: curr.className,
                role: curr.getAttribute('role'),
                htmlSnippet: curr.outerHTML.slice(0, 150)
            });
            curr = curr.parentElement;
        }

        return { chain };
    })()`);

    console.log('Existing email structure:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
