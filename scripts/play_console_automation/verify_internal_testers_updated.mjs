import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.wait(2000);

    const state = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
        const rows = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"]')).map(r => r.innerText.trim().replace(/\\n/g, ' ')).filter(r => r.includes('Internal Testers'));

        // Check if there is an active feedback email or copy link
        const copyLinkBtn = document.querySelector('button[aria-label*="Copy link"]');
        const link = copyLinkBtn ? copyLinkBtn.getAttribute('aria-label') : null;

        return {
            rows,
            saveDisabled: saveBtn ? saveBtn.disabled : null,
            joinLink: link
        };
    })()`);

    console.log('Track page state after adding tester:', JSON.stringify(state, null, 2));

    // If Save button is active on track page, click it
    if (state.saveDisabled === false) {
        console.log('Clicking Save on track page...');
        await cdp.eval(`(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Save');
            if (btn) btn.click();
        })()`);
        await cdp.wait(3000);
        console.log('Saved track page!');
    }

    cdp.close();
}

main().catch(console.error);
