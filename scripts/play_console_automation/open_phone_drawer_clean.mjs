import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Close open side panel/dialog
    console.log('Closing all open dialogs/panels...');
    await cdp.eval(`(() => {
        const closeBtns = Array.from(document.querySelectorAll('button[aria-label*="Close"], button[debug-id="close-button"]'));
        for (const b of closeBtns) {
            b.click();
        }
    })()`);
    await cdp.wait(1500);

    // 2. Locate Phone screenshots section specifically
    console.log('Finding Phone screenshots section...');
    const clickRes = await cdp.eval(`(() => {
        // Find Phone screenshots heading or text
        const allElements = Array.from(document.querySelectorAll('*'));
        const phoneHeading = allElements.find(el => {
            const t = (el.innerText || '').trim();
            return (t === 'Phone screenshots' || t === 'Phone assets') && el.children.length === 0;
        });

        if (!phoneHeading) return { error: 'Phone screenshots heading not found' };

        // Traverse up to find container with Add assets button
        let curr = phoneHeading;
        while (curr && !curr.querySelector('button') && curr !== document.body) {
            curr = curr.parentElement;
        }

        // Search for Add assets button inside or below this section
        let addBtn = null;
        let walk = curr;
        for (let i = 0; i < 5 && walk; i++) {
            const btns = Array.from(walk.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Add assets');
            if (btns.length > 0) {
                addBtn = btns[0];
                break;
            }
            walk = walk.parentElement;
        }

        if (addBtn) {
            addBtn.scrollIntoView({ block: 'center' });
            addBtn.click();
            return { success: true, parentText: addBtn.parentElement?.innerText?.slice(0, 100) };
        }

        return { error: 'Add assets button not found for Phone screenshots' };
    })()`);

    console.log('Click result:', clickRes);
    await cdp.wait(2000);

    // 3. Inspect drawer text
    const drawerInfo = await cdp.eval(`(() => {
        const sidePanel = document.querySelector('aside, mat-sidenav, [role="region"], div[class*="side-panel"], [role="dialog"]');
        return {
            text: sidePanel ? sidePanel.innerText.slice(0, 300).replace(/\\n/g, ' ') : 'no panel'
        };
    })()`);

    console.log('Drawer text now:', drawerInfo);
    cdp.close();
}

main().catch(console.error);
