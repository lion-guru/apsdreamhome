import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const sections = await cdp.eval(`(() => {
        const result = [];
        const headings = Array.from(document.querySelectorAll('h2, h3, [role="heading"], div[debug-id]'));
        for (const h of headings) {
            const text = (h.innerText || '').trim();
            if (text.includes('App icon') || text.includes('Feature graphic') || text.includes('Phone') || text.includes('visual assets')) {
                // Find parent container
                let parent = h.parentElement;
                while (parent && parent.querySelectorAll('button').length < 2 && parent !== document.body) {
                    parent = parent.parentElement;
                }
                const buttons = Array.from(parent ? parent.querySelectorAll('button, [role="button"]') : []).map(b => ({
                    text: b.innerText.trim(),
                    ariaLabel: b.getAttribute('aria-label') || '',
                    debugId: b.getAttribute('debug-id') || '',
                    classes: b.className
                }));
                const fileInputs = Array.from(parent ? parent.querySelectorAll('input[type="file"]') : []).map(i => ({
                    accept: i.accept,
                    multiple: i.multiple
                }));
                result.push({
                    heading: text.split('\\n')[0],
                    buttons,
                    fileInputs
                });
            }
        }
        return result;
    })()`);

    console.log('Visual sections:', JSON.stringify(sections, null, 2));
    cdp.close();
}

main().catch(console.error);
