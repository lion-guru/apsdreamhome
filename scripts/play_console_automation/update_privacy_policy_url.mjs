import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const privacyUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/privacy-policy`;
    console.log('Navigating to Privacy Policy page:', privacyUrl);
    await cdp.navigate(privacyUrl);
    await cdp.wait(5000);

    // Focus and replace the URL input with the active, working Google Firebase URL
    const newPolicyUrl = 'https://apsgroup-163d9.web.app/privacy';
    console.log('Setting Privacy Policy URL to:', newPolicyUrl);

    await cdp.eval(`(() => {
        const input = document.querySelector('input[type="text"], input[type="url"], input');
        if (input) {
            input.focus();
            input.select();
            document.execCommand('selectAll', false, null);
        }
    })()`);
    await cdp.send('Input.insertText', { text: newPolicyUrl });
    await cdp.wait(1500);

    const inputVal = await cdp.eval(`(() => {
        const input = document.querySelector('input');
        return input ? input.value : null;
    })()`);
    console.log('Current input value:', inputVal);
    await cdp.screenshot('privacy_input_updated.png');

    // Click Save button
    console.log('Clicking Save...');
    const saveRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const saveBtn = btns.find(b => (b.innerText || '').trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, text: saveBtn.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Click Save result:', saveRes);
    await cdp.wait(4000);
    await cdp.screenshot('privacy_saved_result.png');

    // Navigate to Publishing Overview
    const pubUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/publishing`;
    console.log('Navigating to publishing overview:', pubUrl);
    await cdp.navigate(pubUrl);
    await cdp.wait(6000);

    const pubStatus = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            disabled: b.disabled
        })).filter(b => b.text.length > 0);

        return {
            hasIssues: text.includes('issue found') || text.includes('DNS name not found'),
            btns,
            snippet: text.substring(0, 1600)
        };
    })()`);
    console.log('Publishing Status after Privacy Fix:\n', JSON.stringify(pubStatus, null, 2));
    await cdp.screenshot('publishing_after_privacy_fix.png');

    // Look for Submit changes for review button
    const submitBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0;
        });

        const target = btns.find(b => {
            const t = (b.innerText || '').trim();
            return t.includes('Submit') && t.includes('review') || t.includes('Send') && t.includes('review');
        });

        if (target) {
            return {
                text: target.innerText.trim(),
                disabled: target.disabled
            };
        }
        return { notFound: true };
    })()`);
    console.log('Submit button status:', submitBtn);

    // If enabled, click it!
    if (submitBtn && !submitBtn.disabled && !submitBtn.notFound) {
        console.log('Clicking Submit button...');
        await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const target = btns.find(b => (b.innerText || '').includes('review') && !b.disabled);
            if (target) target.click();
        })()`);
        await cdp.wait(4000);

        // Confirm modal if any
        const confModal = await cdp.eval(`(() => {
            const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
            if (dialog) {
                const btns = Array.from(dialog.querySelectorAll('button')).filter(b => !b.disabled);
                const conf = btns.find(b => (b.innerText || '').includes('Submit') || (b.innerText || '').includes('Send') || (b.innerText || '').includes('Confirm'));
                if (conf) {
                    conf.click();
                    return { confirmed: true, text: conf.innerText.trim() };
                }
            }
            return { noDialog: true };
        })()`);
        console.log('Confirmation result:', confModal);
        await cdp.wait(6000);
        await cdp.screenshot('submitted_for_review_final.png');
    }

    cdp.close();
}

main().catch(console.error);
