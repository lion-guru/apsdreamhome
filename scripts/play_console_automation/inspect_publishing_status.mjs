import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.wait(3000);

    const info = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const buttons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            disabled: b.disabled,
            visible: b.offsetParent !== null
        })).filter(b => b.visible);

        const headings = Array.from(document.querySelectorAll('h1, h2, h3, [role="heading"]')).map(h => h.innerText.trim());

        return {
            headings,
            buttons: buttons.filter(b => b.text.toLowerCase().includes('review') || b.text.toLowerCase().includes('send') || b.text.toLowerCase().includes('submit')),
            statusSnippet: body.slice(0, 1000).replace(/\\n/g, ' ')
        };
    })()`);

    console.log('Publishing Status:', JSON.stringify(info, null, 2));

    const submitBtn = info.buttons.find(b => !b.disabled && (b.text.toLowerCase().includes('send') || b.text.toLowerCase().includes('submit')));
    if (submitBtn) {
        console.log('Found active submit button:', submitBtn);
        const res = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const b = btns.find(x => !x.disabled && (x.innerText.toLowerCase().includes('send') || x.innerText.toLowerCase().includes('submit')) && x.innerText.toLowerCase().includes('review'));
            if (b) {
                b.click();
                return true;
            }
            return false;
        })()`);
        console.log('Clicked submit:', res);
        await cdp.wait(3000);

        // Confirm in modal dialog
        await cdp.eval(`(() => {
            const modal = document.querySelector('[role="dialog"], mat-dialog-container');
            if (modal) {
                const btn = Array.from(modal.querySelectorAll('button')).find(b => {
                    const t = b.innerText.toLowerCase();
                    return t.includes('send') || t.includes('submit');
                });
                if (btn) btn.click();
            }
        })()`);
        console.log('Confirmed modal dialog.');
        await cdp.wait(3000);
    }

    cdp.close();
}

main().catch(console.error);
