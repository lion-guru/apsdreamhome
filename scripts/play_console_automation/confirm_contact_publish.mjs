import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Save and publish in dialog
    const modalBtn = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .acx-overlay-container');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button'));
            const btn = btns.find(b => (b.innerText || '').includes('Save and publish'));
            if (btn) {
                btn.click();
                return { clicked: true, text: btn.innerText.trim() };
            }
        }
        return { error: 'Dialog button not found' };
    })()`);
    console.log('Dialog click:', modalBtn);

    // Fallback: mouse click at coordinates (1136, 570)
    if (modalBtn?.error) {
        console.log('Using mouse click at (1136, 570)');
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mousePressed',
            x: 1136,
            y: 570,
            button: 'left',
            clickCount: 1
        });
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mouseReleased',
            x: 1136,
            y: 570,
            button: 'left',
            clickCount: 1
        });
    }

    await cdp.wait(4000);
    await cdp.screenshot('contact_published_final.png');

    cdp.close();
}

main().catch(console.error);
