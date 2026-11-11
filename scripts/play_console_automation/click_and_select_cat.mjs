import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const span = Array.from(document.querySelectorAll('span')).find(s => s.className.includes('button-text') && s.innerText.trim() === 'Not selected');
        if (!span) return { error: 'Span not found' };
        
        const parent = span.parentElement;
        const grandParent = parent?.parentElement;

        return {
            span: { tag: span.tagName, class: span.className },
            parent: { tag: parent?.tagName, class: parent?.className, id: parent?.id },
            grandParent: { tag: grandParent?.tagName, class: grandParent?.className }
        };
    })()`);

    console.log('Button elements:', info);

    // Click at the center of the span: x = 568 + 151/2 = 643, y = 400 + 17/2 = 408
    console.log('Dispatching click at (643, 408)');
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mousePressed',
        x: 643,
        y: 408,
        button: 'left',
        clickCount: 1
    });
    await cdp.send('Input.dispatchMouseEvent', {
        type: 'mouseReleased',
        x: 643,
        y: 408,
        button: 'left',
        clickCount: 1
    });

    await cdp.wait(2000);
    await cdp.screenshot('after_not_selected_click.png');

    // Now look for the popup menu that appeared!
    const popupInfo = await cdp.eval(`(() => {
        const all = Array.from(document.querySelectorAll('*'));
        const matching = all.filter(el => {
            const txt = (el.innerText || '').trim();
            const cls = (typeof el.className === 'string') ? el.className : '';
            return (txt.includes('House & Home') || txt.includes('Business') || txt.includes('Productivity')) &&
                   el.children.length === 0;
        }).map(el => {
            const r = el.getBoundingClientRect();
            return {
                tag: el.tagName,
                text: el.innerText.trim(),
                x: Math.round(r.x + r.width / 2),
                y: Math.round(r.y + r.height / 2)
            };
        });
        return matching;
    })()`);

    console.log('Matching options in popup:', popupInfo);

    if (popupInfo.length > 0) {
        // Prefer House & Home if found, else Business
        const target = popupInfo.find(p => p.text === 'House & Home') || popupInfo.find(p => p.text === 'Business') || popupInfo[0];
        console.log('Clicking target option:', target);
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mousePressed',
            x: target.x,
            y: target.y,
            button: 'left',
            clickCount: 1
        });
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mouseReleased',
            x: target.x,
            y: target.y,
            button: 'left',
            clickCount: 1
        });
        await cdp.wait(2000);
        await cdp.screenshot('after_option_selected.png');

        // Click Save button in footer
        const saveRes = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const saveBtn = btns.find(b => b.innerText.trim() === 'Save');
            if (saveBtn) {
                saveBtn.click();
                return { clicked: true };
            }
            return { error: 'Save not found' };
        })()`);
        console.log('Save result:', saveRes);
        await cdp.wait(3000);
        await cdp.screenshot('category_saved_success.png');
    }

    cdp.close();
}

main().catch(console.error);
