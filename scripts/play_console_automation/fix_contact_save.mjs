import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click Edit under Store listing contact details
    const clickEdit = await cdp.eval(`(() => {
        const allEdits = Array.from(document.querySelectorAll('button, a')).filter(b => b.innerText.trim() === 'Edit');
        if (allEdits.length > 1) {
            allEdits[1].click();
            return { clicked: true, index: 1 };
        } else if (allEdits.length === 1) {
            allEdits[0].click();
            return { clicked: true, index: 0 };
        }
        return { error: 'No Edit button' };
    })()`);
    console.log('Clicked Edit:', clickEdit);
    await cdp.wait(2000);

    // 2. Type email into first input
    const email = 'techguruabhay@gmail.com';
    const fillEmail = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="email"]')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 100 && r.height > 20;
        });

        if (inputs.length === 0) return { error: 'No input found' };
        
        const input = inputs[0];
        input.focus();
        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        nativeSet.call(input, ${JSON.stringify(email)});
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        return { filled: true, val: input.value };
    })()`);
    console.log('Fill email:', fillEmail);
    await cdp.wait(1500);

    // 3. Inspect all buttons inside the modal
    const modalButtons = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).map(b => {
            const r = b.getBoundingClientRect();
            return {
                text: b.innerText.trim(),
                disabled: b.disabled,
                rect: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) }
            };
        });
        return btns.filter(b => b.rect.w > 0 && b.rect.h > 0 && (b.text.includes('Save') || b.text.includes('Discard')));
    })()`);
    console.log('Modal buttons:', modalButtons);

    // 4. Click the Save / Save and publish button
    const targetBtn = modalButtons.find(b => b.text.includes('Save') && !b.disabled);
    if (targetBtn) {
        console.log('Clicking target save button:', targetBtn);
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mousePressed',
            x: targetBtn.rect.x + targetBtn.rect.width / 2,
            y: targetBtn.rect.y + targetBtn.rect.height / 2,
            button: 'left',
            clickCount: 1
        });
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mouseReleased',
            x: targetBtn.rect.x + targetBtn.rect.width / 2,
            y: targetBtn.rect.y + targetBtn.rect.height / 2,
            button: 'left',
            clickCount: 1
        });
        await cdp.wait(3000);
    }

    await cdp.screenshot('contact_saved_verified.png');
    cdp.close();
}

main().catch(console.error);
