import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const pubUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/publishing`;
    console.log('Navigating to publishing overview:', pubUrl);
    await cdp.navigate(pubUrl);
    await cdp.wait(5000);

    const pubState = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            disabled: b.disabled
        })).filter(b => b.text.length > 0);

        return {
            title: document.title,
            btns,
            snippet: text.substring(0, 1800)
        };
    })()`);

    console.log('Publishing Overview State:\n', JSON.stringify(pubState, null, 2));
    await cdp.screenshot('publishing_overview_ready_for_review.png');

    // Look for "Send app for review" or "Send changes for review" button
    const clickSubmit = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => {
            const t = (b.innerText || '').trim();
            return t.includes('Send app for review') || t.includes('Send') && t.includes('review') || t === 'Submit for review';
        });

        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Click Submit for review result:', clickSubmit);
    await cdp.wait(4000);

    // If confirmation modal appears ("Send changes for review?"), confirm it
    const confReview = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
                const r = b.getBoundingClientRect();
                return r.width > 0 && r.height > 0 && !b.disabled;
            });
            const conf = btns.find(b => {
                const t = (b.innerText || '').trim();
                return t.includes('Send') || t === 'Submit' || t === 'Confirm';
            });
            if (conf) {
                conf.click();
                return { confirmed: true, text: conf.innerText.trim() };
            }
        }
        return { noDialog: true };
    })()`);
    console.log('Confirmation modal result:', confReview);
    await cdp.wait(6000);

    await cdp.screenshot('final_app_submitted_for_review.png');

    // Check post-submission dashboard and publishing overview text
    const finalStatus = await cdp.eval(`(() => {
        return {
            text: document.body.innerText.substring(0, 1500)
        };
    })()`);
    console.log('Final page text:\n', finalStatus.text);

    cdp.close();
}

main().catch(console.error);
