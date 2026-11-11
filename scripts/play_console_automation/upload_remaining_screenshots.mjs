import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const remaining = [
        { name: 'screenshot_3_emi.png', path: 'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_3_emi.png' },
        { name: 'screenshot_4_legal.png', path: 'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_4_legal.png' },
        { name: 'screenshot_5_associate.png', path: 'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_5_associate.png' }
    ];

    for (const item of remaining) {
        console.log(`Uploading ${item.name}...`);
        const root = await cdp.send('DOM.getDocument');
        const inputNode = await cdp.send('DOM.querySelector', {
            nodeId: root.root.nodeId,
            selector: 'simple-uploader input[type="file"], input[type="file"]'
        });

        if (!inputNode || !inputNode.nodeId) {
            throw new Error(`File input not found for ${item.name}`);
        }

        await cdp.send('DOM.setFileInputFiles', {
            files: [item.path],
            nodeId: inputNode.nodeId
        });

        console.log(`Dispatched ${item.name}. Waiting 6s for processing...`);
        await cdp.wait(6000);
    }

    // Now let's see what items are in the drawer
    const targetNames = [
        'screenshot_1_explore.png',
        'screenshot_2_maps.png',
        'screenshot_3_emi.png',
        'screenshot_4_legal.png',
        'screenshot_5_associate.png'
    ];

    console.log('Selecting all 5 target screenshots in library...');
    const selectRes = await cdp.eval(`(() => {
        const targets = [
            'screenshot_1_explore.png',
            'screenshot_2_maps.png',
            'screenshot_3_emi.png',
            'screenshot_4_legal.png',
            'screenshot_5_associate.png'
        ];

        const cards = Array.from(document.querySelectorAll('div[class*="asset-row"], div[class*="asset-item"], div[class*="asset-thumbnail"], mat-list-item, div[role="listitem"], .asset-name'));
        const clicked = [];

        for (const t of targets) {
            // Find an element containing this name
            const el = Array.from(document.querySelectorAll('*')).find(e => (e.innerText || '').trim() === t || (e.innerText || '').includes(t));
            if (el) {
                // Find clickable parent or tile
                let tile = el;
                while (tile && !tile.className.includes('asset') && tile !== document.body) {
                    tile = tile.parentElement;
                }
                const clickTarget = tile || el;
                clickTarget.scrollIntoView({ block: 'center' });
                clickTarget.click();
                clicked.push({ name: t, clicked: true });
            } else {
                clicked.push({ name: t, notFound: true });
            }
        }

        return clicked;
    })()`);

    console.log('Select results:', JSON.stringify(selectRes, null, 2));
    await cdp.wait(2000);

    // Look for the Add button
    console.log('Clicking Add in drawer...');
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
        return { success: false, availableButtons: btns.map(b => b.innerText.trim()).filter(Boolean) };
    })()`);

    console.log('Add Result:', JSON.stringify(addResult, null, 2));
    await cdp.wait(3000);

    // Verify delete buttons
    const verifyState = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        return { deletes: dels };
    })()`);

    console.log('Current attached deletes on page:', JSON.stringify(verifyState, null, 2));
    cdp.close();
}

main().catch(console.error);
