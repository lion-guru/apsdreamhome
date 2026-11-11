import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('1. Clicking Add assets on App icon (first Add assets button)...');
    const clicked = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Add assets');
        if (btns[0]) {
            btns[0].scrollIntoView({ block: 'center' });
            btns[0].click();
            return true;
        }
        return false;
    })()`);
    console.log('Clicked Add assets for icon:', clicked);
    await cdp.wait(2000);

    // 2. Find file input in drawer
    const root = await cdp.send('DOM.getDocument');
    const inputNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'simple-uploader input[type="file"], input[type="file"]'
    });

    console.log('Found inputNode:', inputNode);
    if (!inputNode || !inputNode.nodeId) {
        throw new Error('File input not found');
    }

    const iconPath = 'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\app_icon_512x512.png';
    console.log('Uploading app icon:', iconPath);
    await cdp.send('DOM.setFileInputFiles', {
        files: [iconPath],
        nodeId: inputNode.nodeId
    });

    console.log('Uploaded. Waiting 6 seconds for processing...');
    await cdp.wait(6000);

    // 3. Inspect drawer buttons & selected item
    const drawerState = await cdp.eval(`(() => {
        const allButtons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            debugId: b.getAttribute('debug-id') || '',
            ariaLabel: b.getAttribute('aria-label') || '',
            disabled: b.disabled
        }));

        const addBtn = allButtons.find(b => b.text.includes('Add') && !b.text.includes('Add assets') && !b.text.includes('Drive'));

        return {
            addBtn,
            drawerButtons: allButtons.filter(b => b.text.length < 30)
        };
    })()`);

    console.log('Drawer state:', JSON.stringify(drawerState, null, 2));

    // 4. Click the Add / Insert button in drawer
    console.log('Clicking Add button in drawer...');
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

    // 5. Check if icon is now set
    const verifyState = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        return { deletes: dels };
    })()`);

    console.log('Verify state after adding icon:', JSON.stringify(verifyState, null, 2));
    cdp.close();
}

main().catch(console.error);
