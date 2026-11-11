import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const clickRes = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next not found' };
    })()`);
    console.log('Scroll and click Next:', clickRes);
    await cdp.wait(5000);

    await cdp.screenshot('data_safety_step3_scrolled.png');

    const state = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        header: document.querySelector('h1, h2, h3')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('State after click:\n', JSON.stringify(state, null, 2));

    cdp.close();
}

main().catch(console.error);
