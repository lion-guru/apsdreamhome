import { CDPClient } from './cdp_helper.mjs';

async function main() {
    console.log('Connecting to Play Console release editor for Release 8 finalization...');
    const cdp = new CDPClient();
    await cdp.init();

    const curUrl = await cdp.eval('window.location.href');
    console.log('Current URL:', curUrl);

    // 1. Inspect current errors and fields
    const pageState = await cdp.eval(`(() => {
        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim());
        const btns = Array.from(document.querySelectorAll('button')).filter(b => b.offsetParent !== null).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled
        }));
        const nameInput = document.querySelector('input[placeholder*="Release name"], input[aria-label*="Release name"], input[name*="releaseName"]');
        const noteArea = document.querySelector('textarea');
        return {
            errors,
            buttons: btns.filter(b => b.text.length > 0 && b.text.length < 50),
            nameVal: nameInput?.value,
            notesVal: noteArea?.value
        };
    })()`);
    console.log('Current page state:', JSON.stringify(pageState, null, 2));

    // 2. Set valid release name
    const releaseName = '1.2.9 (8) - Web Parity & Auctions Desk';
    console.log(`Setting release name: "${releaseName}" (${releaseName.length} chars)...`);
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

    // 3. Set valid release notes with language tags (strictly under 500 characters limit)
    console.log('Setting release notes with <en-US> language tag (under 500 chars)...');
    const validNotes = '<en-US>\\nAPS Dream Home v1.2.9 (Build 8):\\n• Property Auctions: Live plot bidding & countdown timers\\n• Farmer Desk: Land portfolio & acquisition offers\\n• VIP Executive Meetings: Schedule legal & director consultations\\n• Sub-Registrar Desk & 4K Drone site timelapse\\n• Digital plot allotment acceptance with OTP sign-off\\n• Associate advances, digital ID badges & safe closing\\n</en-US>';
    
    await cdp.eval(`(() => {
        const textareas = Array.from(document.querySelectorAll('textarea'));
        const noteArea = textareas.find(t => t.offsetParent !== null);
        if (noteArea) {
            noteArea.value = '${validNotes}';
            noteArea.dispatchEvent(new Event('input', { bubbles: true }));
            noteArea.dispatchEvent(new Event('change', { bubbles: true }));
        }
    })()`);

    await cdp.wait(2000);

    // 4. Re-check errors and Next button status
    const statusAfterNotes = await cdp.eval(`(() => {
        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim());
        const btns = Array.from(document.querySelectorAll('button')).filter(b => b.offsetParent !== null).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled
        }));
        return { errors, buttons: btns };
    })()`);
    console.log('Status after setting notes:', JSON.stringify(statusAfterNotes, null, 2));

    await cdp.screenshot('v8_fixed_form.png');
    console.log('Saved v8_fixed_form.png');

    // 5. Click Next button
    console.log('Clicking Next button...');
    const nextClick = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const nextBtn = btns.find(b => b.innerText.trim() === 'Next' && !b.disabled && b.offsetParent !== null);
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true, text: nextBtn.innerText.trim() };
        }
        return { clicked: false, available: btns.filter(b => b.offsetParent !== null).map(b => ({ text: b.innerText.trim(), disabled: b.disabled })) };
    })()`);
    console.log('Next button click result:', nextClick);

    await cdp.wait(6000);
    await cdp.screenshot('v8_preview_screen.png');
    console.log('Saved v8_preview_screen.png');

    // 6. Review page check & Rollout / Save and publish
    const reviewInfo = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => !b.disabled && b.offsetParent !== null);
        const rolloutBtn = btns.find(b => 
            b.innerText.includes('Save and publish') || 
            b.innerText.includes('Start rollout to Internal testing') ||
            b.innerText.includes('Start rollout') ||
            (b.innerText.trim() === 'Save' && !b.innerText.includes('draft'))
        );
        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(e => e.innerText.trim());
        return {
            url: window.location.href,
            hasRolloutBtn: !!rolloutBtn,
            buttonText: rolloutBtn ? rolloutBtn.innerText.trim() : null,
            availableButtons: btns.map(b => b.innerText.trim()),
            errors
        };
    })()`);
    console.log('Review/Rollout page info:', reviewInfo);

    if (reviewInfo.hasRolloutBtn && reviewInfo.buttonText) {
        console.log(`Clicking "${reviewInfo.buttonText}"...`);
        await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button')).filter(b => !b.disabled && b.offsetParent !== null);
            const b = btns.find(x => 
                x.innerText.includes('Save and publish') || 
                x.innerText.includes('Start rollout to Internal testing') ||
                x.innerText.includes('Start rollout') ||
                (x.innerText.trim() === 'Save' && !x.innerText.includes('draft'))
            );
            if (b) {
                b.scrollIntoView({ block: 'center' });
                b.click();
            }
        })()`);
        await cdp.wait(4000);

        // Check if confirmation modal dialog popped up
        const modalCheck = await cdp.eval(`(() => {
            const modalBtns = Array.from(document.querySelectorAll('mat-dialog-container button, [role="dialog"] button, .modal button')).filter(b => !b.disabled && b.offsetParent !== null);
            const confirmBtn = modalBtns.find(b => 
                b.innerText.includes('Save and publish') || 
                b.innerText.includes('Rollout') || 
                b.innerText.includes('Confirm') ||
                b.innerText.includes('Save')
            );
            if (confirmBtn) {
                confirmBtn.click();
                return { modalClicked: true, text: confirmBtn.innerText.trim() };
            }
            return { modalClicked: false, availableModalButtons: modalBtns.map(b => b.innerText.trim()) };
        })()`);
        console.log('Modal confirmation check:', modalCheck);
        await cdp.wait(5000);

        await cdp.screenshot('v8_final_status.png');
        console.log('Saved v8_final_status.png');
    }

    // 7. Verify track status
    const trackStatus = await cdp.eval(`(() => {
        return {
            url: window.location.href,
            bodySnippet: document.body.innerText.substring(0, 1500)
        };
    })()`);
    console.log('Track final status URL:', trackStatus?.url);

    cdp.close();
}

main().catch(console.error);
