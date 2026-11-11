import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 0. Navigate directly to store-settings
    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/store-settings');
    await cdp.wait(5000);

    // 1. Click Edit on "Store listing contact details"
    const clickEdit = await cdp.eval(`(() => {
        const allEdits = Array.from(document.querySelectorAll('button, a')).filter(b => b.innerText.trim() === 'Edit');
        if (allEdits.length > 1) {
            allEdits[1].scrollIntoView({ block: 'center' });
            allEdits[1].click();
            return { clicked: true, index: 1 };
        } else if (allEdits.length === 1) {
            allEdits[0].scrollIntoView({ block: 'center' });
            allEdits[0].click();
            return { clicked: true, index: 0 };
        }
        return { error: 'No Edit button found' };
    })()`);
    console.log('Click Edit result:', clickEdit);
    await cdp.wait(3000);

    // 2. Fill in the 3 inputs: Email, Phone, Website
    const email = 'techguruabhay@gmail.com';
    const phone = '+917007444842';
    const website = 'https://apsdreamhome.com';

    const fillRes = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="url"]')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 100 && r.height > 20;
        });

        if (inputs.length < 3) return { error: 'Found inputs: ' + inputs.length };

        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;

        // Email
        nativeSet.call(inputs[0], ${JSON.stringify(email)});
        inputs[0].dispatchEvent(new Event('input', { bubbles: true }));
        inputs[0].dispatchEvent(new Event('change', { bubbles: true }));

        // Phone
        nativeSet.call(inputs[1], ${JSON.stringify(phone)});
        inputs[1].dispatchEvent(new Event('input', { bubbles: true }));
        inputs[1].dispatchEvent(new Event('change', { bubbles: true }));

        // Website
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

    // 3. Click Save in modal footer
    const saveRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => ((b.innerText || '').includes('Save and publish') || (b.innerText || '').trim() === 'Save') && !b.disabled);
        if (saveBtn) {
            saveBtn.click();
            return { clickedSave: true, text: saveBtn.innerText.trim() };
        }
        return { error: 'Save button not found or disabled' };
    })()`);
    console.log('Save result:', saveRes);
    await cdp.wait(3000);

    // 4. Confirm dialog if any
    const confirmRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .acx-overlay-container');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button'));
            const confBtn = btns.find(b => (b.innerText || '').includes('Save and publish') || (b.innerText || '').includes('Publish') || (b.innerText || '').includes('Confirm'));
            if (confBtn && !confBtn.disabled) {
                confBtn.click();
                return { clickedConfirm: true, text: confBtn.innerText.trim() };
            }
        }
        return { noConfirmDialog: true };
    })()`);
    console.log('Confirm result:', confirmRes);
    await cdp.wait(4000);

    await cdp.screenshot('contact_details_fully_saved.png');

    // Print current view text
    const curText = await cdp.eval(`document.body.innerText.substring(0, 1000)`);
    console.log('Current store settings text:\n', curText);

    cdp.close();
}

main().catch(console.error);
