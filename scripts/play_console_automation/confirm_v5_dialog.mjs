import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const dialogInfo = await cdp.eval(`(() => {
        const dialog = document.querySelector('mat-dialog-container, [role="dialog"]');
        if (!dialog) return { noDialog: true };
        const btns = Array.from(dialog.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled
        }));
        return {
            text: dialog.innerText.substring(0, 400),
            buttons: btns
        };
    })()`);
    console.log('Dialog:', JSON.stringify(dialogInfo, null, 2));

    const allButtons = await cdp.eval(`(() => {
        return Array.from(document.querySelectorAll('button')).map((b, idx) => ({
            idx,
            text: b.innerText.trim(),
            aria: b.getAttribute('aria-label') || '',
            disabled: b.disabled,
            visible: b.offsetParent !== null
        })).filter(b => b.text.length > 0 || b.aria.length > 0);
    })()`);
    console.log('All visible buttons:', JSON.stringify(allButtons, null, 2));

    const confirmResult = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        // Find button that contains Publish or Save, preferably inside a dialog or overlay
        const dialogBtns = Array.from(document.querySelectorAll('mat-dialog-container button, [role="dialog"] button, .cdk-overlay-pane button'));
        let target = dialogBtns.find(b => !b.disabled && (
            b.innerText.toLowerCase().includes('publish') ||
            b.innerText.toLowerCase().includes('save') ||
            b.innerText.toLowerCase().includes('confirm')
        ));
        if (!target) {
            target = btns.find(b => !b.disabled && (
                b.innerText.toLowerCase() === 'publish' ||
                b.innerText.toLowerCase().includes('save and publish')
            ));
        }
        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Confirm:', confirmResult);

    await cdp.wait(5000);
    await cdp.screenshot('v5_live_published.png');
    console.log('Saved screenshot v5_live_published.png');

    const finalState = await cdp.eval(`(() => ({
        url: window.location.href,
        title: document.title,
        heading: document.querySelector('h1')?.innerText || ''
    }))()`);
    console.log('Final page state:', finalState);

    cdp.close();
}

main().catch(console.error);
