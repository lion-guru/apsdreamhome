import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const formHtml = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { error: 'No dialog' };

        const form = dialog.querySelector('form') || dialog;
        const inputs = Array.from(dialog.querySelectorAll('mat-form-field, .mat-mdc-form-field')).map(f => ({
            label: f.querySelector('mat-label, label')?.innerText || '',
            htmlSnippet: f.innerHTML.slice(0, 300)
        }));

        return { inputs };
    })()`);

    console.log('Form Fields in Dialog:', JSON.stringify(formHtml, null, 2));
    cdp.close();
}

main().catch(console.error);
