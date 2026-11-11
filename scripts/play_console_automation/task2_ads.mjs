import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/ads`);
    await cdp.wait(3000);

    const info = await cdp.eval(`(() => {
        return {
            title: document.title,
            radios: Array.from(document.querySelectorAll('input[type="radio"], [role="radio"], material-radio')).map(r => ({
                text: r.innerText || r.parentElement?.innerText || '',
                checked: r.checked || r.getAttribute('aria-checked')
            })),
            buttons: Array.from(document.querySelectorAll('button')).map(b => ({
                text: b.innerText.trim(),
                disabled: b.disabled
            }))
        };
    })()`);
    console.log('Ads page info:\n', JSON.stringify(info, null, 2));

    // Select "No, my app does not contain ads"
    const selectRes = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, input[type="radio"], [role="radio"]'));
        const noRadio = radios.find(r => (r.innerText || r.parentElement?.innerText || '').toLowerCase().includes('no'));
        if (noRadio) {
            noRadio.scrollIntoView({ block: 'center' });
            noRadio.click();
            return { selected: true, text: noRadio.innerText };
        }
        return { error: 'No radio button found' };
    })()`);
    console.log('Select result:', selectRes);
    await cdp.wait(1000);

    // Click Save
    const saveRes = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn && !saveBtn.disabled) {
            saveBtn.click();
            return { saved: true };
        }
        return { saved: false, disabled: saveBtn?.disabled };
    })()`);
    console.log('Save result:', saveRes);
    await cdp.wait(3000);

    await cdp.screenshot('ads_saved.png');
    cdp.close();
}

main().catch(console.error);
