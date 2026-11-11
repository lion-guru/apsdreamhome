import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Close the "Create email list" dialog
    console.log('Closing modal...');
    await cdp.eval(`(() => {
        const closeBtn = document.querySelector('button[aria-label="Close"]');
        if (closeBtn) closeBtn.click();
        const discardBtn = Array.from(document.querySelectorAll('button')).find(b => (b.innerText || '').trim() === 'Discard');
        if (discardBtn) discardBtn.click();
    })()`);
    await cdp.wait(2000);

    // 2. Check main page content
    const pageData = await cdp.eval(`(() => {
        const table = document.querySelector('ess-table, table, [role="grid"], [role="table"]');
        const rows = Array.from(document.querySelectorAll('tr, ess-row, [role="row"]')).map(r => r.innerText.trim().replace(/\\n/g, ' -- '));
        return {
            tableFound: !!table,
            rows
        };
    })()`);
    console.log('Main Page Rows:', JSON.stringify(pageData, null, 2));

    await cdp.screenshot('main_testers_page.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
