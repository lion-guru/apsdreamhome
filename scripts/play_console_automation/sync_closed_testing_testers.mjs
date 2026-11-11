import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const closedTrackId = '4700085989411864804';
    const closedUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/${closedTrackId}?tab=testers`;
    console.log('Navigating to Closed Testing Testers tab:', closedUrl);
    await cdp.navigate(closedUrl);
    await cdp.wait(4000);

    const closedState = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]')).map(cb => ({
            text: (cb.parentElement ? cb.parentElement.innerText : '').trim().replace(/\\n/g, ' '),
            checked: cb.getAttribute('aria-checked') === 'true' || cb.checked
        }));

        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        const joinLink = Array.from(document.querySelectorAll('a')).map(a => a.href).find(h => h && h.includes('testing'));

        return {
            cbs,
            saveDisabled: saveBtn ? saveBtn.disabled : null,
            joinLink
        };
    })()`);

    console.log('Closed testing testers state:', JSON.stringify(closedState, null, 2));

    // If Internal Testers is not checked, check it and save
    const checkRes = await cdp.eval(`(() => {
        const cbs = Array.from(document.querySelectorAll('mat-checkbox, input[type="checkbox"]'));
        const target = cbs.find(cb => {
            const p = cb.parentElement ? cb.parentElement.innerText : '';
            return p.includes('Internal Testers');
        });

        if (target && target.getAttribute('aria-checked') !== 'true') {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true };
        }
        return { alreadyChecked: true };
    })()`);

    console.log('Checkbox click result:', checkRes);
    await cdp.wait(2000);

    // Click Save if enabled
    const saveRes = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        if (saveBtn && !saveBtn.disabled) {
            saveBtn.scrollIntoView({ block: 'center' });
            saveBtn.click();
            return { saved: true };
        }
        return { notSaved: true, disabled: saveBtn ? saveBtn.disabled : null };
    })()`);

    console.log('Closed testing Save result:', saveRes);
    await cdp.wait(3000);

    // Get Closed testing join link
    const optInLinks = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const matches = text.match(/https:\\/\\/play\\.google\\.com\\/[^\\s\\n]+/g) || [];
        return matches.filter(m => m.includes('testing') || m.includes('apps'));
    })()`);

    console.log('Closed testing opt-in links:', optInLinks);
    cdp.close();
}

main().catch(console.error);
