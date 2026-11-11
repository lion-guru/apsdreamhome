import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { error: 'No dialog' };

        const fileInput = dialog.querySelector('input[type="file"]');
        const csvBtn = Array.from(dialog.querySelectorAll('button')).find(b => (b.innerText || '').includes('CSV'));

        return {
            hasFileInput: !!fileInput,
            fileInputAccept: fileInput ? fileInput.accept : null,
            csvBtnText: csvBtn ? csvBtn.innerText.trim() : null
        };
    })()`);

    console.log('CSV info:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
