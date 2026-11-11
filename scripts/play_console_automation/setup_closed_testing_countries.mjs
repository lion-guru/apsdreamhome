import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const trackId = '4700085989411864804';
    const countriesUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/${trackId}?tab=countries`;
    console.log('Navigating to countries tab:', countriesUrl);
    await cdp.navigate(countriesUrl);
    await cdp.wait(5000);

    const pageInfo = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const btns = Array.from(document.querySelectorAll('button, a')).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled || false,
            visible: b.getBoundingClientRect().width > 0
        })).filter(b => b.visible);

        return {
            title: document.title,
            snippet: text.substring(0, 1500),
            btns
        };
    })()`);

    console.log('Countries Tab Info:\n', JSON.stringify(pageInfo, null, 2));
    await cdp.screenshot('closed_testing_countries_tab.png');

    // Click "Add countries / regions" or similar button
    const clickAdd = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });

        const target = btns.find(b => (b.innerText || '').includes('Add countries') || (b.innerText || '').includes('Select countries'));
        if (target) {
            target.click();
            return { clicked: true, text: target.innerText.trim() };
        }
        return { notFound: true, btns: btns.map(b => b.innerText.trim()) };
    })()`);
    console.log('Click Add countries result:', clickAdd);
    await cdp.wait(3000);

    if (clickAdd.clicked) {
        await cdp.screenshot('countries_modal_opened.png');
        // Select all countries (usually a top-level checkbox "Select all" or country list)
        const selectRes = await cdp.eval(`(() => {
            const checkboxes = Array.from(document.querySelectorAll('input[type="checkbox"], [role="checkbox"]'));
            console.log('Found checkboxes:', checkboxes.length);
            // First checkbox is typically select all
            if (checkboxes.length > 0) {
                checkboxes[0].click();
                return { clickedFirst: true, total: checkboxes.length };
            }
            return { noCheckboxes: true };
        })()`);
        console.log('Select countries checkbox result:', selectRes);
        await cdp.wait(2000);

        // Click Save in dialog
        const saveRes = await cdp.eval(`(() => {
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
        console.log('Save countries result:', saveRes);
        await cdp.wait(4000);
        await cdp.screenshot('countries_after_save.png');
    }

    cdp.close();
}

main().catch(console.error);
