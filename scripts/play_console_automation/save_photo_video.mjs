import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const desc = "APS Dream Home requires users to upload KYC verification documents, customer booking receipts, and land registry photos directly for associate identity verification and offline plot management workflows.";

    const fillRes = await cdp.eval(`(() => {
        const textarea = document.querySelector('textarea');
        if (!textarea) return { error: 'Textarea not found' };

        textarea.focus();
        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, 'value').set;
        nativeSet.call(textarea, ${JSON.stringify(desc)});
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.dispatchEvent(new Event('change', { bubbles: true }));

        return {
            filled: true,
            length: textarea.value.length,
            val: textarea.value
        };
    })()`);

    console.log('Fill result:', fillRes);
    await cdp.wait(2000);

    // Click Save
    const saveRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => b.innerText.trim() === 'Save');
        if (saveBtn) {
            saveBtn.scrollIntoView({ block: 'center' });
            saveBtn.click();
            return { clicked: true, disabled: saveBtn.disabled };
        }
        return { error: 'Save button not found' };
    })()`);

    console.log('Save button click:', saveRes);
    await cdp.wait(5000);

    await cdp.screenshot('photo_video_saved.png');

    // Go back to overview
    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/app-content/overview');
    await cdp.wait(5000);

    const finalStatus = await cdp.eval(`(() => {
        const text = document.body.innerText;
        return {
            hasNeedAttention: text.includes('Need attention'),
            snippet: text.substring(0, 800)
        };
    })()`);

    console.log('Final Overview Status:', finalStatus);
    await cdp.screenshot('all_declarations_complete.png');

    cdp.close();
}

main().catch(console.error);
