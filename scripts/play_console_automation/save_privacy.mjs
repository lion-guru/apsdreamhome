import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const saveRes = await cdp.eval(`(() => {
        const input = document.querySelector('input[aria-label="Privacy policy URL"]') || document.querySelector('input[type="text"]');
        if (!input) return { error: 'Privacy policy URL input not found' };

        input.focus();
        input.value = 'https://apsdreamhome.com/privacy';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        // Find Save button
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        if (!saveBtn) return { error: 'Save button not found' };

        saveBtn.click();
        return { success: true, value: input.value, btnDisabled: saveBtn.disabled };
    })()`);

    console.log('Saved privacy policy:', saveRes);
    await cdp.wait(3000);

    await cdp.screenshot('privacy_policy_saved.png');

    // Navigate back to overview
    const overviewUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`;
    await cdp.navigate(overviewUrl);

    cdp.close();
}

main().catch(console.error);
