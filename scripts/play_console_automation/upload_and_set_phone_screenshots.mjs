import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Close side drawer if open
    await cdp.eval(`(() => {
        const closeBtn = document.querySelector('button[aria-label="Close side panel"]');
        if (closeBtn) closeBtn.click();
    })()`);
    await cdp.wait(1000);

    // 2. Click Add assets on Phone screenshots
    console.log('1. Clicking Add assets on Phone screenshots...');
    const clickRes = await cdp.eval(`(() => {
        const headings = Array.from(document.querySelectorAll('h2, h3, [role="heading"], div'));
        for (const h of headings) {
            if ((h.innerText || '').trim().includes('Phone screenshots')) {
                let parent = h.parentElement;
                while (parent && !parent.querySelector('button') && parent !== document.body) {
                    parent = parent.parentElement;
                }
                const btn = parent ? Array.from(parent.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Add assets') : null;
                if (btn) {
                    btn.scrollIntoView({ block: 'center' });
                    btn.click();
                    return { clicked: true, method: 'byHeading' };
                }
            }
        }
        // Fallback: find any Add assets button in Phone section
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Add assets');
        if (btns.length > 0) {
            const last = btns[btns.length - 1];
            last.scrollIntoView({ block: 'center' });
            last.click();
            return { clicked: true, method: 'byLast' };
        }
        return { clicked: false };
    })()`);

    console.log('Clicked Add assets for phone screenshots:', clickRes);
    await cdp.wait(2000);

    // 3. Find file input in drawer
    const root = await cdp.send('DOM.getDocument');
    const inputNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'simple-uploader input[type="file"], input[type="file"]'
    });

    if (!inputNode || !inputNode.nodeId) {
        throw new Error('File input not found');
    }

    const screenshots = [
        'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_2_maps.png',
        'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_3_emi.png',
        'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_4_legal.png',
        'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_5_associate.png'
    ];

    console.log('Uploading remaining 4 screenshots...');
    await cdp.send('DOM.setFileInputFiles', {
        files: screenshots,
        nodeId: inputNode.nodeId
    });

    console.log('Dispatched. Waiting 15s for all 4 uploads to finish...');
    await cdp.wait(15000);

    // 4. Ensure all 5 target screenshots are selected in the library
    console.log('Ensuring all 5 target screenshots are selected...');
    const selectRes = await cdp.eval(`(() => {
        const targetNames = [
            'screenshot_1_explore.png',
            'screenshot_2_maps.png',
            'screenshot_3_emi.png',
            'screenshot_4_legal.png',
            'screenshot_5_associate.png'
        ];

        const results = [];
        const items = Array.from(document.querySelectorAll('div[role="listitem"], li, mat-list-item, div[class*="asset"]'));
        for (const name of targetNames) {
            const item = items.find(el => (el.innerText || '').includes(name));
            if (item) {
                const isSelected = (item.innerText || '').includes('Selected asset') || item.classList.contains('selected') || item.getAttribute('aria-selected') === 'true';
                if (!isSelected) {
                    item.scrollIntoView({ block: 'center' });
                    item.click();
                    results.push({ name, clicked: true, wasSelected: false });
                } else {
                    results.push({ name, clicked: false, wasSelected: true });
                }
            } else {
                results.push({ name, notFound: true });
            }
        }
        return results;
    })()`);

    console.log('Select results:', JSON.stringify(selectRes, null, 2));
    await cdp.wait(2000);

    // 5. Click Add button in drawer
    console.log('Clicking Add in drawer...');
    const clickAddRes = await cdp.eval(`(() => {
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

    console.log('Click Add result:', clickAddRes);
    await cdp.wait(3000);

    // 6. Verify delete buttons on page
    const verifyState = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        return { deletes: dels };
    })()`);

    console.log('Verify state after adding screenshots:', JSON.stringify(verifyState, null, 2));
    cdp.close();
}

main().catch(console.error);
