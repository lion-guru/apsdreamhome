import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const countriesUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804?tab=countryAvailability`;
    console.log('Navigating to:', countriesUrl);
    await cdp.navigate(countriesUrl);
    await cdp.wait(5000);

    // 1. Scroll down .main-content
    console.log('Scrolling down .main-content...');
    await cdp.eval(`(() => {
        const main = document.querySelector('.main-content') || document.documentElement || document.body;
        main.scrollTop = 1000;
        window.scrollTo(0, 1000);
    })()`);
    await cdp.wait(2000);
    await cdp.screenshot('countries_tab_scrolled.png');

    // 2. Click "Add countries / regions" button
    console.log('Clicking "Add countries / regions" button...');
    const clickAddBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return (b.innerText || '').includes('Add countries');
        });

        if (btns.length > 0) {
            btns[0].scrollIntoView({ block: 'center' });
            btns[0].click();
            return { clicked: true, text: btns[0].innerText.trim() };
        }
        return { notFound: true, allBtns: Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).slice(0, 10) };
    })()`);
    console.log('Click Add result:', clickAddBtn);
    await cdp.wait(3000);
    await cdp.screenshot('country_modal_opened.png');

    // 3. Inspect modal checkboxes
    const cbModalRes = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal') || document;
        const cbs = Array.from(dialog.querySelectorAll('mat-checkbox, input[type="checkbox"]'));
        if (cbs.length > 0) {
            cbs[0].scrollIntoView({ block: 'center' });
            cbs[0].click();
            return { clickedFirst: true, total: cbs.length, aria: cbs[0].getAttribute('aria-label') };
        }
        return { noCheckboxes: true };
    })()`);
    console.log('Modal Checkbox Select result:', cbModalRes);
    await cdp.wait(2000);
    await cdp.screenshot('country_modal_checked.png');

    // 4. Click Save in modal
    console.log('Clicking Save in country modal...');
    const clickSaveModal = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal') || document;
        const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const saveBtn = btns.find(b => (b.innerText || '').includes('Save') || (b.innerText || '').includes('Add'));
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true, text: saveBtn.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Click Save result in modal:', clickSaveModal);
    await cdp.wait(3000);

    // 5. Confirm dialog if any
    const confirmAdd = await cdp.eval(`(() => {
        const dialogs = Array.from(document.querySelectorAll('material-dialog, [role="dialog"], .modal'));
        for (const dlg of dialogs) {
            const btns = Array.from(dlg.querySelectorAll('button')).filter(b => {
                const r = b.getBoundingClientRect();
                return r.width > 0 && r.height > 0 && !b.disabled;
            });
            const conf = btns.find(b => (b.innerText || '').includes('Add') || (b.innerText || '').includes('Save'));
            if (conf) {
                conf.click();
                return { confirmed: true, text: conf.innerText.trim() };
            }
        }
        return { noDialog: true };
    })()`);
    console.log('Confirm Add Dialog result:', confirmAdd);
    await cdp.wait(5000);
    await cdp.screenshot('countries_fully_saved.png');

    // 6. Navigate to Review page
    const revUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804/releases/1/review`;
    console.log('Navigating to review page:', revUrl);
    await cdp.navigate(revUrl);
    await cdp.wait(6000);

    // 7. Check errors on review page
    const reviewStatus = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const hasErr = text.includes('Error') && !text.includes('0 Error');
        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            disabled: b.disabled
        })).filter(b => b.text.length > 0);

        return {
            hasErr,
            snippet: text.substring(0, 1500),
            btns
        };
    })()`);
    console.log('Review Page Status:\n', JSON.stringify(reviewStatus, null, 2));
    await cdp.screenshot('review_page_status.png');

    // 8. Click Save and publish / Start rollout
    console.log('Clicking Save / Start rollout to closed testing...');
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
    console.log('Publish result:', pubRes);
    await cdp.wait(4000);

    // 9. Confirm modal if any
    const finalConfirm = await cdp.eval(`(() => {
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
    console.log('Final confirmation dialog:', finalConfirm);
    await cdp.wait(5000);

    await cdp.screenshot('closed_testing_live_verified.png');

    cdp.close();
}

main().catch(console.error);
