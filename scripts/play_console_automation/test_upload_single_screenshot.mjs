import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('1. Opening side panel by clicking Add assets on Phone screenshots...');
    await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Add assets');
        if (btns[2]) {
            btns[2].scrollIntoView({ block: 'center' });
            btns[2].click();
        }
    })()`);

    await cdp.wait(2000);

    // 2. Find file input
    const root = await cdp.send('DOM.getDocument');
    const inputNode = await cdp.send('DOM.querySelector', {
        nodeId: root.root.nodeId,
        selector: 'simple-uploader input[type="file"], input[type="file"]'
    });

    console.log('Found inputNode:', inputNode);
    if (!inputNode || !inputNode.nodeId) {
        console.error('File input not found');
        cdp.close();
        return;
    }

    const testFile = 'c:\\xampp\\htdocs\\apsdreamhome\\assets\\images\\playstore_v2\\screenshot_1_explore.png';
    console.log('Uploading test screenshot:', testFile);
    await cdp.send('DOM.setFileInputFiles', {
        files: [testFile],
        nodeId: inputNode.nodeId
    });

    console.log('Dispatched DOM.setFileInputFiles. Waiting 5s for upload to process...');
    await cdp.wait(5000);

    // 3. Inspect what the side panel and page show
    const result = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container, aside');
        const buttons = Array.from(dialog ? dialog.querySelectorAll('button') : []).map(b => ({
            text: b.innerText.trim(),
            debugId: b.getAttribute('debug-id') || '',
            ariaLabel: b.getAttribute('aria-label') || '',
            disabled: b.disabled
        }));

        const images = Array.from(dialog ? dialog.querySelectorAll('img') : []).map(img => ({
            src: img.src ? img.src.slice(0, 80) : '',
            alt: img.alt
        }));

        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText);

        return {
            buttons,
            imagesCount: images.length,
            errors,
            hasUploading: body.includes('Uploading')
        };
    })()`);

    console.log('Upload result:', JSON.stringify(result, null, 2));
    await cdp.screenshot('test_screenshot_uploaded.png');

    cdp.close();
}

main().catch(console.error);
