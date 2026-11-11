import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const details = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        if (!dialog) return { error: 'No dialog' };

        const rows = Array.from(dialog.querySelectorAll('div, tr, li, [role="row"]')).map(el => {
            const hasDelete = el.querySelector('button[aria-label*="Remove"]');
            if (!hasDelete) return null;
            return {
                text: el.innerText.trim().replace(/\\n/g, ' -- '),
                ariaLabel: hasDelete.getAttribute('aria-label')
            };
        }).filter(Boolean);

        const errorElements = Array.from(dialog.querySelectorAll('.error-message, mat-error, [role="alert"], [aria-invalid="true"], .error, [class*="error"]')).map(e => ({
            tag: e.tagName,
            className: e.className,
            text: e.innerText.trim()
        }));

        const bottomErrors = Array.from(dialog.querySelectorAll('footer, [class*="footer"], [class*="action"], [class*="bottom"]')).map(f => f.innerText.trim());

        return { rows, errorElements, bottomText: dialog.innerText.slice(-300) };
    })()`);

    console.log('Dialog Details:', JSON.stringify(details, null, 2));
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
