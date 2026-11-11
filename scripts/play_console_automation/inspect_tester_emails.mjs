import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click on the arrow or row of "Internal Testers"
    const clickRow = await cdp.eval(`(() => {
        const rows = Array.from(document.querySelectorAll('tr, div[role="row"], material-row, div')).filter(el => {
            return (el.innerText || '').includes('Internal Testers');
        });

        // Find arrow or link
        const arrow = document.querySelector('a[href*="tester"], button[aria-label*="edit"], [role="row"] a, [role="row"] button');
        if (arrow) {
            arrow.click();
            return { clicked: true, tag: arrow.tagName };
        }
        return { notFound: true, rowCount: rows.length };
    })()`);
    console.log('Click tester list row:', clickRow);
    await cdp.wait(3000);
    await cdp.screenshot('tester_list_view.png');

    // Also check Closed Testing testers tab:
    // Can we add this same email list to Closed Testing Alpha track?
    const closedTrackId = '4700085989411864804';
    const closedTestersUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/${closedTrackId}?tab=testers`;
    console.log('Navigating to Closed Testing Testers tab:', closedTestersUrl);
    await cdp.navigate(closedTestersUrl);
    await cdp.wait(5000);

    const closedTestersState = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]')).map(cb => ({
            text: (cb.parentElement ? cb.parentElement.innerText : '').trim().replace(/\\n/g, ' '),
            checked: cb.getAttribute('aria-checked') === 'true' || cb.checked
        }));
        return {
            cbs,
            snippet: document.body.innerText.substring(0, 1000)
        };
    })()`);
    console.log('Closed Testing Testers state:\n', JSON.stringify(closedTestersState, null, 2));
    await cdp.screenshot('closed_testing_testers_tab.png');

    // If there is an unchecked "Internal Testers" list on Closed Testing, check it and save!
    const selectListRes = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]'));
        const target = cbs.find(cb => {
            const p = cb.parentElement ? cb.parentElement.innerText : '';
            return p.includes('Internal Testers') || p.includes('Testers');
        });

        if (target && target.getAttribute('aria-checked') !== 'true') {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clickedList: true };
        }
        return { notFoundOrChecked: true };
    })()`);
    console.log('Select tester list on closed testing result:', selectListRes);
    await cdp.wait(2000);

    // Click Save button if enabled
    const saveRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => {
            const r = b.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && !b.disabled;
        });
        const saveBtn = btns.find(b => (b.innerText || '').trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clickedSave: true };
        }
        return { noSave: true };
    })()`);
    console.log('Save testers on closed testing:', saveRes);
    await cdp.wait(3000);

    await cdp.screenshot('closed_testing_testers_saved.png');

    // Also get the Closed Testing join link if available
    const closedLink = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const match = text.match(/https:\/\/play\.google\.com\/apps\/testing\/[^\s\n]+/);
        return match ? match[0] : null;
    })()`);
    console.log('Closed Testing Join Link:', closedLink);

    cdp.close();
}

main().catch(console.error);
