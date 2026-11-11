import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "Show more" on Errors
    const expandRes = await cdp.eval(`(() => {
        const expandBtns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('Show more'));
        for (const btn of expandBtns) {
            btn.click();
        }
        return { clickedCount: expandBtns.length };
    })()`);
    console.log('Expanded errors:', expandRes);
    await cdp.wait(2000);

    // Read error and warning text
    const errorDetails = await cdp.eval(`(() => {
        const errorBoxes = Array.from(document.querySelectorAll('[role="alert"], material-dialog, div')).filter(el => {
            const t = el.innerText || '';
            return t.includes('Error') || t.includes('error') || t.includes('Warning') || t.includes('warning');
        });

        // Let's get the entire text around Errors, warnings and messages
        const text = document.body.innerText;
        const startIdx = text.indexOf('Errors, warnings and messages');
        const snippet = startIdx !== -1 ? text.substring(startIdx, startIdx + 2000) : text.substring(0, 2000);

        return { snippet };
    })()`);

    console.log('Error Details Snippet:\n', errorDetails.snippet);
    await cdp.screenshot('exact_release_errors.png');

    cdp.close();
}

main().catch(console.error);
