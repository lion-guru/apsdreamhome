import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Clicking the exact arrow button for Edit email list Internal Testers...');
    const clickRes = await cdp.eval(`(() => {
        const btn = document.querySelector('button[aria-label="Edit email list Internal Testers"]');
        if (btn) {
            btn.scrollIntoView({ block: 'center' });
            btn.click();
            return { clicked: true, text: btn.innerText.trim() };
        }
        return { clicked: false };
    })()`);

    console.log('Click result:', clickRes);
    await cdp.wait(3000);

    const dialogState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container, aside');
        const inputs = Array.from(document.querySelectorAll('input, textarea')).map(i => ({
            tag: i.tagName,
            type: i.type,
            placeholder: i.placeholder,
            value: i.value,
            ariaLabel: i.getAttribute('aria-label') || ''
        }));

        const buttons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: b.getAttribute('aria-label') || '',
            disabled: b.disabled,
            visible: b.offsetParent !== null
        })).filter(b => b.visible && b.text.length < 30);

        return {
            hasDialog: !!dialog,
            dialogText: dialog ? dialog.innerText.slice(0, 500).replace(/\\n/g, ' ') : null,
            inputs,
            buttons
        };
    })()`);

    console.log('Dialog state:', JSON.stringify(dialogState, null, 2));
    cdp.close();
}

main().catch(console.error);
