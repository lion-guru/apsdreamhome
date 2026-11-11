import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Navigate to Closed Testing testers tab
    const closedUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804?tab=testers`;
    console.log('Navigating to Closed Testing testers tab:', closedUrl);
    await cdp.navigate(closedUrl);
    await cdp.wait(4000);

    // Inspect testers list selection on closed track
    const checkState = await cdp.eval(`(() => {
        const rows = Array.from(document.querySelectorAll('tr, ess-row, [role="row"]')).map(r => r.innerText.trim().replace(/\\n/g, ' -- '));
        const checkboxes = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]')).map(cb => ({
            text: cb.innerText || cb.parentElement?.innerText || '',
            checked: cb.classList.contains('mat-mdc-checkbox-checked') || cb.checked
        }));
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        return {
            rows,
            checkboxes,
            saveDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);
    console.log('Closed track state:', JSON.stringify(checkState, null, 2));

    await cdp.screenshot('closed_track_testers_state.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
