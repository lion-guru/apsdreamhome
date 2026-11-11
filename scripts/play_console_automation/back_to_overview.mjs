import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Cancel on modal if present
    await cdp.eval(`(() => {
        const cancelBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Cancel');
        if (cancelBtn) cancelBtn.click();
    })()`);
    await cdp.wait(1000);

    // Navigate to App Content overview
    const overviewUrl = `https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/app-content/overview`;
    await cdp.navigate(overviewUrl);
    await cdp.wait(3000);

    console.log('Navigated back to overview. Title:', await cdp.eval('document.title'));
    await cdp.screenshot('back_to_overview.png');
    cdp.close();
}

main().catch(console.error);
