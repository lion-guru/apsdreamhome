import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Select "18 and over"
    const clickAge = await cdp.eval(`(() => {
        const labels = Array.from(document.querySelectorAll('label, material-checkbox, [role="checkbox"]'));
        const target = labels.find(l => (l.innerText || '').includes('18 and over'));
        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { error: '18 and over checkbox not found' };
    })()`);
    console.log('Selected age group:', clickAge);
    await cdp.wait(1000);

    // Click Next
    const clickNext1 = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        if (nextBtn && !nextBtn.disabled) {
            nextBtn.click();
            return { clicked: true };
        }
        return { clicked: false, disabled: nextBtn?.disabled };
    })()`);
    console.log('Clicked Next 1:', clickNext1);
    await cdp.wait(3000);

    // Question: Could your store listing appeal to children? -> Select "No"
    const clickAppealNo = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"], label'));
        const noRadio = radios.find(r => (r.innerText || '').trim().startsWith('No'));
        if (noRadio) {
            noRadio.scrollIntoView({ block: 'center' });
            noRadio.click();
            return { clicked: true, text: noRadio.innerText.trim() };
        }
        return { none: true };
    })()`);
    console.log('Appeals to children (No):', clickAppealNo);
    await cdp.wait(1000);

    // Click Next again if present, or Save
    const clickNextOrSave = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const nextBtn = btns.find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.click();
            return { action: 'clicked_next' };
        }
        const saveBtn = btns.find(b => b.innerText.trim() === 'Save' && !b.disabled);
        if (saveBtn) {
            saveBtn.click();
            return { action: 'clicked_save' };
        }
        return { error: 'No active Next or Save button' };
    })()`);
    console.log('Next or Save action:', clickNextOrSave);
    await cdp.wait(3000);

    // Check if on Summary step needing Save
    const finalSave = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save' && !b.disabled);
        if (saveBtn) {
            saveBtn.click();
            return { saved: true };
        }
        return { saveNeeded: false };
    })()`);
    console.log('Final Save result:', finalSave);
    await cdp.wait(3000);

    await cdp.screenshot('target_audience_saved.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
