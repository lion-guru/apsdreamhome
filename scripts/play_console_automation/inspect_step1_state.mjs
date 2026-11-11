import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const emailInput = document.querySelector('input[type="email"]');
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        return {
            url: window.location.href,
            emailVal: emailInput?.value,
            nextDisabled: nextBtn?.disabled,
            nextClasses: nextBtn?.className,
            bodyText: document.body.innerText.substring(0, 500)
        };
    })()`);

    console.log(JSON.stringify(info, null, 2));
    cdp.close();
}

main().catch(console.error);
