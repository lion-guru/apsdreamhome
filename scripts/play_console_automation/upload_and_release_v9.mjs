import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Play Console release editor via CDP for Version 9 (1.2.10+9)...');
    const cdp = new CDPClient();
    await cdp.init();

    const curUrl = await cdp.eval('window.location.href');
    console.log('Current URL:', curUrl);

    // If not already on release editor page, navigate to releases page
    if (!curUrl.includes('/releases/')) {
        console.log('Navigating to releases page...');
        await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4701293369268175450?tab=releases`);
        await cdp.wait(10000);

        let createRes = { clicked: false };
        for (let attempt = 0; attempt < 4 && !createRes.clicked; attempt++) {
            createRes = await cdp.eval(`(() => {
                const btns = Array.from(document.querySelectorAll('button, a'));
                const createBtn = btns.find(b => {
                    const txt = (b.innerText || '').trim().toLowerCase();
                    return txt.includes('create new release') || txt.includes('create release');
                });
                if (createBtn) {
                    createBtn.scrollIntoView({ block: 'center' });
                    createBtn.click();
                    return { clicked: true };
                }
                return { clicked: false };
            })()`);
            console.log('Create release button click (attempt ' + (attempt + 1) + '):', createRes);
            if (!createRes.clicked) await cdp.wait(5000);
        }
        await cdp.wait(8000);
    }

    console.log('Waiting for release editor file input...');
    const aabPath = 'C:\\xampp\\htdocs\\apsdreamhome\\public\\downloads\\apsdreamhome-v1.2.10-release.aab';
    let inputNode = null;
    for (let attempt = 0; attempt < 12 && !inputNode; attempt++) {
        await cdp.wait(5000);
        const root = await cdp.send('DOM.getDocument');
        inputNode = await cdp.send('DOM.querySelector', {
            nodeId: root.root.nodeId,
            selector: 'input[type="file"][accept*=".aab"], input[type="file"]'
        });
        if (!inputNode || !inputNode.nodeId) {
            console.log('File input not ready yet (attempt ' + (attempt + 1) + '), retrying...');
            inputNode = null;
        }
    }

    if (!inputNode || !inputNode.nodeId) {
        console.error('File input not found in DOM');
        cdp.close();
        return;
    }

    console.log('Found file input node ID:', inputNode.nodeId);
    console.log('Dispatching DOM.setFileInputFiles for Version 9 AAB bundle (~83MB)...');
    await cdp.send('DOM.setFileInputFiles', {
        files: [aabPath],
        nodeId: inputNode.nodeId
    });
    console.log('DOM.setFileInputFiles dispatched.');

    // 2. Monitor upload & validation progress
    let completed = false;
    for (let i = 0; i < 150; i++) {
        await cdp.wait(3000);
        const st = await cdp.eval(`(() => {
            const body = document.body.innerText;
            const progress = document.querySelector('mat-progress-bar, [role="progressbar"]');
            const percent = progress?.getAttribute('aria-valuenow') || '';
            const hasBundle = (body.includes('1.2.10') || body.includes('9 (1.2.10)') || body.includes('(9)') || body.includes('Version code 9')) && !body.includes('Uploading');
            const uploadText = Array.from(document.querySelectorAll('*'))
                .map(e => e.innerText || '')
                .find(t => t.includes('MB of') && t.includes('MB')) || '';
            const rawErr = document.querySelector('.error-message, mat-error')?.innerText;
            return {
                uploading: body.includes('Uploading') || !!progress,
                uploadText,
                percent,
                hasBundle,
                error: (rawErr || '').substring(0, 100)
            };
        })()`);

        console.log(`[${(i + 1) * 3}s] Upload status:`, JSON.stringify(st));
        if (st.hasBundle) {
            console.log('SUCCESS: Bundle Version 9 (1.2.10+9) detected on Play Console!');
            completed = true;
            break;
        }
        if (st.error && st.error.length > 5 && !st.error.includes('Version code 8')) {
            console.error('Upload Error:', st.error);
            break;
        }
    }

    if (completed) {
        await cdp.wait(4000);

        // 3. Set release name (strictly <= 50 chars)
        console.log('Setting release name...');
        const releaseName = '1.2.10 (9) - AI Dashboard & Photo Leads';
        await cdp.eval(`(() => {
            const inputs = Array.from(document.querySelectorAll('input'));
            const nameInput = inputs.find(i => 
                i.placeholder?.includes('Release name') || 
                i.getAttribute('aria-label')?.includes('Release name') || 
                i.name?.includes('releaseName')
            );
            if (nameInput) {
                nameInput.value = '${releaseName}';
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                nameInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        })()`);

        // Set release notes
        console.log('Setting release notes...');
        const notes = 'APS Dream Home v1.2.10 (Build 9):\\n• AI Dashboard: Next best actions, churn alerts, priority leads, and deal predictions.\\n• Photo Lead: Create a lead from a property photo with AI extraction; confirm site visits with AI photos.\\n• Home recommendations: AI picks for you with confidence scores.\\n• Smart Search, AI chatbot with voice (TTS), and valuation tools.\\n• Performance: cached dashboards, fixed payroll/training/voice pages.';
        await cdp.eval(`(() => {
            const textareas = Array.from(document.querySelectorAll('textarea'));
            const noteArea = textareas.find(t => 
                t.placeholder?.includes('Release notes') || 
                t.getAttribute('aria-label')?.includes('Release notes') ||
                t.offsetParent !== null
            );
            if (noteArea) {
                noteArea.value = '${notes}';
                noteArea.dispatchEvent(new Event('input', { bubbles: true }));
                noteArea.dispatchEvent(new Event('change', { bubbles: true }));
            }
        })()`);

        await cdp.screenshot('v9_uploaded_screenshot.png');
        console.log('Saved screenshot v9_uploaded_screenshot.png');

        // 4. Click Next
        console.log('Clicking Next to preview release...');
        const nextResult = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const nextBtn = btns.find(b => !b.disabled && b.innerText.trim() === 'Next' && b.offsetParent !== null);
            if (nextBtn) {
                nextBtn.scrollIntoView({ block: 'center' });
                nextBtn.click();
                return { clicked: true, text: nextBtn.innerText.trim() };
            }
            return { notFound: true, available: btns.map(b => b.innerText.trim()).filter(Boolean) };
        })()`);
        console.log('Next button result:', nextResult);

        await cdp.wait(6000);
        await cdp.screenshot('v9_preview_page.png');
        console.log('Saved screenshot v9_preview_page.png');

        // 5. Check review page and click Save and publish / Start rollout if available
        const rolloutCheck = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const saveOrRollout = btns.find(b => !b.disabled && b.offsetParent !== null && (
                b.innerText.includes('Save and publish') || 
                b.innerText.includes('Start rollout to Internal testing') ||
                b.innerText.includes('Start rollout') ||
                (b.innerText.trim() === 'Save' && !b.innerText.includes('draft'))
            ));
            const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim());
            return {
                hasSaveOrRollout: !!saveOrRollout,
                buttonText: saveOrRollout ? saveOrRollout.innerText.trim() : null,
                availableButtons: btns.filter(b => !b.disabled && b.offsetParent !== null).map(b => b.innerText.trim()),
                errors
            };
        })()`);
        console.log('Rollout check:', rolloutCheck);

        if (rolloutCheck.hasSaveOrRollout && rolloutCheck.buttonText) {
            console.log(`Clicking "${rolloutCheck.buttonText}"...`);
            await cdp.eval(`(() => {
                const btns = Array.from(document.querySelectorAll('button'));
                const b = btns.find(x => !x.disabled && x.offsetParent !== null && (
                    x.innerText.includes('Save and publish') || 
                    x.innerText.includes('Start rollout to Internal testing') ||
                    x.innerText.includes('Start rollout') ||
                    (x.innerText.trim() === 'Save' && !x.innerText.includes('draft'))
                ));
                if (b) {
                    b.scrollIntoView({ block: 'center' });
                    b.click();
                }
            })()`);
            await cdp.wait(5000);

            // Handle potential modal confirmation dialog
            await cdp.eval(`(() => {
                const modalBtns = Array.from(document.querySelectorAll('mat-dialog-container button, [role="dialog"] button'));
                const confirmBtn = modalBtns.find(b => !b.disabled && (
                    b.innerText.includes('Save and publish') || 
                    b.innerText.includes('Rollout') || 
                    b.innerText.includes('Confirm') ||
                    b.innerText.includes('Save')
                ));
                if (confirmBtn) {
                    confirmBtn.click();
                }
            })()`);
            await cdp.wait(5000);

            await cdp.screenshot('v9_final_status.png');
            console.log('Saved screenshot v9_final_status.png');
        }
    }

    cdp.close();
}

main().catch(console.error);
