import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Find all 'Add assets' buttons
    const res = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Add assets');
        return btns.map((b, idx) => {
            const section = b.closest('section, div[debug-id], mat-expansion-panel') || b.parentElement;
            return {
                idx,
                text: b.innerText.trim(),
                sectionSnippet: section ? section.innerText.slice(0, 100) : ''
            };
        });
    })()`);

    console.log('Add assets buttons:', res);

    cdp.close();
}

main().catch(console.error);
