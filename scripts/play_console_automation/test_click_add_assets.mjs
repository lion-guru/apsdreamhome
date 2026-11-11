import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    console.log('Clicking Add assets on Phone screenshots (idx 2)...');
    const clickRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').trim() === 'Add assets');
        if (btns[2]) {
            btns[2].scrollIntoView({ block: 'center' });
            btns[2].click();
            return { clicked: true };
        }
        return { clicked: false };
    })()`);

    console.log('Clicked result:', clickRes);
    await cdp.wait(2000);

    // Inspect what opened: dialog, drawer, file input, etc.
    const opened = await cdp.eval(`(() => {
        const dialogs = Array.from(document.querySelectorAll('[role="dialog"], mat-dialog-container, [role="region"], .side-drawer, aside')).map(d => ({
            tag: d.tagName,
            role: d.getAttribute('role'),
            text: d.innerText.slice(0, 200)
        }));

        const fileInputs = Array.from(document.querySelectorAll('input[type="file"]')).map(i => ({
            id: i.id,
            name: i.name,
            accept: i.accept,
            multiple: i.multiple,
            ariaLabel: i.getAttribute('aria-label') || ''
        }));

        const buttons = Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(Boolean);

        return { dialogs, fileInputs, uploadButtons: buttons.filter(b => b.toLowerCase().includes('upload') || b.toLowerCase().includes('select') || b.toLowerCase().includes('choose')) };
    })()`);

    console.log('Opened state:', JSON.stringify(opened, null, 2));
    await cdp.screenshot('add_assets_clicked.png');

    cdp.close();
}

main().catch(console.error);
