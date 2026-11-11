import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. First close side drawer if open
    await cdp.eval(`(() => {
        const closeBtn = document.querySelector('button[aria-label="Close side panel"]');
        if (closeBtn) closeBtn.click();
    })()`);
    await cdp.wait(1000);

    // 2. Click Remove scr5.png
    console.log('Clicking remove on scr5.png...');
    const delRes = await cdp.eval(`(() => {
        const btn = document.querySelector('button[aria-label="Remove scr5.png"]');
        if (btn) {
            btn.scrollIntoView({ block: 'center' });
            btn.click();
            return { clicked: true };
        }
        return { clicked: false };
    })()`);
    console.log('Delete result:', delRes);
    await cdp.wait(2000);

    // 3. Check remaining screenshots & Save button status
    const state = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        const saveDraft = document.querySelector('button[debug-id="save-draft-button"]');
        return {
            remainingDeletes: dels,
            saveDraftDisabled: saveDraft ? saveDraft.disabled : null
        };
    })()`);

    console.log('State after delete:', JSON.stringify(state, null, 2));
    cdp.close();
}

main().catch(console.error);
