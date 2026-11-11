import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Navigating to store-settings...');
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/store-settings`);
    await cdp.wait(5000);

    // 1. Click Edit under Store listing contact details
    console.log('Clicking Edit on Store listing contact details...');
    const clickEdit = await cdp.eval(`(() => {
        const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, [role="heading"], div'));
        const contactHeading = headings.find(h => (h.innerText || '').trim() === 'Store listing contact details');
        
        let targetEdit = null;
        if (contactHeading) {
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
            const allEdits = Array.from(document.querySelectorAll('button, a')).filter(b => (b.innerText || '').trim() === 'Edit');
            if (allEdits.length > 1) targetEdit = allEdits[1];
            else if (allEdits.length === 1) targetEdit = allEdits[0];
        }

        if (targetEdit) {
            targetEdit.scrollIntoView({ block: 'center' });
            targetEdit.click();
            return { clicked: true };
        }
        return { error: 'No edit button found' };
    })()`);
    console.log('Click edit result:', clickEdit);
    await cdp.wait(3000);

    // 2. Identify the 3 text inputs
    const inputsInfo = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input')).filter(i => {
            const r = i.getBoundingClientRect();
            return r.width > 100 && r.height > 20 && i.type !== 'checkbox';
        });
        // Sort by vertical position
        inputs.sort((a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top);

        return inputs.map((input, idx) => {
            input.setAttribute('data-contact-idx', idx.toString());
            return {
                idx,
                top: input.getBoundingClientRect().top,
                val: input.value,
                aria: input.getAttribute('aria-label'),
                placeholder: input.placeholder,
                type: input.type
            };
        });
    })()`);
    console.log('Inputs found:', JSON.stringify(inputsInfo, null, 2));

    // We expect 3 inputs:
    // idx 0 -> Email: techguruabhay@gmail.com
    // idx 1 -> Phone: +917007444842
    // idx 2 -> Website: www.apsdreamhomes.co.in or https://www.apsdreamhomes.co.in

    // 3. Type Email (idx 0)
    console.log('Typing Email...');
    await cdp.eval(`(() => {
        const inp = document.querySelector('input[data-contact-idx="0"]');
        if (inp) {
            inp.focus();
            inp.select();
            document.execCommand('selectAll', false, null);
        }
    })()`);
    await cdp.send('Input.insertText', { text: 'techguruabhay@gmail.com' });
    await cdp.wait(1000);

    // 4. Type Phone (idx 1)
    console.log('Typing Phone...');
    await cdp.eval(`(() => {
        const inp = document.querySelector('input[data-contact-idx="1"]');
        if (inp) {
            inp.focus();
            inp.select();
            document.execCommand('selectAll', false, null);
        }
    })()`);
    await cdp.send('Input.insertText', { text: '+917007444842' });
    await cdp.wait(1000);

    // 5. Type Website (idx 2)
    // Let's check what aria-label or prefix is on idx 2
    console.log('Typing Website...');
    const webAria = inputsInfo[2]?.aria || '';
    // If aria is 'https://', in Play console website fields with 'https://' prefix expect the domain, or full URL
    // Let's check what value is accepted:
    const webUrl = webAria.includes('https') ? 'https://www.apsdreamhomes.co.in' : 'https://www.apsdreamhomes.co.in';
    await cdp.eval(`(() => {
        const inp = document.querySelector('input[data-contact-idx="2"]');
        if (inp) {
            inp.focus();
            inp.select();
            document.execCommand('selectAll', false, null);
        }
    })()`);
    await cdp.send('Input.insertText', { text: webUrl });
    await cdp.wait(1000);

    // Check values and button states
    const checkState = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input[data-contact-idx]')).map(i => ({
            idx: i.getAttribute('data-contact-idx'),
            val: i.value
        }));

        const btns = Array.from(document.querySelectorAll('button')).map(b => {
            const r = b.getBoundingClientRect();
            return {
                text: b.innerText.trim(),
                disabled: b.disabled,
                visible: r.width > 0 && r.height > 0
            };
        }).filter(b => b.visible);

        return { inputs, btns };
    })()`);
    console.log('State after typing:', JSON.stringify(checkState, null, 2));
    await cdp.screenshot('contact_typed_state.png');

    // 6. Click Save / Save and publish
    console.log('Clicking Save / Save and publish button...');
    const clickSave = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0;
        });

        const saveBtn = btns.find(b => {
            const t = (b.innerText || '').trim();
            return (t === 'Save and publish' || t === 'Save') && !b.disabled;
        });

        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, text: saveBtn.innerText.trim() };
        }
        return { error: 'Save button not enabled', allButtons: btns.map(b => ({ text: b.innerText.trim(), disabled: b.disabled })) };
    })()`);
    console.log('Click Save result:', clickSave);
    await cdp.wait(3000);

    // 7. Check if confirmation modal popped up
    const confirmRes = await cdp.eval(`(() => {
        const dialogs = Array.from(document.querySelectorAll('material-dialog, [role="dialog"], .modal'));
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
    console.log('Confirm modal result:', confirmRes);
    await cdp.wait(5000);

    await cdp.screenshot('contact_after_all_saves.png');

    // 8. Go back to app dashboard and verify
    console.log('Navigating to app dashboard...');
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-dashboard`);
    await cdp.wait(6000);

    const finalDash = await cdp.eval(`(() => {
        const text = document.body.innerText;
        return {
            hasFinish: text.includes('Finish setting up your app'),
            completedCount: (text.match(/(\d+) of (\d+) complete/) || [])[0],
            snippet: text.substring(0, 1800)
        };
    })()`);
    console.log('Final Dashboard Check:', finalDash);
    await cdp.screenshot('final_dashboard_check.png');

    cdp.close();
}

main().catch(console.error);
