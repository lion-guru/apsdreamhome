import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Clicking "Edit email list Internal Testers"...');
    const clickEdit = await cdp.eval(`(() => {
        const btn = document.querySelector('button[aria-label="Edit email list Internal Testers"], [aria-label*="Internal Testers"]');
        if (btn) {
            btn.scrollIntoView({ block: 'center' });
            btn.click();
            return { clicked: true, ariaLabel: btn.getAttribute('aria-label') };
        }
        return { clicked: false };
    })()`);

    console.log('Click Edit result:', clickEdit);
    await cdp.wait(3000);

    // Inspect the opened email list editor / modal / page
    const editorState = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], mat-dialog-container');
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

        const textSnippet = (dialog ? dialog.innerText : document.body.innerText).slice(0, 800).replace(/\\n/g, ' ');

        return { inputs, buttons, textSnippet };
    })()`);

    console.log('Editor State:', JSON.stringify(editorState, null, 2));
    cdp.close();
}

main().catch(console.error);
