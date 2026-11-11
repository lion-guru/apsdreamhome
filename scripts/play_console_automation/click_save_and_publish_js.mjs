import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const clickRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => (b.innerText || '').includes('Save and publish') && !b.disabled);
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, text: saveBtn.innerText.trim() };
        }
        return { error: 'Button not found' };
    })()`);

    console.log('Save and publish click:', clickRes);
    await cdp.wait(3000);

    // Confirm dialog if it appears
    const confRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const confBtn = btns.find(b => (b.innerText || '').includes('Save and publish') && !b.disabled);
        if (confBtn) {
            confBtn.click();
            return { clickedConfirm: true };
        }
        return { noConfirm: true };
    })()`);
    console.log('Confirm result:', confRes);
    await cdp.wait(3000);

    await cdp.screenshot('contact_saved_success.png');
    cdp.close();
}

main().catch(console.error);
