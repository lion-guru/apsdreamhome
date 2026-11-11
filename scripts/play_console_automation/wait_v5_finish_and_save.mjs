import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Play Console via CDP to wait for upload finish...');
    const cdp = new CDPClient();
    await cdp.init();

    let uploaded = false;
    for (let i = 0; i < 40; i++) {
        await cdp.wait(3000);
        const st = await cdp.eval(`(() => {
            const body = document.body.innerText;
            const hasV5 = body.includes('5 (1.2.6)') || body.includes('1.2.6') || (body.includes('Version code 5') && !body.includes('Uploading'));
            const uploadText = body.match(/(\\d+\\.?\\d*\\s*MB of \\d+\\.?\\d*\\s*MB)/)?.[0] || '';
            const isUploading = body.includes('Uploading app-release.aab');
            return { hasV5, uploadText, isUploading };
        })()`);

        console.log(`[${(i + 1) * 3}s] Status:`, JSON.stringify(st));
        if (st.hasV5 && !st.isUploading) {
            console.log('Version 5 AAB upload and validation COMPLETE!');
            uploaded = true;
            break;
        }
    }

    if (!uploaded) {
        console.log('Still processing or waiting...');
        await cdp.screenshot('v5_still_processing.png');
        cdp.close();
        return;
    }

    await cdp.wait(3000);

    // 1. Set release name
    console.log('Setting Release name...');
    await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input'));
        const nameInput = inputs.find(i => 
            i.placeholder?.includes('Release name') || 
            i.getAttribute('aria-label')?.includes('Release name') || 
            i.name?.includes('releaseName')
        );
        if (nameInput) {
            nameInput.value = '1.2.6 (5) - Luxury UI & Guest Mode';
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            nameInput.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }
        return false;
    })()`);

    // 2. Click Next button
    console.log('Finding and clicking Next button...');
    const nextRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const nextBtn = btns.find(b => !b.disabled && b.innerText.trim() === 'Next');
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { notFound: true, buttons: btns.map(b => b.innerText.trim()).filter(Boolean) };
    })()`);
    console.log('Next button result:', nextRes);

    await cdp.wait(5000);

    // 3. Take screenshot of next state
    await cdp.screenshot('v5_review_page.png');
    console.log('Current URL:', await cdp.eval('window.location.href'));

    // 4. Check for Save / Start rollout button
    const rolloutCheck = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button'));
        const saveOrRollout = btns.find(b => !b.disabled && (
            b.innerText.includes('Save and publish') || 
            b.innerText.includes('Save') || 
            b.innerText.includes('Start rollout')
        ));
        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim());
        return {
            hasSaveOrRollout: !!saveOrRollout,
            buttonText: saveOrRollout ? saveOrRollout.innerText.trim() : null,
            errors
        };
    })()`);
    console.log('Rollout check:', rolloutCheck);

    if (rolloutCheck.hasSaveOrRollout && rolloutCheck.buttonText) {
        console.log(`Clicking "${rolloutCheck.buttonText}"...`);
        await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const b = btns.find(x => !x.disabled && (
                x.innerText.includes('Save and publish') || 
                x.innerText.includes('Save') || 
                x.innerText.includes('Start rollout')
            ));
            if (b) b.click();
        })()`);
        await cdp.wait(4000);

        // Confirm modal if any
        await cdp.eval(`(() => {
            const modal = document.querySelector('[role="dialog"], mat-dialog-container');
            if (modal) {
                const btn = Array.from(modal.querySelectorAll('button')).find(b => {
                    const t = b.innerText.toLowerCase();
                    return t.includes('save') || t.includes('rollout') || t.includes('publish');
                });
                if (btn) btn.click();
            }
        })()`);
        await cdp.wait(3000);
        console.log('Rollout action dispatched!');
    }

    await cdp.screenshot('v5_final_state.png');
    cdp.close();
}

main().catch(console.error);
