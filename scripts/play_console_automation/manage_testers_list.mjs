import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click on the arrow or row to edit "Internal Testers"
    console.log('Opening Internal Testers list...');
    await cdp.eval(`(() => {
        const arrow = document.querySelector('i[aria-label*="Internal Testers"], [aria-label*="Edit email list"], [aria-label*="Internal Testers"] button');
        if (arrow) {
            arrow.click();
            return 'clicked aria-label';
        }
        const rows = Array.from(document.querySelectorAll('tr, div, [role="row"]')).filter(r => r.innerText.includes('Internal Testers'));
        for (const r of rows) {
            const btn = r.querySelector('button, [role="button"], a');
            if (btn) {
                btn.click();
                return 'clicked button in row';
            }
        }
        return 'not found';
    })()`);
    await cdp.wait(2000);

    // 2. Check if dialog opened
    const dialogOpen = await cdp.eval(`(() => {
        const dialog = document.querySelector('[role="dialog"], focus-trap');
        if (!dialog) return false;
        return {
            title: dialog.querySelector('h1, h2, [role="heading"]')?.innerText || 'Dialog open',
            emails: Array.from(dialog.querySelectorAll('div, tr, li, [role="row"]'))
                .filter(el => el.querySelector('button[aria-label*="Remove"]'))
                .map(el => el.innerText.trim().replace(/\\n/g, ' -- '))
        };
    })()`);
    console.log('Dialog state:', JSON.stringify(dialogOpen, null, 2));

    await cdp.screenshot('opened_list_dialog.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
