import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const sections = await cdp.eval(`(() => {
        const result = [];
        const headings = Array.from(document.querySelectorAll('h2, h3, [role="heading"]'));
        for (const h of headings) {
            const text = (h.innerText || '').trim();
            if (text.includes('App icon') || text.includes('Feature graphic') || text.includes('Phone screenshots')) {
                let parent = h.parentElement;
                while (parent && parent.querySelectorAll('button').length < 1 && parent !== document.body) {
                    parent = parent.parentElement;
                }
                const buttons = Array.from(parent ? parent.querySelectorAll('button') : []).map(b => ({
                    text: b.innerText.trim(),
                    ariaLabel: b.getAttribute('aria-label') || '',
                    debugId: b.getAttribute('debug-id') || ''
                }));
                const fileInputs = Array.from(parent ? parent.querySelectorAll('input[type="file"]') : []).map(i => ({
                    accept: i.accept
                }));
                result.push({
                    heading: text,
                    buttons,
                    fileInputs,
                    textSnippet: parent ? parent.innerText.slice(0, 150) : ''
                });
            }
        }
        return result;
    })()`);

    console.log('Sections state:', JSON.stringify(sections, null, 2));
    cdp.close();
}

main().catch(console.error);
