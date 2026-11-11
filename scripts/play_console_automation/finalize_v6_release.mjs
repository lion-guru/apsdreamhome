import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Play Console release editor to finalize Version 6...');
    const cdp = new CDPClient();
    await cdp.init();

    const curUrl = await cdp.eval('window.location.href');
    console.log('Current URL:', curUrl);

    // 1. Set short release name (<= 50 chars)
    console.log('Setting short release name (34 chars)...');
    const setNameRes = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input'));
        const nameInput = inputs.find(i => 
            i.placeholder?.includes('Release name') || 
            i.getAttribute('aria-label')?.includes('Release name') || 
            i.name?.includes('releaseName')
        );
        if (nameInput) {
            nameInput.focus();
            nameInput.value = '1.2.7 (6) - Luxury UI & Deal Tools';
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            nameInput.dispatchEvent(new Event('change', { bubbles: true }));
            return { success: true, val: nameInput.value, len: nameInput.value.length };
        }
        return { notFound: true };
    })()`);
    console.log('Set release name result:', setNameRes);

    await cdp.wait(2000);

    // 2. Check errors
    const errors = await cdp.eval(`Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim()).filter(Boolean)`);
    console.log('Current errors after setting name:', errors);

    // 3. Click Next button
    console.log('Clicking Next button...');
    const nextRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const nextBtn = btns.find(b => !b.disabled && b.innerText.trim() === 'Next');
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { notFound: true, buttons: btns.map(b => ({ text: b.innerText.trim(), disabled: b.disabled })).filter(b => b.text) };
    })()`);
    console.log('Next button result:', nextRes);

    await cdp.wait(6000);

    await cdp.screenshot('v6_after_next.png');
    console.log('Saved screenshot v6_after_next.png');

    const reviewUrl = await cdp.eval('window.location.href');
    console.log('URL after Next:', reviewUrl);

    // 4. Check for Save / Start rollout to internal testing / Save and publish
    const rolloutCheck = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const candidates = btns.filter(b => !b.disabled && (
            b.innerText.includes('Save and publish') || 
            b.innerText.includes('Start rollout') || 
            b.innerText.includes('Save')
        ));
        return candidates.map(b => b.innerText.trim());
    })()`);
    console.log('Available action buttons on review page:', rolloutCheck);

    if (rolloutCheck.length > 0) {
        const targetBtnText = rolloutCheck[0];
        console.log(`Clicking action button: "${targetBtnText}"...`);
        await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const b = btns.find(x => !x.disabled && (
                x.innerText.includes('Save and publish') || 
                x.innerText.includes('Start rollout') || 
                x.innerText.includes('Save')
            ));
            if (b) {
                b.scrollIntoView({ block: 'center' });
                b.click();
            }
        })()`);

        await cdp.wait(4000);

        // Check if a confirmation modal dialog popped up
        const dialogCheck = await cdp.eval(`(() => {
            const dialog = document.querySelector('mat-dialog-container, [role="dialog"]');
            if (dialog) {
                const btns = Array.from(dialog.querySelectorAll('button'));
                const confirmBtn = btns.find(b => !b.disabled && (
                    b.innerText.includes('Save and publish') || 
                    b.innerText.includes('Publish') || 
                    b.innerText.includes('Save') || 
                    b.innerText.includes('Start rollout')
                ));
                if (confirmBtn) {
                    confirmBtn.click();
                    return { dialogConfirmed: true, text: confirmBtn.innerText.trim() };
                }
                return { dialogFound: true, buttons: btns.map(b => b.innerText.trim()) };
            }
            return { noDialog: true };
        })()`);
        console.log('Dialog check:', dialogCheck);

        await cdp.wait(5000);
        await cdp.screenshot('v6_published_live.png');
        console.log('Saved screenshot v6_published_live.png');
    }

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
