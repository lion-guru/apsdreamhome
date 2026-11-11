import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const selectRes = await cdp.eval(`(() => {
        // Find radio that has text "No, my app does not contain ads"
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"], label'));
        const noTarget = radios.find(r => (r.innerText || '').includes('No, my app does not contain ads'));
        if (noTarget) {
            noTarget.scrollIntoView({ block: 'center' });
            noTarget.click();
            return { clicked: true, text: noTarget.innerText.trim() };
        }
        return { error: 'No target radio not found' };
    })()`);
    console.log('Select result:', selectRes);
    await cdp.wait(1000);

    const saveRes = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, disabled: saveBtn.disabled };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Save result:', saveRes);
    await cdp.wait(3000);

    await cdp.screenshot('ads_declaration_saved.png');

    // Navigate back to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
