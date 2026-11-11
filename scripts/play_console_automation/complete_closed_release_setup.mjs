import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('On release prepare page...');
    await cdp.wait(2000);

    // 1. Click "Add from library"
    console.log('Clicking "Add from library"...');
    const clickAddLib = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => (b.innerText || '').includes('Add from library'));
        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true, allBtns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Add from library result:', clickAddLib);
    await cdp.wait(3000);
    await cdp.screenshot('add_from_library_modal.png');

    // 2. Select Version 4 from the library modal
    console.log('Selecting Version 4 from library dialog...');
    const selectBundle = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal') || document;
        // Find rows or checkboxes
        const checkboxes = Array.from(dialog.querySelectorAll('input[type="checkbox"], [role="checkbox"]'));
        console.log('Found checkboxes in dialog:', checkboxes.length);

        // Find the one corresponding to version 4 or first bundle
        if (checkboxes.length > 0) {
            checkboxes[0].click();
            return { clickedFirst: true, total: checkboxes.length };
        }

        // Or click row containing "4" or "1.2.5"
        const rows = Array.from(dialog.querySelectorAll('tr, material-row, div[role="row"]')).filter(r => (r.innerText || '').includes('1.2.5') || (r.innerText || '').includes('(4)'));
        if (rows.length > 0) {
            const cb = rows[0].querySelector('input[type="checkbox"], [role="checkbox"]');
            if (cb) cb.click();
            else rows[0].click();
            return { clickedRow: true };
        }
        return { notFound: true };
    })()`);
    console.log('Select bundle result:', selectBundle);
    await cdp.wait(2000);

    // Click "Add to release" button in dialog
    console.log('Clicking "Add to release" in dialog...');
    const confirmAdd = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal') || document;
        const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const addBtn = btns.find(b => (b.innerText || '').includes('Add to release') || (b.innerText || '').trim() === 'Add');
        if (addBtn) {
            addBtn.click();
            return { clicked: true, text: addBtn.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Confirm add result:', confirmAdd);
    await cdp.wait(4000);
    await cdp.screenshot('after_bundle_added.png');

    // 3. Click "Copy from a previous release" for Release notes
    console.log('Clicking "Copy from a previous release"...');
    const copyNotes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => (b.innerText || '').includes('Copy from a previous release') || (b.innerText || '').includes('Copy from previous'));
        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Copy notes result:', copyNotes);
    await cdp.wait(3000);

    // If modal appeared to pick which previous release to copy from
    const pickPrev = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
                const r = b.getBoundingClientRect();
                return r.width > 0 && r.height > 0 && !b.disabled;
            });
            const copyBtn = btns.find(b => (b.innerText || '').includes('Copy') || (b.innerText || '').includes('Select'));
            if (copyBtn) {
                copyBtn.click();
                return { clickedCopy: true, text: copyBtn.innerText.trim() };
            }
        }
        return { noDialog: true };
    })()`);
    console.log('Pick previous release result:', pickPrev);
    await cdp.wait(2000);

    // Check Release name
    const setRelName = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input[type="text"]')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 100 && r.height > 20;
        });
        if (inputs.length > 0) {
            if (!inputs[0].value) {
                const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                nativeSet.call(inputs[0], '1.2.5 (4) - Closed Testing Release');
                inputs[0].dispatchEvent(new Event('input', { bubbles: true }));
                inputs[0].dispatchEvent(new Event('change', { bubbles: true }));
                return { setName: '1.2.5 (4) - Closed Testing Release' };
            }
            return { existingName: inputs[0].value };
        }
        return { noInput: true };
    })()`);
    console.log('Release name check:', setRelName);
    await cdp.wait(2000);
    await cdp.screenshot('release_form_filled.png');

    // 4. Click Next button
    console.log('Clicking Next button...');
    const clickNext = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const nextBtn = btns.find(b => (b.innerText || '').trim() === 'Next');
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Click Next result:', clickNext);
    await cdp.wait(6000);

    const valUrl = await cdp.eval(`window.location.href`);
    console.log('URL after Next:', valUrl);
    await cdp.screenshot('validation_screen.png');

    // Inspect validation results
    const valText = await cdp.eval(`document.body.innerText.substring(0, 1500)`);
    console.log('Validation text:\n', valText);

    // Look for "Save and publish" or "Save" button
    const saveFinal = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const pubBtn = btns.find(b => {
            const t = (b.innerText || '').trim();
            return t === 'Save and publish' || t.includes('rollout') || t === 'Save' || t === 'Publish';
        });

        if (pubBtn) {
            pubBtn.click();
            return { clicked: true, text: pubBtn.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Save final result:', saveFinal);
    await cdp.wait(4000);

    // If confirmation dialog appears
    const confModal = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
                const r = b.getBoundingClientRect();
                return r.width > 0 && r.height > 0 && !b.disabled;
            });
            const pubBtn = btns.find(b => {
                const t = (b.innerText || '').trim();
                return t === 'Save and publish' || t.includes('rollout') || t === 'Publish' || t === 'Save';
            });
            if (pubBtn) {
                pubBtn.click();
                return { confirmed: true, text: pubBtn.innerText.trim() };
            }
        }
        return { noDialog: true };
    })()`);
    console.log('Confirmation dialog result:', confModal);
    await cdp.wait(5000);

    await cdp.screenshot('closed_release_completed.png');

    cdp.close();
}

main().catch(console.error);
