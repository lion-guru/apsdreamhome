import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Start declaration for Advertising ID
    const clickRes = await cdp.eval(`(() => {
        // Find heading 'Advertising ID'
        const headings = Array.from(document.querySelectorAll('h3, h2, [role="heading"]'));
        const h = headings.find(el => el.innerText.trim() === 'Advertising ID');
        if (!h) return { error: 'Advertising ID heading not found' };

        let p = h.parentElement;
        for (let i = 0; i < 6 && p; i++) {
            const btn = p.querySelector('button');
            if (btn && btn.innerText.includes('declaration')) {
                btn.scrollIntoView({ block: 'center' });
                btn.click();
                return { clicked: true, text: btn.innerText.trim() };
            }
            p = p.parentElement;
        }
        return { error: 'Button not found' };
    })()`);
    console.log('Clicked Advertising ID:', clickRes);
    await cdp.wait(3000);

    const pageInfo = await cdp.eval(`({
        url: window.location.href,
        radios: Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
            text: r.innerText.trim(),
            checked: r.getAttribute('aria-checked')
        })),
        bodySnippet: document.body.innerText.substring(0, 800)
    })`);
    console.log('Advertising ID page:\n', JSON.stringify(pageInfo, null, 2));

    // Select "No"
    const clickNo = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"]'));
        const noRadio = radios.find(r => (r.innerText || '').trim().startsWith('No'));
        if (noRadio) {
            noRadio.scrollIntoView({ block: 'center' });
            noRadio.click();
            return { clickedNo: true };
        }
        return { error: 'No radio not found' };
    })()`);
    console.log('Selected No:', clickNo);
    await cdp.wait(1000);

    // Click Save
    const clickSave = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (saveBtn && !saveBtn.disabled) {
            saveBtn.click();
            return { saved: true };
        }
        return { error: 'Save button not clickable' };
    })()`);
    console.log('Save result:', clickSave);
    await cdp.wait(3000);

    await cdp.screenshot('ad_id_saved.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
