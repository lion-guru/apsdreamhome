import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const all = Array.from(document.querySelectorAll('*'));
        const notSel = all.filter(el => (el.innerText || '').trim() === 'Not selected');
        return notSel.map(el => ({
            tagName: el.tagName,
            className: el.className,
            attributes: Array.from(el.attributes).map(a => a.name + '=' + a.value),
            parentTag: el.parentElement?.tagName,
            rect: el.getBoundingClientRect()
        }));
    })()`);

    console.log('Not selected elements:', JSON.stringify(info, null, 2));

    if (info.length > 0) {
        const target = info[0];
        const clickX = target.rect.x + target.rect.width / 2;
        const clickY = target.rect.y + target.rect.height / 2;
        console.log(`Clicking Not selected at (${clickX}, ${clickY})`);

        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mousePressed',
            x: clickX,
            y: clickY,
            button: 'left',
            clickCount: 1
        });
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mouseReleased',
            x: clickX,
            y: clickY,
            button: 'left',
            clickCount: 1
        });
        console.log('Clicked Not selected.');
        await cdp.wait(2000);
        await cdp.screenshot('dropdown_opened.png');

        // Check options
        const opts = await cdp.eval(`(() => {
            const listItems = Array.from(document.querySelectorAll('mat-option, [role="option"], .mat-option, li, .popup *'));
            return listItems.map(o => o.innerText.trim()).filter(t => t.length > 2 && t.length < 30).slice(0, 30);
        })()`);
        console.log('Dropdown options:', opts);
    }

    cdp.close();
}

main().catch(console.error);
