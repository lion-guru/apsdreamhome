import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click "Save" button in the confirmation modal
    console.log('Confirming Save in dialog...');
    const modalSaveResult = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap, .popup, .modal');
        if (!dialog) return { error: 'No modal found' };
        const btns = Array.from(dialog.querySelectorAll('button'));
        const saveBtn = btns.find(b => (b.innerText || '').trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, text: saveBtn.innerText };
        }
        return { clicked: false, availableBtns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Modal Save result:', JSON.stringify(modalSaveResult, null, 2));
    await cdp.wait(3000);

    // 2. Check if bottom right track "Save" button is enabled
    console.log('Checking track-level Save button...');
    const trackSaveResult = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtns = btns.filter(b => (b.innerText || '').trim() === 'Save');
        const trackSave = saveBtns[saveBtns.length - 1]; // bottom right button
        if (trackSave && !trackSave.disabled) {
            trackSave.click();
            return { clicked: true, disabled: false };
        }
        return {
            clicked: false,
            exists: !!trackSave,
            disabled: trackSave ? trackSave.disabled : null
        };
    })()`);
    console.log('Track Save result:', JSON.stringify(trackSaveResult, null, 2));
    await cdp.wait(3000);

    // 3. Inspect final state of testers tab
    const finalState = await cdp.eval(`(() => {
        const rows = Array.from(document.querySelectorAll('tr, ess-row, [role="row"]')).map(r => r.innerText.trim().replace(/\\n/g, ' -- '));
        const joinLink = document.querySelector('a[href*="internaltest"], button[aria-label*="Copy link"]')?.innerText || '';
        return { rows, joinLink };
    })()`);
    console.log('Final page state:', JSON.stringify(finalState, null, 2));

    await cdp.screenshot('final_testers_saved.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
