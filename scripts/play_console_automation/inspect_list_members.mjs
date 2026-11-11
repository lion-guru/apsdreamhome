import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Let's click on the arrow next to "Internal Testers"
    console.log('Inspecting "Internal Testers"...');
    await cdp.eval(`(() => {
        const rows = Array.from(document.querySelectorAll('tr, ess-row, [role="row"]'));
        const targetRow = rows.find(r => r.innerText.includes('Internal Testers') && !r.innerText.includes('List name'));
        if (targetRow) {
            const btn = targetRow.querySelector('button, [role="button"], i, a');
            if (btn) btn.click();
        }
    })()`);
    await cdp.wait(2000);

    const dialogData = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        if (!dialog) return { error: 'No dialog' };
        const title = dialog.querySelector('h1, h2, [role="heading"]')?.innerText || '';
        const listName = dialog.querySelector('input[aria-label="List name"]')?.value || '';
        const emails = Array.from(dialog.querySelectorAll('div, tr, li, [role="row"]'))
            .filter(el => el.querySelector('button[aria-label*="Remove email address"]'))
            .map(el => {
                const btn = el.querySelector('button[aria-label*="Remove email address"]');
                return btn.getAttribute('aria-label');
            });
        return {
            title,
            listName,
            emails: [...new Set(emails)]
        };
    })()`);
    console.log('Dialog Data:', JSON.stringify(dialogData, null, 2));

    await cdp.screenshot('internal_testers_inspected.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
