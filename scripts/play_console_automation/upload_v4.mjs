import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const trackUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4701293369268175450?tab=releases`;
    console.log('Navigating to releases track...');
    await cdp.navigate(trackUrl);
    await cdp.wait(3000);

    // Click "Create new release"
    const clickCreate = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button, a[role="button"]'));
        const createBtn = btns.find(b => b.innerText.includes('Create new release') || b.innerText.includes('Create release'));
        if (createBtn) {
            createBtn.scrollIntoView({ block: 'center' });
            createBtn.click();
            return { clicked: true, text: createBtn.innerText.trim() };
        }
        return { error: 'Create release button not found' };
    })()`);
    console.log('Clicked Create new release:', clickCreate);
    await cdp.wait(4000);

    console.log('Current URL on release editor:', await cdp.eval('window.location.href'));
    await cdp.screenshot('create_release_v4.png');

    // Find file input on release editor
    const fileInputId = await cdp.eval(`(() => {
        const fi = document.querySelector('input[type="file"]');
        return fi ? { id: fi.id, name: fi.name, accept: fi.accept } : null;
    })()`);
    console.log('File input found:', fileInputId);

    // Set file input via DOM.setFileInputFiles
    const aabPath = 'c:\\xampp\\htdocs\\apsdreamhome\\mobile\\apsdreamhome_app_v2\\android\\app\\build\\outputs\\bundle\\release\\app-release.aab';
    
    // Get document root
    const root = await cdp.send('DOM.getDocument');
    const inputNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'input[type="file"]'
    });

    if (inputNode && inputNode.nodeId) {
        console.log('Uploading Version 4 AAB to node', inputNode.nodeId);
        await cdp.send('DOM.setFileInputFiles', {
            files: [aabPath],
            nodeId: inputNode.nodeId
        });
        console.log('DOM.setFileInputFiles dispatched.');
    } else {
        console.error('File input node not found via CDP querySelector');
    }

    // Monitor upload progress
    console.log('Monitoring upload...');
    let uploadDone = false;
    for (let i = 0; i < 40; i++) {
        await cdp.wait(3000);
        const uploadStatus = await cdp.eval(`(() => {
            const body = document.body.innerText;
            const progress = document.querySelector('mat-progress-bar, [role="progressbar"]');
            const percent = progress?.getAttribute('aria-valuenow') || '';
            const error = document.querySelector('.error-message, mat-error')?.innerText;
            const hasBundle = body.includes('1.2.5') || body.includes('version code 4') || body.includes('4 (1.2.5)');
            return {
                uploading: body.includes('Uploading') || !!progress,
                percent,
                hasBundle,
                error
            };
        })()`);

        console.log(`[${i * 3}s] Status:`, uploadStatus);
        if (uploadStatus.hasBundle) {
            console.log('Version 4 upload complete!');
            uploadDone = true;
            break;
        }
        if (uploadStatus.error) {
            console.error('Upload error detected:', uploadStatus.error);
            break;
        }
    }

    await cdp.screenshot('v4_uploaded.png');
    cdp.close();
}

main().catch(console.error);
