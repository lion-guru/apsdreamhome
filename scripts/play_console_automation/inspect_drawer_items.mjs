import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const drawerInfo = await cdp.eval(`(() => {
        const sidePanel = document.querySelector('aside, mat-sidenav, [role="region"], div[class*="side-panel"], div[class*="drawer"]');
        const items = Array.from(document.querySelectorAll('div[role="listitem"], li, mat-list-item, div[class*="asset"]')).filter(el => {
            return (el.innerText || '').includes('.png');
        }).map(el => ({
            text: el.innerText.trim().replace(/\\n/g, ' '),
            checkbox: !!el.querySelector('input[type="checkbox"], mat-checkbox'),
            checked: el.querySelector('input[type="checkbox"]')?.checked || el.querySelector('mat-checkbox')?.classList.contains('mat-mdc-checkbox-checked'),
            hasButton: !!el.querySelector('button')
        }));

        const bottomButtons = Array.from(document.querySelectorAll('button')).filter(b => {
            const t = b.innerText.toLowerCase();
            return t.includes('save') || t.includes('apply') || t.includes('done') || t.includes('use') || t.includes('insert') || t.includes('select');
        }).map(b => ({
            text: b.innerText.trim(),
            disabled: b.disabled,
            visible: b.offsetParent !== null
        }));

        return { items, bottomButtons };
    })()`);

    console.log('Drawer Info:', JSON.stringify(drawerInfo, null, 2));
    await cdp.screenshot('drawer_state_now.png');
    cdp.close();
}

main().catch(console.error);
