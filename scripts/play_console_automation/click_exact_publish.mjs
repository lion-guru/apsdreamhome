import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const result = await cdp.eval(`(() => {
        // Find every element with text "Save and publish"
        const candidates = [];
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_ELEMENT);
        let node;
        while (node = walker.nextNode()) {
            if (node.innerText && node.innerText.includes('Save and publish')) {
                const rect = node.getBoundingClientRect();
                candidates.push({
                    tagName: node.tagName,
                    className: node.className,
                    text: node.innerText.trim(),
                    rect: { x: rect.x, y: rect.y, width: rect.width, height: rect.height }
                });
            }
        }
        return candidates;
    })()`);

    console.log('Candidates:', JSON.stringify(result, null, 2));

    // Find the one in the dialog (higher y or centered, small width/height)
    // The dialog button has width ~130-150px, around x=1100-1200, y=550-620
    const btnCandidate = result.find(c => c.rect.y > 500 && c.rect.y < 650 && c.rect.x > 1000 && c.rect.width > 50 && c.rect.width < 250);
    console.log('Target button candidate:', btnCandidate);

    if (btnCandidate) {
        const clickX = btnCandidate.rect.x + btnCandidate.rect.width / 2;
        const clickY = btnCandidate.rect.y + btnCandidate.rect.height / 2;
        console.log(`Dispatching mouse click at (${clickX}, ${clickY})`);

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
        console.log('Dispatched mouse click.');
    } else {
        console.log('Using hardcoded coordinates (1170, 585)');
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mousePressed',
            x: 1170,
            y: 585,
            button: 'left',
            clickCount: 1
        });
        await cdp.send('Input.dispatchMouseEvent', {
            type: 'mouseReleased',
            x: 1170,
            y: 585,
            button: 'left',
            clickCount: 1
        });
    }

    await cdp.wait(5000);
    await cdp.screenshot('after_dialog_mouse_click.png');

    const state = await cdp.eval(`(() => {
        return {
            url: window.location.href,
            bodySnippet: document.body.innerText.substring(0, 500)
        };
    })()`);
    console.log('New state:', state);

    cdp.close();
}

main().catch(console.error);
