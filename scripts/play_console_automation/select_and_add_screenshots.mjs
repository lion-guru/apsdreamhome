import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const targets = [
        'screenshot_1_explore.png',
        'screenshot_2_maps.png',
        'screenshot_3_emi.png',
        'screenshot_4_legal.png',
        'screenshot_5_associate.png'
    ];

    console.log('Selecting 5 screenshots via [debug-id="select-button"]...');
    const selectResults = await cdp.eval(`(() => {
        const targets = [
            'screenshot_1_explore.png',
            'screenshot_2_maps.png',
            'screenshot_3_emi.png',
            'screenshot_4_legal.png',
            'screenshot_5_associate.png'
        ];

        const rows = Array.from(document.querySelectorAll('asset-list-row'));
        const log = [];

        for (const name of targets) {
            const row = rows.find(r => (r.innerText || '').includes(name));
            if (!row) {
                log.push({ name, status: 'row_not_found' });
                continue;
            }

            const selectBtn = row.querySelector('[debug-id="select-button"], .select-button');
            const isChecked = (row.innerText || '').includes('Selected asset');

            if (selectBtn && !isChecked) {
                selectBtn.scrollIntoView({ block: 'center' });
                selectBtn.click();
                log.push({ name, status: 'clicked_select_btn' });
            } else if (isChecked) {
                log.push({ name, status: 'already_selected' });
            } else {
                log.push({ name, status: 'select_btn_not_found' });
            }
        }

        return log;
    })()`);

    console.log('Select results:', JSON.stringify(selectResults, null, 2));
    await cdp.wait(2000);

    // Check bottom buttons now
    const drawerState = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: b.getAttribute('aria-label') || '',
            debugId: b.getAttribute('debug-id') || '',
            disabled: b.disabled
        })).filter(b => b.text.includes('Add') || b.text.includes('Save') || b.text.includes('photo'));

        return btns;
    })()`);

    console.log('Available action buttons:', JSON.stringify(drawerState, null, 2));

    // Click the Add button
    console.log('Clicking Add button in drawer...');
    const addResult = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const addBtn = btns.find(b => {
            const t = b.innerText.trim();
            return (t.includes('Add') || t.includes('Use') || t.includes('Insert')) && !t.includes('Add assets') && !t.includes('Drive');
        });
        if (addBtn) {
            addBtn.scrollIntoView({ block: 'center' });
            addBtn.click();
            return { success: true, text: addBtn.innerText.trim() };
        }
        return { success: false };
    })()`);

    console.log('Add Result:', JSON.stringify(addResult, null, 2));
    await cdp.wait(3000);

    // Verify all attached screenshots on main page
    const finalAttached = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        const saveDraft = document.querySelector('button[debug-id="save-draft-button"]');
        return {
            attachedAssets: dels,
            saveDraftDisabled: saveDraft ? saveDraft.disabled : null
        };
    })()`);

    console.log('Final Attached Assets on Store Listing:', JSON.stringify(finalAttached, null, 2));
    cdp.close();
}

main().catch(console.error);
