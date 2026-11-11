import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Check "Collected"
    const clickCollected = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"], label'));
        const collected = cbs.find(c => (c.innerText || '').startsWith('Collected'));
        if (collected) {
            collected.scrollIntoView({ block: 'center' });
            collected.click();
            return { clicked: true };
        }
        return { error: 'Collected checkbox not found' };
    })()`);
    console.log('Checked Collected:', clickCollected);
    await cdp.wait(1500);

    // 2. Select:
    // - Ephemerally processed: Yes
    // - Data collection is optional (users can choose)
    // - Purpose: App functionality
    const fillDetails = await cdp.eval(`(() => {
        const res = {};
        // Ephemeral: Yes
        const ephemeralYes = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).find(r => (r.innerText || '').trim() === 'Yes');
        if (ephemeralYes) {
            ephemeralYes.click();
            res.ephemeral = true;
        }

        // Users can choose
        const optionalRadio = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).find(r => (r.innerText || '').includes('Users can choose') || (r.innerText || '').includes('optional'));
        if (optionalRadio) {
            optionalRadio.click();
            res.optional = true;
        }

        // Purpose: App functionality
        const appFuncCb = Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"]')).find(c => (c.innerText || '').includes('App functionality'));
        if (appFuncCb) {
            appFuncCb.click();
            res.appFunctionality = true;
        }

        return res;
    })()`);
    console.log('Filled details:', fillDetails);
    await cdp.wait(1500);

    // 3. Click Save inside the drawer
    const clickDrawerSave = await cdp.eval(`(() => {
        const saveBtns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.trim() === 'Save');
        // The drawer Save button is usually the last one
        const activeSave = saveBtns.find(b => !b.disabled && b.offsetParent !== null);
        if (activeSave) {
            activeSave.click();
            return { clicked: true, text: activeSave.innerText.trim() };
        }
        return { error: 'Drawer Save not found' };
    })()`);
    console.log('Clicked Drawer Save:', clickDrawerSave);
    await cdp.wait(3000);

    // 4. Click Next on main page
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next button not clickable' };
    })()`);
    console.log('Clicked Next to Step 5 (Preview):', clickNext);
    await cdp.wait(4000);

    await cdp.screenshot('data_safety_step5_preview.png');

    // 5. Click Save on Preview
    const clickSubmit = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save' && !b.disabled);
        if (saveBtn) {
            saveBtn.scrollIntoView({ block: 'center' });
            saveBtn.click();
            return { submitted: true };
        }
        return { error: 'Final Save button not found' };
    })()`);
    console.log('Final Save result:', clickSubmit);
    await cdp.wait(4000);

    await cdp.screenshot('data_safety_final_submitted.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
