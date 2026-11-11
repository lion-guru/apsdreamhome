import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const trackId = '4700085989411864804';
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/${trackId}`);
    await cdp.wait(5000);

    // Click "Select countries" task row
    const clickTask = await cdp.eval(`(() => {
        const rows = Array.from(document.querySelectorAll('div, a, button, [role="button"]'));
        const row = rows.find(el => (el.innerText || '').trim().startsWith('Select countries'));
        if (row) {
            row.click();
            return { clicked: true, text: row.innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Click Select countries row:', clickTask);
    await cdp.wait(4000);

    // Let's see if tab changed or modal appeared
    const state = await cdp.eval(`(() => {
        return {
            url: window.location.href,
            textSnippet: document.body.innerText.substring(0, 1000)
        };
    })()`);
    console.log('Current state after clicking:', state);
    await cdp.screenshot('countries_after_row_click.png');

    // Look for "Add countries / regions" link/button
    const addBtnRes = await cdp.eval(`(() => {
        const all = Array.from(document.querySelectorAll('button, a, [role="button"]'));
        const btn = all.find(b => (b.innerText || '').includes('Add countries') || (b.innerText || '').includes('Add country'));
        if (btn) {
            btn.click();
            return { clickedAdd: true, text: btn.innerText.trim() };
        }
        return { notFoundAdd: true, candidates: all.filter(b => b.getBoundingClientRect().width > 0).map(b => b.innerText.trim()).slice(0, 15) };
    })()`);
    console.log('Add button result:', addBtnRes);
    await cdp.wait(3000);

    await cdp.screenshot('countries_modal_check.png');

    cdp.close();
}

main().catch(console.error);
