import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Play Console tab via CDP...');
    const cdp = new CDPClient();
    await cdp.init();

    const curUrl = await cdp.eval('window.location.href');
    console.log('Current URL on release editor:', curUrl);

    // 1. Find file input via CDP DOM
    const aabPath = 'c:\\xampp\\htdocs\\apsdreamhome\\mobile\\apsdreamhome_app_v2\\build\\app\\outputs\\bundle\\release\\app-release.aab';
    const root = await cdp.send('DOM.getDocument');
    const inputNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'input[type="file"]'
    });

    if (!inputNode || !inputNode.nodeId) {
        console.error('File input not found in DOM');
        cdp.close();
        return;
    }

    console.log('Found file input node', inputNode.nodeId);
    console.log('Uploading Version 5 AAB bundle...');
    await cdp.send('DOM.setFileInputFiles', {
        files: [aabPath],
        nodeId: inputNode.nodeId
    });
    console.log('DOM.setFileInputFiles dispatched cleanly.');

    // 2. Monitor upload progress
    let completed = false;
    for (let i = 0; i < 60; i++) {
        await cdp.wait(3000);
        const st = await cdp.eval(`(() => {
            const body = document.body.innerText;
            const progress = document.querySelector('mat-progress-bar, [role="progressbar"]');
            const percent = progress?.getAttribute('aria-valuenow') || '';
            const hasBundle = body.includes('1.2.6') || body.includes('5 (1.2.6)') || body.includes('Version code 5');
            const rawErr = document.querySelector('.error-message, mat-error')?.innerText;
            return {
                uploading: body.includes('Uploading') || !!progress,
                percent,
                hasBundle,
                error: (rawErr || '').substring(0, 100)
            };
        })()`);

        console.log(`[${(i + 1) * 3}s] Upload status:`, JSON.stringify(st));
        if (st.hasBundle) {
            console.log('Bundle Version 5 detected successfully!');
            completed = true;
            break;
        }
        if (st.error && st.error.length > 5) {
            console.error('Upload Error:', st.error);
            break;
        }
    }

    if (completed) {
        await cdp.wait(4000);

        // 3. Set release name
        console.log('Setting release name...');
        await cdp.eval(`(() => {
            const inputs = Array.from(document.querySelectorAll('input'));
            const nameInput = inputs.find(i => i.placeholder?.includes('Release name') || i.getAttribute('aria-label')?.includes('Release name') || i.name?.includes('releaseName'));
            if (nameInput) {
                nameInput.value = '1.2.6 (5) - Luxury UI & Guest Mode';
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                nameInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        })()`);

        await cdp.screenshot('v5_uploaded_screenshot.png');
        console.log('Saved screenshot v5_uploaded_screenshot.png');

        // 4. Click Save as draft / Next
        const saveResult = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const saveBtn = btns.find(b => !b.disabled && (b.innerText.includes('Save') || b.innerText.includes('Next')));
            if (saveBtn) {
                saveBtn.scrollIntoView({ block: 'center' });
                saveBtn.click();
                return { clicked: true, text: saveBtn.innerText.trim() };
            }
            return { notFound: true };
        })()`);
        console.log('Save button result:', saveResult);
        await cdp.wait(4000);
    }

    cdp.close();
}

main().catch(console.error);
