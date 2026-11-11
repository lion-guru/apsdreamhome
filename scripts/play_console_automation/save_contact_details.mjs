import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const email = 'techguruabhay@gmail.com';
    const phone = '+919125555555';
    const website = 'https://apsdreamhome.com';

    const fillRes = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="url"]')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 100 && r.height > 20;
        });

        if (inputs.length < 3) return { error: 'Less than 3 inputs found', count: inputs.length };

        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;

        // 0: Email
        nativeSet.call(inputs[0], ${JSON.stringify(email)});
        inputs[0].dispatchEvent(new Event('input', { bubbles: true }));
        inputs[0].dispatchEvent(new Event('change', { bubbles: true }));

        // 1: Phone
        nativeSet.call(inputs[1], ${JSON.stringify(phone)});
        inputs[1].dispatchEvent(new Event('input', { bubbles: true }));
        inputs[1].dispatchEvent(new Event('change', { bubbles: true }));

        // 2: Website
        nativeSet.call(inputs[2], ${JSON.stringify(website)});
        inputs[2].dispatchEvent(new Event('input', { bubbles: true }));
        inputs[2].dispatchEvent(new Event('change', { bubbles: true }));

        return {
            filled: true,
            values: inputs.map(i => i.value)
        };
    })()`);

    console.log('Fill result:', fillRes);
    await cdp.wait(2000);

    // Click Save / Save and publish button
    const saveRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => (b.innerText || '').includes('Save and publish') || (b.innerText || '').trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, text: saveBtn.innerText.trim(), disabled: saveBtn.disabled };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Save result:', saveRes);
    await cdp.wait(4000);

    await cdp.screenshot('contact_details_saved.png');

    // Confirm dialog if any
    const confirmRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const confBtn = btns.find(b => (b.innerText || '').includes('Save and publish') || (b.innerText || '').includes('Publish') || (b.innerText || '').includes('Confirm'));
        if (confBtn && !confBtn.disabled) {
            confBtn.click();
            return { clicked: true, text: confBtn.innerText.trim() };
        }
        return { noDialog: true };
    })()`);
    console.log('Confirm dialog result:', confirmRes);
    await cdp.wait(3000);

    await cdp.screenshot('contact_details_final.png');
    cdp.close();
}

main().catch(console.error);
