import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const prepUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804/releases/1/prepare`;
    console.log('Navigating to prepare URL:', prepUrl);
    await cdp.navigate(prepUrl);
    await cdp.wait(5000);

    // 1. Click Add from library
    console.log('Clicking Add from library...');
    await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const target = btns.find(b => (b.innerText || '').includes('Add from library'));
        if (target) target.click();
    })()`);
    await cdp.wait(3000);

    // 2. Inspect element at (211, 188)
    const elInfo = await cdp.eval(`(() => {
        const el = document.elementFromPoint(211, 188);
        return {
            tag: el ? el.tagName : null,
            cls: el ? el.className : null,
            text: el ? el.innerText : null,
            parent: el && el.parentElement ? el.parentElement.tagName : null
        };
    })()`);
    console.log('Element at (211, 188):', elInfo);

    // Click at (211, 188) via CDP mouse events
    console.log('Dispatching click at (211, 188)...');
    await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: 211, y: 188, button: 'left', clickCount: 1 });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: 211, y: 188, button: 'left', clickCount: 1 });
    await cdp.wait(2000);
    await cdp.screenshot('after_point_click_checkbox.png');

    // 3. Inspect Add to release button at (1365, 708)
    const btnInfo = await cdp.eval(`(() => {
        const el = document.elementFromPoint(1365, 708);
        return {
            tag: el ? el.tagName : null,
            text: el ? el.innerText : null,
            disabled: el ? el.disabled : null
        };
    })()`);
    console.log('Button at (1365, 708):', btnInfo);

    // Click Add to release button at (1365, 708)
    console.log('Dispatching click at (1365, 708)...');
    await cdp.send('Input.dispatchMouseEvent', { type: 'mousePressed', x: 1365, y: 708, button: 'left', clickCount: 1 });
    await cdp.send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: 1365, y: 708, button: 'left', clickCount: 1 });
    await cdp.wait(4000);
    await cdp.screenshot('after_bundle_added_to_prepare.png');

    // 4. Check if bundle appears in page text
    const textSnippet = await cdp.eval(`document.body.innerText.substring(0, 1000)`);
    console.log('Page snippet after adding bundle:\n', textSnippet);

    cdp.close();
}

main().catch(console.error);
