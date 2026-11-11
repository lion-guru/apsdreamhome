import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/health`);
    await cdp.wait(3000);

    // Select "My app does not have any health features"
    const selectRes = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"], label'));
        const noCb = cbs.find(c => (c.innerText || '').includes('does not have any health features'));
        if (noCb) {
            noCb.scrollIntoView({ block: 'center' });
            noCb.click();
            return { clicked: true };
        }
        return { error: 'Not found' };
    })()`);
    console.log('Selected No health features:', selectRes);
    await cdp.wait(1000);

    // Click Next
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next button not clickable' };
    })()`);
    console.log('Clicked Next:', clickNext);
    await cdp.wait(3000);

    // Click Save on Step 2
    const clickSave = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save' && !b.disabled);
        if (saveBtn) {
            saveBtn.click();
            return { saved: true };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Clicked Save on Step 2:', clickSave);
    await cdp.wait(3000);

    await cdp.screenshot('health_saved.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
