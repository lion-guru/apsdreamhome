import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Finding and clicking blue Save button in "Save changes to email list?" modal...');
    const clickRes = await cdp.eval(`(() => {
        const dialogs = Array.from(document.querySelectorAll('[role="dialog"], mat-dialog-container, .popup, focus-trap'));
        const modal = dialogs.find(d => d.innerText.includes('Save changes to email list'));
        if (modal) {
            const btns = Array.from(modal.querySelectorAll('button'));
            const saveBtn = btns.find(b => !b.disabled && b.innerText.trim() === 'Save');
            if (saveBtn) {
                saveBtn.scrollIntoView({ block: 'center' });
                saveBtn.click();
                return { clicked: true, text: saveBtn.innerText };
            }
            return { modalFound: true, btns: btns.map(b => b.innerText.trim()) };
        }

        // Fallback: any button with text "Save" inside an overlay
        const allBtns = Array.from(document.querySelectorAll('button'));
        const fallback = allBtns.find(b => !b.disabled && b.innerText.trim() === 'Save' && !b.classList.contains('mdc-button--unelevated'));
        if (fallback) {
            fallback.click();
            return { clickedFallback: true, text: fallback.innerText };
        }

        return { notFound: true };
    })()`);

    console.log('Modal click result:', clickRes);

    await cdp.wait(4000);
    await cdp.screenshot('after_modal_save_clicked.png');

    // Inspect main track page
    const trackState = await cdp.eval(`(() => {
        const mainSaveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => r.innerText.trim().replace(/\\n/g, ' ')).filter(r => r.includes('Internal Testers'));
        return {
            rows,
            mainSaveDisabled: mainSaveBtn ? mainSaveBtn.disabled : null
        };
    })()`);
    console.log('Main track page state after modal save:', trackState);

    // If main page save button is enabled, click it
    if (trackState.mainSaveDisabled === false) {
        console.log('Clicking main page Save button...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
        console.log('Main track page saved!');
    }

    await cdp.screenshot('track_saved_final.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
