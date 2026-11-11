import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const dataTypes = await cdp.eval(`(() => {
        // Collect all accordion / expansion items under Data types
        const headers = Array.from(document.querySelectorAll('mat-expansion-panel-header, [role="button"], h3, h4')).map(h => h.innerText.trim()).filter(t => t.length > 2 && t.length < 50);
        const checkboxes = Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"], label')).map(c => ({
            text: c.innerText.trim(),
            checked: c.getAttribute('aria-checked') || c.checked
        })).filter(c => c.text.length > 0 && c.text.length < 60);

        return {
            headers,
            checkboxCount: checkboxes.length,
            sampleCheckboxes: checkboxes.slice(0, 20)
        };
    })()`);

    console.log('Data types options:\n', JSON.stringify(dataTypes, null, 2));
    cdp.close();
}

main().catch(console.error);
