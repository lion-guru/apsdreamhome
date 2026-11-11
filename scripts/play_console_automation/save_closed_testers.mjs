import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click checkbox for Internal Testers
    console.log('Selecting Internal Testers checkbox...');
    const clickCb = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]'));
        if (cbs.length > 1) {
            cbs[1].scrollIntoView({ block: 'center' });
            cbs[1].click();
            return { clicked: true, total: cbs.length };
        } else if (cbs.length === 1) {
            cbs[0].click();
            return { clicked: true, index: 0 };
        }
        return { notFound: true };
    })()`);
    console.log('Click checkbox result:', clickCb);
    await cdp.wait(2000);

    // 2. Fill feedback email
    console.log('Filling feedback email...');
    await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="email"], input')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 150 && r.height > 20;
        });
        if (inputs.length > 0) {
            const lastInput = inputs[inputs.length - 1];
            lastInput.focus();
            const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
            nativeSet.call(lastInput, 'techguruabhay@gmail.com');
            lastInput.dispatchEvent(new Event('input', { bubbles: true }));
            lastInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    })()`);
    await cdp.wait(1000);

    // 3. Click Save button at bottom right
    console.log('Clicking Save...');
    const clickSave = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled && (b.innerText || '').trim() === 'Save';
        });
        if (btns.length > 0) {
            btns[0].scrollIntoView({ block: 'center' });
            btns[0].click();
            return { clicked: true };
        }
        return { notFound: true };
    })()`);
    console.log('Click Save result:', clickSave);
    await cdp.wait(4000);
    await cdp.screenshot('closed_testers_saved.png');

    // 4. Extract Opt-in links at bottom
    const links = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const webMatch = text.match(/https:\\/\\/play\\.google\\.com\\/apps\\/testing\\/[^\\s\\n]+/);
        const allLinks = Array.from(document.querySelectorAll('a')).map(a => a.href).filter(h => h && h.includes('testing'));
        return { webMatch: webMatch ? webMatch[0] : null, allLinks };
    })()`);
    console.log('Closed testing links:', links);

    cdp.close();
}

main().catch(console.error);
