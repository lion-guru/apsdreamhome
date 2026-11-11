import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Navigating to store-settings...');
    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/store-settings');
    await cdp.wait(5000);

    // Find and click the Edit button under Store listing contact details
    const clickRes = await cdp.eval(`(() => {
        // Let's locate the section with heading "Store listing contact details"
        const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, [role="heading"], div'));
        const contactHeading = headings.find(h => (h.innerText || '').trim() === 'Store listing contact details');
        
        let targetEdit = null;
        if (contactHeading) {
            // Find parent section or card
            let parent = contactHeading.parentElement;
            for (let i = 0; i < 5 && parent; i++) {
                const edit = Array.from(parent.querySelectorAll('button, a')).find(b => (b.innerText || '').trim() === 'Edit');
                if (edit) {
                    targetEdit = edit;
                    break;
                }
                parent = parent.parentElement;
            }
        }
        
        if (!targetEdit) {
            // Fallback: all buttons with text Edit
            const allEdits = Array.from(document.querySelectorAll('button, a')).filter(b => (b.innerText || '').trim() === 'Edit');
            if (allEdits.length > 1) {
                targetEdit = allEdits[1];
            } else if (allEdits.length === 1) {
                targetEdit = allEdits[0];
            }
        }

        if (targetEdit) {
            targetEdit.scrollIntoView({ block: 'center' });
            targetEdit.click();
            return { clicked: true, tag: targetEdit.tagName, text: targetEdit.innerText };
        }
        return { error: 'Could not find contact edit button' };
    })()`);

    console.log('Click contact edit result:', clickRes);
    await cdp.wait(3000);

    // Let's inspect what opened (modal, drawer, or inline form)
    const domState = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .acx-overlay-container, material-drawer, aside');
        const allInputs = Array.from(document.querySelectorAll('input, textarea')).map(i => {
            const r = i.getBoundingClientRect();
            return {
                tag: i.tagName,
                type: i.type,
                placeholder: i.placeholder,
                ariaLabel: i.getAttribute('aria-label'),
                value: i.value,
                visible: r.width > 0 && r.height > 0,
                rect: { w: r.width, h: r.height, top: r.top }
            };
        });

        const allButtons = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0;
        }).map(b => b.innerText.trim());

        return {
            hasDialog: !!dialog,
            dialogText: dialog ? dialog.innerText.substring(0, 500) : null,
            visibleInputs: allInputs.filter(i => i.visible),
            buttons: allButtons
        };
    })()`);

    console.log('DOM State after clicking Edit:\n', JSON.stringify(domState, null, 2));
    await cdp.screenshot('contact_edit_opened.png');

    // Fill the contact details:
    // Email: techguruabhay@gmail.com
    // Phone: +917007444842
    // Website: https://www.apsdreamhomes.co.in
    const fillResult = await cdp.eval(`(() => {
        const email = 'techguruabhay@gmail.com';
        const phone = '+917007444842';
        const website = 'https://www.apsdreamhomes.co.in';

        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;

        // Find visible text/email/tel/url inputs inside dialog or on page
        const scope = document.querySelector('material-dialog, [role="dialog"], .acx-overlay-container') || document;
        const textInputs = Array.from(scope.querySelectorAll('input')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 50 && r.height > 15 && i.type !== 'checkbox' && i.type !== 'radio' && i.type !== 'hidden';
        });

        console.log('Found ' + textInputs.length + ' candidate text inputs');
        if (textInputs.length < 1) {
            return { error: 'No text inputs found in scope', found: textInputs.length };
        }

        // Fill inputs by label or position
        let filledCount = 0;
        for (const input of textInputs) {
            const label = (input.getAttribute('aria-label') || input.placeholder || '').toLowerCase();
            const parentText = (input.parentElement ? input.parentElement.parentElement ? input.parentElement.parentElement.innerText : '' : '').toLowerCase();

            if (label.includes('email') || parentText.includes('email') || input.type === 'email') {
                nativeSet.call(input, email);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                filledCount++;
            } else if (label.includes('phone') || parentText.includes('phone') || input.type === 'tel') {
                nativeSet.call(input, phone);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                filledCount++;
            } else if (label.includes('website') || label.includes('https') || parentText.includes('website') || input.type === 'url') {
                nativeSet.call(input, website);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                filledCount++;
            }
        }

        // If not matched by label, fill by index if there are 3 inputs
        if (filledCount === 0 && textInputs.length >= 3) {
            nativeSet.call(textInputs[0], email);
            textInputs[0].dispatchEvent(new Event('input', { bubbles: true }));
            textInputs[0].dispatchEvent(new Event('change', { bubbles: true }));

            nativeSet.call(textInputs[1], phone);
            textInputs[1].dispatchEvent(new Event('input', { bubbles: true }));
            textInputs[1].dispatchEvent(new Event('change', { bubbles: true }));

            nativeSet.call(textInputs[2], website);
            textInputs[2].dispatchEvent(new Event('input', { bubbles: true }));
            textInputs[2].dispatchEvent(new Event('change', { bubbles: true }));
            filledCount = 3;
        }

        return {
            filledCount,
            currentValues: textInputs.map(i => ({ val: i.value, type: i.type, label: i.getAttribute('aria-label') }))
        };
    })()`);

    console.log('Fill result:', fillResult);
    await cdp.wait(2000);
    await cdp.screenshot('contact_details_inputs_filled.png');

    // Click Save button
    const saveRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .acx-overlay-container') || document;
        const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const saveBtn = btns.find(b => {
            const t = (b.innerText || '').trim();
            return t === 'Save' || t === 'Save and publish';
        });

        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, text: saveBtn.innerText.trim() };
        }
        return { error: 'Save button not found or disabled', buttons: btns.map(b => b.innerText.trim()) };
    })()`);

    console.log('Save button click result:', saveRes);
    await cdp.wait(3000);

    // If confirmation dialog appears, confirm it
    const confRes = await cdp.eval(`(() => {
        const dialogs = Array.from(document.querySelectorAll('material-dialog, [role="dialog"]'));
        for (const dlg of dialogs) {
            const btns = Array.from(dlg.querySelectorAll('button')).filter(b => {
                const r = b.getBoundingClientRect();
                return r.width > 0 && r.height > 0 && !b.disabled;
            });
            const confBtn = btns.find(b => {
                const t = (b.innerText || '').trim();
                return t === 'Save and publish' || t === 'Publish' || t === 'Confirm';
            });
            if (confBtn) {
                confBtn.click();
                return { confirmed: true, text: confBtn.innerText.trim() };
            }
        }
        return { noDialog: true };
    })()`);

    console.log('Confirmation click result:', confRes);
    await cdp.wait(4000);

    await cdp.screenshot('contact_details_saved_result.png');

    // Check dashboard
    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/app-dashboard');
    await cdp.wait(5000);

    const dashText = await cdp.eval(`document.body.innerText.substring(0, 1500)`);
    console.log('Dashboard text:\n', dashText);
    await cdp.screenshot('dashboard_after_contact_update.png');

    cdp.close();
}

main().catch(console.error);
