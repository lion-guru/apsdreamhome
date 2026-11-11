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

    console.log('1. Clicking Add assets on Feature graphic...');
    // Find Feature graphic section Add assets button
    const clickRes = await cdp.eval(`(() => {
        // Look for the Add assets button inside the Feature graphic container
        const headings = Array.from(document.querySelectorAll('h2, h3, [role="heading"], div'));
        for (const h of headings) {
            if ((h.innerText || '').trim() === 'Feature graphic') {
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
        // Fallback: second Add assets button
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Add assets');
        if (btns[1]) {
            btns[1].scrollIntoView({ block: 'center' });
            btns[1].click();
            return { clicked: true, method: 'byIndex' };
        }
        return { clicked: false };
    })()`);

    console.log('Clicked Add assets for feature graphic:', clickRes);
    await cdp.wait(2000);

    // 2. Find file input
    const root = await cdp.send('DOM.getDocument');
    const inputNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'simple-uploader input[type="file"], input[type="file"]'
    });

    if (!inputNode || !inputNode.nodeId) {
        throw new Error('File input not found in drawer');
    }

    const featurePath = 'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\feature_graphic_1024x500.png';
    console.log('Uploading feature graphic:', featurePath);
    await cdp.send('DOM.setFileInputFiles', {
        files: [featurePath],
        nodeId: inputNode.nodeId
    });

    console.log('Waiting 6s for processing...');
    await cdp.wait(6000);

    // 3. Click the Add button in drawer
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

    // 4. Verify deletes
    const verifyState = await cdp.eval(`(() => {
        const dels = Array.from(document.querySelectorAll('button[debug-id="delete-button"]')).map(b => b.getAttribute('aria-label'));
        return { deletes: dels };
    })()`);

    console.log('Verify state after adding feature graphic:', JSON.stringify(verifyState, null, 2));
    cdp.close();
}

main().catch(console.error);
