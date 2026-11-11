import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { error: 'No dialog' };

        const input = dialog.querySelector('input[type="email"], input[aria-label="Add email addresses"]');
        const chipGrid = dialog.querySelector('mat-chip-grid, mat-chip-list, .mat-mdc-chip-grid');
        const chips = Array.from(dialog.querySelectorAll('mat-chip-row, mat-chip, .mat-mdc-chip')).map(c => c.innerText.trim());

        return {
            inputValue: input ? input.value : null,
            inputTag: input ? input.tagName : null,
            chipGridClass: chipGrid ? chipGrid.className : null,
            chips
        };
    })()`);

    console.log('Chip input state:', JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
