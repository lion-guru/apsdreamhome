import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Remove remaining old assets
    const oldLabels = [
        'Remove scr4.png',
        'Remove scr3.png',
        'Remove scr2.png',
        'Remove play_store_feature_1024x500.png',
        'Remove play_store_icon_512.png'
    ];

    for (const label of oldLabels) {
        console.log(`Removing old asset: ${label}...`);
        const res = await cdp.eval(`(() => {
            const btn = document.querySelector('button[aria-label="${label}"]');
            if (btn) {
                btn.scrollIntoView({ block: 'center' });
                btn.click();
                return true;
            }
            return false;
        })()`);
        console.log(`Removed ${label}:`, res);
        await cdp.wait(1500);
    }

    const state = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        const saveDraft = document.querySelector('button[debug-id="save-draft-button"]');
        return {
            remainingDeletes: dels,
            saveDraftDisabled: saveDraft ? saveDraft.disabled : null
        };
    })()`);

    console.log('After deleting all old assets:', JSON.stringify(state, null, 2));
    await cdp.screenshot('all_old_assets_deleted.png');
    cdp.close();
}

main().catch(console.error);
