import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click "Create new release"
    const clickBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const createBtn = btns.find(b => b.innerText.trim() === 'Create new release');
        if (createBtn) {
            createBtn.scrollIntoView({ block: 'center' });
            createBtn.click();
            return { clicked: true };
        }
        return { error: 'Create new release button not found' };
    })()`);
    console.log('Clicked Create new release:', clickBtn);
    await cdp.wait(4000);

    const curUrl = await cdp.eval('window.location.href');
    console.log('Current URL on release editor:', curUrl);
    await cdp.screenshot('v4_editor_opened.png');

    // 2. Find file input via CDP DOM
    const aabPath = 'c:\\xampp\\htdocs\\apsdreamhome\\mobile\\apsdreamhome_app_v2\\android\\app\\build\\outputs\\bundle\\release\\app-release.aab';
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

    console.log('Uploading Version 4 AAB to node', inputNode.nodeId);
    await cdp.send('DOM.setFileInputFiles', {
        files: [aabPath],
        nodeId: inputNode.nodeId
    });
    console.log('DOM.setFileInputFiles dispatched cleanly.');

    // 3. Monitor upload
    let completed = false;
    for (let i = 0; i < 40; i++) {
        await cdp.wait(3000);
        const st = await cdp.eval(`(() => {
            const body = document.body.innerText;
            const progress = document.querySelector('mat-progress-bar, [role="progressbar"]');
            const percent = progress?.getAttribute('aria-valuenow') || '';
            const hasBundle = body.includes('1.2.5') || body.includes('4 (1.2.5)') || body.includes('Version code 4');
            const error = document.querySelector('.error-message, mat-error, [role="alert"]')?.innerText;
            return {
                uploading: body.includes('Uploading') || !!progress,
                percent,
                hasBundle,
                error: (error || '').substring(0, 100)
            };
        })()`);

        console.log(`[${(i + 1) * 3}s] Upload status:`, st);
        if (st.hasBundle) {
            console.log('Bundle Version 4 detected successfully!');
            completed = true;
            break;
        }
    }

    await cdp.wait(2000);
    await cdp.screenshot('v4_uploaded_result.png');

    // 4. Set Release name and notes
    if (completed) {
        await cdp.eval(`(() => {
            const inputs = Array.from(document.querySelectorAll('input[type="text"]'));
            const releaseNameInput = inputs.find(i => (i.getAttribute('aria-label') || '').toLowerCase().includes('release name') || i.value.includes('1.2.'));
            if (releaseNameInput) {
                const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                nativeSet.call(releaseNameInput, '1.2.5 (4) - Clean Production Release');
                releaseNameInput.dispatchEvent(new Event('input', { bubbles: true }));
                releaseNameInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        })()`);
        console.log('Updated release name');

        // Click Next
        const clickNext = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const nextBtn = btns.find(b => b.innerText.trim() === 'Next' && !b.disabled);
            if (nextBtn) {
                nextBtn.scrollIntoView({ block: 'center' });
                nextBtn.click();
                return { clicked: true };
            }
            return { error: 'Next button not clickable' };
        })()`);
        console.log('Clicked Next on release editor:', clickNext);
        await cdp.wait(5000);

        await cdp.screenshot('v4_review_screen.png');

        // Click Save and publish / Save
        const clickPublish = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const pubBtn = btns.find(b => (b.innerText || '').includes('Save and publish') || (b.innerText || '').includes('Save'));
            if (pubBtn && !pubBtn.disabled) {
                pubBtn.scrollIntoView({ block: 'center' });
                pubBtn.click();
                return { clicked: true, text: pubBtn.innerText.trim() };
            }
            return { error: 'Publish button not clickable' };
        })()`);
        console.log('Publish action:', clickPublish);
        await cdp.wait(3000);

        // Confirm modal if any
        const confirmRes = await cdp.eval(`(() => {
            const dialog = document.querySelector('mat-dialog-container, [role="dialog"]');
            if (dialog) {
                const btns = Array.from(dialog.querySelectorAll('button'));
                const confirmBtn = btns.find(b => (b.innerText || '').includes('Save and publish') || (b.innerText || '').includes('Publish') || (b.innerText || '').includes('Confirm'));
                if (confirmBtn) {
                    confirmBtn.click();
                    return { confirmed: true, text: confirmBtn.innerText.trim() };
                }
            }
            return { dialog: false };
        })()`);
        console.log('Confirm dialog result:', confirmRes);
        await cdp.wait(3000);

        await cdp.screenshot('v4_published_live.png');
    }

    cdp.close();
}

main().catch(console.error);
