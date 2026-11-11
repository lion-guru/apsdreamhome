import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "Go to overview" in dialog
    const clickOverview = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button'));
            const btn = btns.find(b => (b.innerText || '').includes('Go to overview') || (b.innerText || '').includes('overview'));
            if (btn) {
                btn.click();
                return { clicked: true, text: btn.innerText.trim() };
            }
        }
        return { notFound: true };
    })()`);
    console.log('Click Go to overview result:', clickOverview);
    await cdp.wait(5000);

    // If on publishing page, check if any change needs to be submitted
    const curUrl = await cdp.eval(`window.location.href`);
    console.log('Current URL:', curUrl);

    if (!curUrl.includes('/publishing')) {
        await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/publishing`);
        await cdp.wait(5000);
    }

    // Inspect submit button
    const submitCheck = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && (b.innerText || '').includes('review');
        });

        if (btns.length > 0) {
            return {
                text: btns[0].innerText.trim(),
                disabled: btns[0].disabled
            };
        }
        return { notFound: true };
    })()`);
    console.log('Submit button on Publishing overview:', submitCheck);

    if (submitCheck && !submitCheck.disabled && !submitCheck.notFound) {
        console.log('Submitting tester change for review...');
        await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('review') && !b.disabled);
            if (btns.length > 0) btns[0].click();
        })()`);
        await cdp.wait(3000);

        // Confirm modal
        await cdp.eval(`(() => {
            const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
            if (dialog) {
                const btns = Array.from(dialog.querySelectorAll('button')).filter(b => !b.disabled);
                const conf = btns.find(b => (b.innerText || '').includes('Send') || (b.innerText || '').includes('Submit'));
                if (conf) conf.click();
            }
        })()`);
        await cdp.wait(5000);
    }

    await cdp.screenshot('final_publishing_overview_all_in_review.png');

    const finalSnippet = await cdp.eval(`document.body.innerText.substring(0, 1500)`);
    console.log('Final Overview State:\n', finalSnippet);

    cdp.close();
}

main().catch(console.error);
