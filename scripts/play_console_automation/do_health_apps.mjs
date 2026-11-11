import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click button 2 (Health apps)
    const clickBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.includes('Start declaration'));
        if (btns.length > 2) {
            btns[2].scrollIntoView({ block: 'center' });
            btns[2].click();
            return { clicked: true, text: btns[2].innerText.trim() };
        }
        return { error: 'Button 2 not found' };
    })()`);
    console.log('Clicked Button 2 (Health apps):', clickBtn);
    await cdp.wait(3000);

    const info = await cdp.eval(`({
        url: window.location.href,
        title: document.title,
        radios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
            text: r.innerText.trim(),
            checked: r.getAttribute('aria-checked')
        })),
        checkboxes: Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"]')).map(c => ({
            text: c.innerText.trim(),
            checked: c.getAttribute('aria-checked')
        })),
        bodySnippet: document.body.innerText.substring(0, 800)
    })`);
    console.log('Opened page:\n', JSON.stringify(info, null, 2));

    // Select "My app doesn't have any health features" or "No"
    const clickNo = await cdp.eval(`(() => {
        const items = Array.from(document.querySelectorAll('material-radio, material-checkbox, [role="radio"], [role="checkbox"], label'));
        const noItem = items.find(r => (r.innerText || '').includes("doesn't") || (r.innerText || '').includes('does not') || (r.innerText || '').trim() === 'No');
        if (noItem) {
            noItem.scrollIntoView({ block: 'center' });
            noItem.click();
            return { clicked: true, text: noItem.innerText.trim() };
        }
        return { error: 'No item not found' };
    })()`);
    console.log('Selected No health features:', clickNo);
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

    await cdp.screenshot('health_apps_done.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
