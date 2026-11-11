import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Look for modal and click Save and publish
    const clickRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('mat-dialog-container, [role="dialog"]');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button'));
            const btn = btns.find(b => (b.innerText || '').includes('Save and publish'));
            if (btn) {
                btn.click();
                return { clicked: true, text: btn.innerText.trim() };
            }
            return { error: 'Button not found in dialog', buttons: btns.map(b => b.innerText) };
        }
        return { error: 'Dialog not found' };
    })()`);

    console.log('Dialog click result:', clickRes);
    await cdp.wait(5000);

    const state = await cdp.eval(`(() => {
        return {
            url: window.location.href,
            bodySnippet: document.body.innerText.substring(0, 500)
        };
    })()`);
    console.log('Current state:', state);

    await cdp.screenshot('v4_published_track.png');
    cdp.close();
}

main().catch(console.error);
