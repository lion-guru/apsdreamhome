import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        const rect = nextBtn.getBoundingClientRect();
        // Check for any validation error text on page
        const errors = Array.from(document.querySelectorAll('.error, [role="alert"], [class*="error"], mat-error')).map(e => e.innerText.trim());
        return {
            btnRect: { x: rect.x, y: rect.y, w: rect.width, h: rect.height },
            errors,
            activeStep: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText
        };
    })()`);

    console.log('Button details:', info);

    // Let's click using CDP Input.dispatchMouseEvent at the center of the button!
    const { x, y, w, h } = info.btnRect;
    const cx = x + w / 2;
    const cy = y + h / 2;

    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseMoved', x: cx, y: cy });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: cx, y: cy, button: 'left', clickCount: 1 });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: cx, y: cy, button: 'left', clickCount: 1 });
    console.log('Dispatched mouse click at', cx, cy);

    await cdp.wait(4000);
    await cdp.screenshot('after_mouse_click_next.png');

    const afterClick = await cdp.eval(`(() => ({
        url: window.location.href,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('After mouse click:', afterClick);

    cdp.close();
}

main().catch(console.error);
