import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click edit if not open
    const openRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (dialog) return { alreadyOpen: true };
        const btn = document.querySelector('button[aria-label*="Internal Testers"], [aria-label="Edit email list Internal Testers"]');
        if (btn) {
            btn.click();
            return { clicked: true, aria: btn.getAttribute('aria-label') };
        }
        return { notFound: true };
    })()`);
    console.log('Open Dialog:', openRes);
    await cdp.wait(3000);

    // 2. Dump dialog elements
    const dump = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
        if (!dialog) return { noDialog: true };
        const inputs = Array.from(dialog.querySelectorAll('input, textarea')).map(i => ({
            tag: i.tagName,
            type: i.type,
            placeholder: i.placeholder,
            ariaLabel: i.getAttribute('aria-label'),
            debugId: i.getAttribute('debug-id'),
            classes: i.className,
            outer: i.outerHTML
        }));
        const buttons = Array.from(dialog.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            aria: b.getAttribute('aria-label'),
            disabled: b.disabled
        }));
        return {
            text: dialog.innerText,
            inputs,
            buttons
        };
    })()`);

    console.log('Dialog Dump:\n', JSON.stringify(dump, null, 2));
    await cdp.screenshot('dialog_inspect.png');

    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
