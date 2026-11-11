import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Close drawer if open
    await cdp.eval(`(() => {
        const closeBtn = document.querySelector('button[aria-label="Close side panel"]');
        if (closeBtn) closeBtn.click();
    })()`);
    await cdp.wait(1000);

    // 2. Click Save as draft / Save
    console.log('Clicking Save as draft button...');
    const saveRes = await cdp.eval(`(() => {
        const saveDraft = document.querySelector('button[debug-id="save-draft-button"]') || 
                          Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save as draft' || (b.innerText || '').trim() === 'Save');
        if (saveDraft && !saveDraft.disabled) {
            saveDraft.scrollIntoView({ block: 'center' });
            saveDraft.click();
            return { clicked: true, text: saveDraft.innerText.trim() };
        }
        return { clicked: false, disabled: saveDraft ? saveDraft.disabled : null };
    })()`);

    console.log('Save result:', saveRes);
    await cdp.wait(4000);

    // 3. Check for any notification or errors
    const postSave = await cdp.eval(`(() => {
        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim()).filter(Boolean);
        const toasts = Array.from(document.querySelectorAll('snack-bar-container, .mat-snack-bar-container, .snackbar')).map(s => s.innerText.trim()).filter(Boolean);
        const saveDraft = document.querySelector('button[debug-id="save-draft-button"]');
        return {
            errors,
            toasts,
            saveDraftDisabled: saveDraft ? saveDraft.disabled : null
        };
    })()`);

    console.log('Post save status:', JSON.stringify(postSave, null, 2));
    cdp.close();
}

main().catch(console.error);
