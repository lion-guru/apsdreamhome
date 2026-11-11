import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Clicking "Send changes for review" button...');
    const clickConf = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => (b.innerText || '').trim() === 'Send changes for review');
        if (target) {
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Confirmation click result:', clickConf);
    await cdp.wait(6000);

    await cdp.screenshot('in_review_confirmed.png');

    // Inspect the status after submission
    const statusAfter = await cdp.eval(`(() => {
        return {
            title: document.title,
            text: document.body.innerText.substring(0, 1500)
        };
    })()`);
    console.log('Status after submission:\n', statusAfter.text);

    cdp.close();
}

main().catch(console.error);
