import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Clicking "Send changes for review"...');
    const res = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button')).filter(b => !b.disabled);
            const conf = btns.find(b => (b.innerText || '').includes('Send') || (b.innerText || '').includes('Submit'));
            if (conf) {
                conf.click();
                return { confirmed: true, text: conf.innerText.trim() };
            }
        }
        // Fallback on whole document
        const btns = Array.from(document.querySelectorAll('button')).filter(b => !b.disabled);
        const conf = btns.find(b => (b.innerText || '').trim() === 'Send changes for review');
        if (conf) {
            conf.click();
            return { confirmedDoc: true, text: conf.innerText.trim() };
        }
        return { notFound: true };
    })()`);

    console.log('Confirm result:', res);
    await cdp.wait(5000);

    await cdp.screenshot('all_changes_in_review_confirmed.png');

    const text = await cdp.eval(`document.body.innerText.substring(0, 1000)`);
    console.log('Final State:\n', text);

    cdp.close();
}

main().catch(console.error);
