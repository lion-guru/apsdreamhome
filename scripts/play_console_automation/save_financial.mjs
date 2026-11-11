import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Select "My app doesn't provide any financial features"
    const selectNo = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"], label'));
        const target = cbs.find(c => (c.innerText || '').includes("doesn't provide any financial features"));
        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { error: 'Target checkbox not found' };
    })()`);
    console.log('Selected option:', selectNo);
    await cdp.wait(1000);

    // Click Next
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next not found' };
    })()`);
    console.log('Clicked Next:', clickNext);
    await cdp.wait(3000);

    // Click Save if present, or check Step 2
    const clickSave = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => b.innerText.trim() === 'Save' && !b.disabled);
        if (saveBtn) {
            saveBtn.click();
            return { action: 'saved' };
        }
        return { action: 'no_save_yet', buttons: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Save action:', clickSave);
    await cdp.wait(3000);

    await cdp.screenshot('financial_saved.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
