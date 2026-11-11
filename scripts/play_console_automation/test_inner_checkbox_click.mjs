import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Navigate back to prepare or click Back
    console.log('Navigating to prepare...');
    const prepUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804/releases/1/prepare`;
    await cdp.navigate(prepUrl);
    await cdp.wait(5000);

    // 2. Click Add from library
    console.log('Clicking Add from library...');
    await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('Add from library'));
        if (btns.length > 0) btns[0].click();
    })()`);
    await cdp.wait(3000);

    // 3. Inspect inner structure of mat-checkbox
    const cbInspect = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox'));
        if (cbs.length < 2) return { error: 'Less than 2 mat-checkboxes found', total: cbs.length };

        const targetCb = cbs[1]; // Row 1 (Version 4)
        const innerInput = targetCb.querySelector('input');
        const innerLabel = targetCb.querySelector('label');

        // Let's click innerInput first
        if (innerInput) {
            innerInput.click();
            innerInput.dispatchEvent(new Event('change', { bubbles: true }));
        } else if (innerLabel) {
            innerLabel.click();
        } else {
            targetCb.click();
        }

        return {
            hasInnerInput: !!innerInput,
            hasInnerLabel: !!innerLabel,
            innerInputChecked: innerInput ? innerInput.checked : null,
            cbCheckedClass: targetCb.className
        };
    })()`);
    console.log('Checkbox click inspect:', cbInspect);
    await cdp.wait(2000);

    // 4. Check Add to release button
    const btnCheck = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('Add to release'));
        if (btns.length > 0) {
            return {
                found: true,
                disabled: btns[0].disabled,
                text: btns[0].innerText.trim()
            };
        }
        return { notFound: true };
    })()`);
    console.log('Add to release button after inner click:', btnCheck);
    await cdp.screenshot('modal_after_inner_click.png');

    // 5. If enabled, click Add to release!
    if (btnCheck.found && !btnCheck.disabled) {
        console.log('Clicking enabled Add to release button...');
        await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('Add to release'));
            if (btns.length > 0) btns[0].click();
        })()`);
        await cdp.wait(4000);
        await cdp.screenshot('prepare_with_bundle_added.png');

        // Check if bundle is now in prepare page
        const bundlesAdded = await cdp.eval(`(() => {
            return {
                text: document.body.innerText.includes('1.2.5') || document.body.innerText.includes('(4)')
            };
        })()`);
        console.log('Bundle in prepare page:', bundlesAdded);

        // Click Next
        console.log('Clicking Next...');
        await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Next' && !b.disabled);
            if (btns.length > 0) btns[0].click();
        })()`);
        await cdp.wait(6000);

        const revUrl = await cdp.eval(`window.location.href`);
        console.log('Review URL:', revUrl);
        await cdp.screenshot('review_with_real_bundle.png');

        const reviewText = await cdp.eval(`document.body.innerText.substring(0, 1500)`);
        console.log('Review text:\n', reviewText);

        // Click Save and publish / rollout
        const pubResult = await cdp.eval(`(() => {
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
        console.log('Publish click result:', pubResult);
        await cdp.wait(4000);

        // Confirm modal if any
        const confirmDlg = await cdp.eval(`(() => {
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
        console.log('Confirmation modal result:', confirmDlg);
        await cdp.wait(5000);

        await cdp.screenshot('closed_release_final_status.png');
    }

    cdp.close();
}

main().catch(console.error);
