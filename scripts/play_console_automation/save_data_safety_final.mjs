import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Save on Step 5 (Preview)
    const saveRes = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save' && !b.disabled);
        if (saveBtn) {
            saveBtn.scrollIntoView({ block: 'center' });
            saveBtn.click();
            return { saved: true };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Clicked Save on Preview:', saveRes);
    await cdp.wait(4000);

    await cdp.screenshot('data_safety_done_saved.png');

    // Return to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
