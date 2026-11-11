import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const publishingUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/publishing`;
    console.log('Navigating to Publishing overview:', publishingUrl);
    await cdp.navigate(publishingUrl);
    await cdp.wait(4000);

    const info = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const buttons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: b.getAttribute('aria-label') || '',
            debugId: b.getAttribute('debug-id') || '',
            disabled: b.disabled,
            visible: b.offsetParent !== null
        })).filter(b => b.visible);

        const headings = Array.from(document.querySelectorAll('h1, h2, h3, [role="heading"]')).map(h => h.innerText.trim());

        return {
            headings,
            buttons: buttons.filter(b => b.text.includes('review') || b.text.includes('Send') || b.text.includes('Submit') || b.text.includes('publish') || b.text.includes('Save')),
            inReview: body.includes('Changes in review') || body.includes('In review')
        };
    })()`);

    console.log('Publishing overview state:', JSON.stringify(info, null, 2));

    // If there is a button like "Submit changes for review" or "Send changes for review", let's click it!
    const submitBtn = info.buttons.find(b => !b.disabled && (b.text.toLowerCase().includes('send') || b.text.toLowerCase().includes('submit')));
    if (submitBtn) {
        console.log('Submitting changes for review:', submitBtn);
        const clickSub = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const b = btns.find(x => !x.disabled && (x.innerText.toLowerCase().includes('send') || x.innerText.toLowerCase().includes('submit')) && x.innerText.toLowerCase().includes('review'));
            if (b) {
                b.scrollIntoView({ block: 'center' });
                b.click();
                return { clicked: true, text: b.innerText.trim() };
            }
            return { clicked: false };
        })()`);
        console.log('Click submit result:', clickSub);
        await cdp.wait(3000);

        // Confirm modal if any
        const confirmRes = await cdp.eval(`(() => {
            const modal = document.querySelector('[role="dialog"], mat-dialog-container');
            if (modal) {
                const confirmBtn = Array.from(modal.querySelectorAll('button')).find(b => {
                    const t = b.innerText.toLowerCase();
                    return t.includes('send') || t.includes('submit') || t.includes('confirm');
                });
                if (confirmBtn) {
                    confirmBtn.click();
                    return { confirmed: true, text: confirmBtn.innerText.trim() };
                }
            }
            return { noModal: true };
        })()`);
        console.log('Confirm modal result:', confirmRes);
        await cdp.wait(3000);
    }

    const finalState = await cdp.eval(`(() => {
        return {
            bodySnippet: document.body.innerText.slice(0, 300).replace(/\\n/g, ' ')
        };
    })()`);

    console.log('Final state:', finalState);
    cdp.close();
}

main().catch(console.error);
