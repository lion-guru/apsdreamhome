import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const prepUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804/releases/1/prepare`;
    console.log('Navigating to prepare URL:', prepUrl);
    await cdp.navigate(prepUrl);
    await cdp.wait(5000);

    // 1. Click "Add from library"
    console.log('Clicking "Add from library"...');
    const clickAddLib = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => (b.innerText || '').includes('Add from library'));
        if (target) {
            target.click();
            return { clicked: true };
        }
        return { notFound: true };
    })()`);
    console.log('Click Add from library:', clickAddLib);
    await cdp.wait(3000);

    // 2. Click the row or checkbox for Version 4 (1.2.5)
    console.log('Selecting Version 4...');
    const selectRes = await cdp.eval(`(() => {
        // Find elements in the dialog
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal') || document;
        const allElements = Array.from(dialog.querySelectorAll('*'));

        // Look for element containing "1.2.5"
        const vEl = allElements.find(el => el.children.length === 0 && (el.innerText || '').trim() === '1.2.5');
        if (vEl) {
            // Find row ancestor
            let row = vEl.parentElement;
            for (let i = 0; i < 5 && row; i++) {
                const cb = row.querySelector('material-checkbox, input[type="checkbox"], [role="checkbox"]');
                if (cb) {
                    cb.click();
                    return { clickedCheckbox: true, tag: cb.tagName };
                }
                row = row.parentElement;
            }
            vEl.click();
            return { clickedText: true };
        }

        // Fallback: look for material-checkboxes
        const mCbs = Array.from(dialog.querySelectorAll('material-checkbox, [role="checkbox"]'));
        if (mCbs.length > 1) {
            mCbs[1].click(); // index 1 is row 1
            return { clickedSecondCb: true };
        }

        return { notFound: true, totalEl: allElements.length };
    })()`);
    console.log('Select result:', selectRes);
    await cdp.wait(2000);
    await cdp.screenshot('bundle_selected_dialog.png');

    // 3. Click "Add to release" button in dialog footer
    console.log('Clicking "Add to release" button...');
    const addReleaseRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal') || document;
        const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0;
        });

        const addBtn = btns.find(b => (b.innerText || '').includes('Add to release'));
        if (addBtn) {
            addBtn.click();
            return { clicked: true, disabled: addBtn.disabled };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Add to release button result:', addReleaseRes);
    await cdp.wait(4000);
    await cdp.screenshot('bundle_added_to_prepare.png');

    // 4. Set Release name and Release notes
    console.log('Setting Release name and notes...');
    const setDetailsRes = await cdp.eval(`(() => {
        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        const nativeSetArea = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, 'value').set;

        // Release name input
        const inputs = Array.from(document.querySelectorAll('input[type="text"]')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 100 && r.height > 20;
        });
        let nameSet = false;
        if (inputs.length > 0) {
            nativeSet.call(inputs[0], '1.2.5 (4) - Official Release');
            inputs[0].dispatchEvent(new Event('input', { bubbles: true }));
            inputs[0].dispatchEvent(new Event('change', { bubbles: true }));
            nameSet = true;
        }

        // Release notes textarea
        const textareas = Array.from(document.querySelectorAll('textarea')).filter(t => {
            const r = t.getBoundingClientRect();
            return r.width > 100 && r.height > 20;
        });
        let notesSet = false;
        const notes = '<en-US>\\nOfficial Release 1.2.5 (4):\\n- Real estate plot booking and live availability maps\\n- EMI calculator & payment schedule tracker\\n- Associate partner network, genealogy tree & commission ledger\\n- Digital allotment letter and documents\\n- Enhanced security and stability\\n</en-US>';
        if (textareas.length > 0) {
            nativeSetArea.call(textareas[0], notes);
            textareas[0].dispatchEvent(new Event('input', { bubbles: true }));
            textareas[0].dispatchEvent(new Event('change', { bubbles: true }));
            notesSet = true;
        }

        return { nameSet, notesSet, inputsCount: inputs.length, textareasCount: textareas.length };
    })()`);
    console.log('Set details result:', setDetailsRes);
    await cdp.wait(2000);
    await cdp.screenshot('details_set_on_prepare.png');

    // 5. Click Next button
    console.log('Clicking Next...');
    const nextRes = await cdp.eval(`(() => {
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
    console.log('Next result:', nextRes);
    await cdp.wait(6000);

    const afterNextUrl = await cdp.eval(`window.location.href`);
    console.log('URL after clicking Next:', afterNextUrl);
    await cdp.screenshot('review_page_after_bundle.png');

    // 6. Inspect review page
    const reviewText = await cdp.eval(`document.body.innerText.substring(0, 1500)`);
    console.log('Review page text:\n', reviewText);

    // 7. Click Save and publish / Save / Start rollout
    const pubRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => {
            const t = (b.innerText || '').trim();
            return t === 'Save and publish' || t.includes('rollout') || t === 'Save' || t === 'Publish';
        });

        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Publish result on review page:', pubRes);
    await cdp.wait(4000);

    // 8. Confirm dialog if any
    const dlgRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
                const r = b.getBoundingClientRect();
                return r.width > 0 && r.height > 0 && !b.disabled;
            });
            const conf = btns.find(b => {
                const t = (b.innerText || '').trim();
                return t === 'Save and publish' || t.includes('rollout') || t === 'Publish' || t === 'Save';
            });
            if (conf) {
                conf.click();
                return { confirmed: true, text: conf.innerText.trim() };
            }
        }
        return { noDialog: true };
    })()`);
    console.log('Confirmation dialog result:', dlgRes);
    await cdp.wait(5000);
    await cdp.screenshot('final_closed_release_result.png');

    cdp.close();
}

main().catch(console.error);
