import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click button 1 (Government apps)
    const clickBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.includes('Start declaration'));
        if (btns.length > 1) {
            btns[1].scrollIntoView({ block: 'center' });
            btns[1].click();
            return { clicked: true, text: btns[1].innerText.trim() };
        }
        return { error: 'Button 1 not found' };
    })()`);
    console.log('Clicked Button 1 (Government apps):', clickBtn);
    await cdp.wait(3000);

    const info = await cdp.eval(`({
        url: window.location.href,
        title: document.title,
        radios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
            text: r.innerText.trim(),
            checked: r.getAttribute('aria-checked')
        }))
    })`);
    console.log('Opened page:\n', JSON.stringify(info, null, 2));

    // Select "No"
    const clickNo = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"]'));
        const noRadio = radios.find(r => (r.innerText || '').trim().startsWith('No'));
        if (noRadio) {
            noRadio.scrollIntoView({ block: 'center' });
            noRadio.click();
            return { clicked: true, text: noRadio.innerText.trim() };
        }
        return { error: 'No radio not found' };
    })()`);
    console.log('Selected No:', clickNo);
    await cdp.wait(1000);

    // Save
    const clickSave = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { saved: true, disabled: saveBtn.disabled };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Saved:', clickSave);
    await cdp.wait(3000);

    await cdp.screenshot('gov_apps_done.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
