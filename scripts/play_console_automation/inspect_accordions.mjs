import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const sections = await cdp.eval(`(() => {
        // Look for section headers or panels
        const headers = Array.from(document.querySelectorAll('h2, h3, h4, mat-expansion-panel-header, [role="button"], div[class*="header"]')).map(el => ({
            tag: el.tagName,
            text: el.innerText.trim(),
            role: el.getAttribute('role'),
            classes: el.className
        })).filter(h => h.text.includes('User Content') || h.text.includes('Online Content') || h.text.includes('Promotion') || h.text.includes('Miscellaneous'));

        // Look for all questions on the page
        const allText = document.body.innerText;
        return {
            headers,
            allRadios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
                text: r.innerText.trim(),
                checked: r.getAttribute('aria-checked')
            }))
        };
    })()`);

    console.log(JSON.stringify(sections, null, 2));
    cdp.close();
}

main().catch(console.error);
