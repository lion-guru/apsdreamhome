import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const aabPath = 'c:\\xampp\\htdocs\\apsdreamhome\\mobile\\apsdreamhome_app_v2\\android\\app\\build\\outputs\\bundle\\release\\app-release.aab';
    
    // Find file input node
    const root = await cdp.send('DOM.getDocument');
    const inputNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'input[type="file"]'
    });

    console.log('File input node:', inputNode);
    if (!inputNode || !inputNode.nodeId) {
        console.error('File input nodeId not found');
        cdp.close();
        return;
    }

    console.log('Setting file input files:', aabPath);
    await cdp.send('DOM.setFileInputFiles', {
        files: [aabPath],
        nodeId: inputNode.nodeId
    });
    console.log('Dispatched DOM.setFileInputFiles.');

    // Wait and monitor upload
    let completed = false;
    for (let i = 0; i < 45; i++) {
        await cdp.wait(3000);
        const st = await cdp.eval(`(() => {
            const body = document.body.innerText;
            const progress = document.querySelector('mat-progress-bar, [role="progressbar"]');
            const percent = progress?.getAttribute('aria-valuenow') || '';
            const hasV4 = body.includes('4 (1.2.5)') || body.includes('1.2.5') || body.includes('Version 4');
            const error = document.querySelector('.error-message, mat-error, [role="alert"]')?.innerText;
            return {
                uploading: body.includes('Uploading') || !!progress,
                percent,
                hasV4,
                error: (error || '').substring(0, 100)
            };
        })()`);

        console.log(`[${(i + 1) * 3}s]`, st);
        if (st.hasV4) {
            console.log('Version 4 AAB upload verified!');
            completed = true;
            break;
        }
        if (st.error && st.error.includes('already been used')) {
            console.error('Version code error:', st.error);
            break;
        }
    }

    await cdp.screenshot('v4_uploaded_state.png');

    if (completed) {
        // Set release name
        await cdp.eval(`(() => {
            const inputs = Array.from(document.querySelectorAll('input[type="text"]'));
            const rName = inputs.find(i => (i.getAttribute('aria-label') || '').toLowerCase().includes('release name') || i.value.includes('1.2.'));
            if (rName) {
                const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                nativeSet.call(rName, '1.2.5 (4) - Clean Production Release');
                rName.dispatchEvent(new Event('input', { bubbles: true }));
                rName.dispatchEvent(new Event('change', { bubbles: true }));
            }
        })()`);
        console.log('Release name set.');

        // Click Next
        const clickNext = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const nextBtn = btns.find(b => b.innerText.trim() === 'Next' && !b.disabled);
            if (nextBtn) {
                nextBtn.scrollIntoView({ block: 'center' });
                nextBtn.click();
                return { clicked: true };
            }
            return { error: 'Next not clickable' };
        })()`);
        console.log('Clicked Next:', clickNext);
        await cdp.wait(5000);

        await cdp.screenshot('v4_preview_page.png');

        // Click Save and publish
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
        console.log('Clicked Publish:', clickPublish);
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
        console.log('Confirm modal result:', confirmRes);
        await cdp.wait(3000);

        await cdp.screenshot('v4_live_final.png');
    }

    cdp.close();
}

main().catch(console.error);
