import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const errors = Array.from(document.querySelectorAll('.error, [role="alert"], [class*="error"], mat-error')).map(e => e.innerText.trim()).filter(Boolean);
        const inputs = Array.from(document.querySelectorAll('input[type="text"]')).map(i => ({
            val: i.value,
            ariaLabel: i.getAttribute('aria-label'),
            parentText: i.parentElement?.innerText?.substring(0, 100)
        }));
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        return {
            errors,
            inputs,
            nextDisabled: nextBtn?.disabled
        };
    })()`);

    console.log(JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
