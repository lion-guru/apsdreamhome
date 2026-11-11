import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled,
            aria: b.getAttribute('aria-label') || ''
        }));
        return {
            url: window.location.href,
            buttons: btns.filter(b => b.text.length > 0 || b.aria.length > 0)
        };
    })()`);
    console.log('Current state:', JSON.stringify(info, null, 2));

    // Find "Save and publish to internal testers" or "Save and publish"
    const clickResult = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const pubBtn = btns.find(b => !b.disabled && (
            b.innerText.includes('Save and publish') ||
            b.innerText.includes('Publish to internal testers') ||
            b.innerText.includes('Save')
        ));
        if (pubBtn) {
            pubBtn.scrollIntoView({ block: 'center' });
            pubBtn.click();
            return { clicked: true, text: pubBtn.innerText.trim() };
        }
        return { clicked: false, available: btns.map(b => b.innerText.trim()).filter(Boolean) };
    })()`);
    console.log('Click result:', clickResult);

    await cdp.wait(3000);

    // If modal appears, confirm
    const modalResult = await cdp.eval(`(() => {
        const dialog = document.querySelector('mat-dialog-container, [role="dialog"]');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button'));
            const confirmBtn = btns.find(b => !b.disabled && (
                b.innerText.includes('Publish') ||
                b.innerText.includes('Save') ||
                b.innerText.includes('Confirm')
            ));
            if (confirmBtn) {
                confirmBtn.click();
                return { confirmed: true, text: confirmBtn.innerText.trim() };
            }
        }
        return { dialog: !!dialog };
    })()`);
    console.log('Modal result:', modalResult);

    await cdp.wait(4000);
    await cdp.screenshot('v5_published_result.png');
    console.log('Saved screenshot v5_published_result.png');

    cdp.close();
}

main().catch(console.error);
