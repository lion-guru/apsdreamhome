import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click cbs[1] (MAT-CHECKBOX for Version 4)
    console.log('Clicking Version 4 mat-checkbox...');
    const clickCb = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox'));
        if (cbs.length > 1) {
            cbs[1].scrollIntoView({ block: 'center' });
            cbs[1].click();
            return { clicked: true, total: cbs.length };
        }
        return { notFound: true, total: cbs.length };
    })()`);
    console.log('Click checkbox result:', clickCb);
    await cdp.wait(2000);

    // 2. Check if "Add to release" button is now enabled
    const checkBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('Add to release'));
        if (btns.length > 0) {
            return { found: true, disabled: btns[0].disabled, text: btns[0].innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Add to release button status:', checkBtn);

    // 3. Click "Add to release" button
    const clickAdd = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('Add to release'));
        if (btns.length > 0 && !btns[0].disabled) {
            btns[0].click();
            return { clicked: true };
        }
        return { error: 'Button not enabled or not found' };
    })()`);
    console.log('Click Add to release result:', clickAdd);
    await cdp.wait(4000);
    await cdp.screenshot('bundle_added_successfully.png');

    // 4. Verify bundle appears on prepare page
    const pageSnippet = await cdp.eval(`(() => {
        return {
            text: document.body.innerText.substring(0, 1500)
        };
    })()`);
    console.log('Prepare page snippet:\n', pageSnippet.text);

    // 5. Click Next button
    console.log('Clicking Next button...');
    const clickNext = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Next' && !b.disabled);
        if (btns.length > 0) {
            btns[0].scrollIntoView({ block: 'center' });
            btns[0].click();
            return { clicked: true };
        }
        return { notFound: true };
    })()`);
    console.log('Click Next result:', clickNext);
    await cdp.wait(6000);

    const reviewUrl = await cdp.eval(`window.location.href`);
    console.log('Review URL:', reviewUrl);
    await cdp.screenshot('review_page_with_bundle.png');

    // 6. Inspect review page validation
    const reviewInfo = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        }).map(b => b.innerText.trim().replace(/\\n/g, ' '));

        return {
            hasErrors: text.includes('Errors') || text.includes('fix errors'),
            hasWarnings: text.includes('Warning'),
            btns,
            snippet: text.substring(0, 1500)
        };
    })()`);
    console.log('Review Page Info:\n', JSON.stringify(reviewInfo, null, 2));

    // 7. Click Save and publish / Start rollout to closed testing
    console.log('Looking for publish / rollout button...');
    const clickPub = await cdp.eval(`(() => {
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
    console.log('Click Publish result:', clickPub);
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
    await cdp.screenshot('final_closed_rollout_result.png');

    cdp.close();
}

main().catch(console.error);
