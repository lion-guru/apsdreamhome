import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Play Console release editor via CDP...');
    const cdp = new CDPClient();
    await cdp.init();

    const curUrl = await cdp.eval('window.location.href');
    console.log('Current URL on release editor:', curUrl);

    // 1. Clear any previous failed upload if present
    const clearResult = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button, [role="button"]'));
        const clearBtn = btns.find(b => {
            const txt = (b.innerText || '').trim().toLowerCase();
            const aria = (b.getAttribute('aria-label') || '').toLowerCase();
            return txt === 'clear' || aria.includes('clear') || aria.includes('remove') || txt.includes('clear');
        });
        if (clearBtn) {
            clearBtn.click();
            return { cleared: true, text: clearBtn.innerText };
        }
        return { cleared: false };
    })()`);
    console.log('Clear previous upload attempt result:', clearResult);
    await cdp.wait(2000);

    // 2. Find file input via CDP DOM
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

    // 3. Monitor upload progress
    let completed = false;
    for (let i = 0; i < 90; i++) {
        await cdp.wait(3000);
        const st = await cdp.eval(`(() => {
            const body = document.body.innerText;
            const progress = document.querySelector('mat-progress-bar, [role="progressbar"]');
            const percent = progress?.getAttribute('aria-valuenow') || '';
            const hasBundle = body.includes('1.2.6') && (body.includes('(5)') || body.includes('Version code 5') || body.includes('5 (1.2.6)'));
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
            console.log('Bundle Version 5 detected successfully!');
            completed = true;
            break;
        }
        if (st.error && st.error.length > 5 && !st.error.includes('Version code 3')) {
            console.error('Upload Error:', st.error);
            break;
        }
    }

    if (completed) {
        await cdp.wait(4000);

        // 4. Set release name
        console.log('Setting release name...');
        await cdp.eval(`(() => {
            const inputs = Array.from(document.querySelectorAll('input'));
            const nameInput = inputs.find(i => i.placeholder?.includes('Release name') || i.getAttribute('aria-label')?.includes('Release name') || i.name?.includes('releaseName'));
            if (nameInput) {
                nameInput.value = '1.2.6 (5) - Luxury UI & Explore as Guest';
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                nameInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        })()`);

        await cdp.screenshot('v5_uploaded_screenshot.png');
        console.log('Saved screenshot v5_uploaded_screenshot.png');

        // 5. Click Next
        console.log('Clicking Next to preview release...');
        const nextResult = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const nextBtn = btns.find(b => !b.disabled && b.innerText.trim() === 'Next');
            if (nextBtn) {
                nextBtn.scrollIntoView({ block: 'center' });
                nextBtn.click();
                return { clicked: true, text: nextBtn.innerText.trim() };
            }
            return { notFound: true, available: btns.map(b => b.innerText.trim()).filter(Boolean) };
        })()`);
        console.log('Next button result:', nextResult);
    }

    cdp.close();
}

main().catch(console.error);
