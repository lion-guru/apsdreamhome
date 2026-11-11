import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click "Add countries / regions" link/button
    console.log('Clicking "Add countries / regions"...');
    const clickAddCountries = await cdp.eval(`(() => {
        const all = Array.from(document.querySelectorAll('a, button, [role="button"]')).filter(el => {
            const t = (el.innerText || '').trim();
            return t === 'Add countries / regions' || t.includes('Add countries');
        });

        if (all.length > 0) {
            all[0].scrollIntoView({ block: 'center' });
            all[0].click();
            return { clicked: true, tag: all[0].tagName, text: all[0].innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Click Add countries result:', clickAddCountries);
    await cdp.wait(4000);
    await cdp.screenshot('countries_selection_opened.png');

    // 2. Inspect what opened (modal or page)
    const viewState = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        const cbs = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]')).map(cb => {
            const r = cb.getBoundingClientRect();
            return {
                tag: cb.tagName,
                aria: cb.getAttribute('aria-label'),
                rect: { x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height) }
            };
        }).filter(cb => cb.rect.w > 0);

        const btns = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            disabled: b.disabled
        }));

        return {
            hasDialog: !!dialog,
            cbs,
            btns,
            url: window.location.href,
            textSnippet: document.body.innerText.substring(0, 800)
        };
    })()`);
    console.log('View State after clicking Add countries:\n', JSON.stringify(viewState, null, 2));

    // 3. Select all countries or India
    // Usually the top checkbox or cbs[0] is "Select all" or country selection
    console.log('Selecting countries checkbox...');
    const selectCountryRes = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]'));
        if (cbs.length > 0) {
            // Click the first one (Select all)
            cbs[0].scrollIntoView({ block: 'center' });
            cbs[0].click();
            return { clickedFirstCb: true, total: cbs.length, aria: cbs[0].getAttribute('aria-label') };
        }
        return { noCbs: true };
    })()`);
    console.log('Select country result:', selectCountryRes);
    await cdp.wait(2000);
    await cdp.screenshot('countries_checkbox_selected.png');

    // 4. Click Save in the dialog/page
    console.log('Clicking Save / Save countries...');
    const saveCountriesRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => {
            const t = (b.innerText || '').trim();
            return t === 'Save' || t.includes('Add countries') || t.includes('Select') || t === 'Save and publish';
        });

        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Save countries result:', saveCountriesRes);
    await cdp.wait(4000);

    // If confirmation modal appears ("Add countries and regions?"), confirm it
    const confCountries = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"], .modal');
        if (dialog) {
            const btns = Array.from(dialog.querySelectorAll('button')).filter(b => {
                const r = b.getBoundingClientRect();
                return r.width > 0 && r.height > 0 && !b.disabled;
            });
            const conf = btns.find(b => {
                const t = (b.innerText || '').trim();
                return t.includes('Add') || t === 'Save' || t === 'Confirm';
            });
            if (conf) {
                conf.click();
                return { confirmed: true, text: conf.innerText.trim() };
            }
        }
        return { noDialog: true };
    })()`);
    console.log('Confirm countries modal result:', confCountries);
    await cdp.wait(5000);
    await cdp.screenshot('after_countries_saved.png');

    // 5. Navigate back to review page to verify 0 errors
    const reviewUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804/releases/1/review`;
    console.log('Navigating to review URL:', reviewUrl);
    await cdp.navigate(reviewUrl);
    await cdp.wait(6000);

    const afterReview = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        }).map(b => b.innerText.trim().replace(/\\n/g, ' '));

        return {
            hasErrors: text.includes('error\n1 Error') || text.includes('Errors'),
            hasWarnings: text.includes('Warning'),
            btns,
            snippet: text.substring(0, 1500)
        };
    })()`);
    console.log('Review page after countries fix:\n', JSON.stringify(afterReview, null, 2));
    await cdp.screenshot('review_page_errors_fixed.png');

    // 6. Click Save and publish / Start rollout to closed testing
    console.log('Publishing release to Closed Testing...');
    const pubClick = await cdp.eval(`(() => {
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
    console.log('Publish button click:', pubClick);
    await cdp.wait(4000);

    // 7. Confirm dialog if any
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

    await cdp.screenshot('closed_testing_release_live.png');

    cdp.close();
}

main().catch(console.error);
