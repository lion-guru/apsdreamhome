import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const structure = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, .dialog, mat-dialog-container');
        if (!dialog) return { error: 'No dialog' };

        const rows = Array.from(dialog.querySelectorAll('.console-form-row, [role="row"], .form-row, div')).filter(d => {
            return (d.innerText || '').includes('Category *');
        });

        const categoryRow = rows[rows.length - 1] || dialog;
        const allDescendants = Array.from(categoryRow.querySelectorAll('*')).map(el => ({
            tag: el.tagName,
            className: el.className,
            text: el.innerText.trim(),
            rect: {
                x: Math.round(el.getBoundingClientRect().x),
                y: Math.round(el.getBoundingClientRect().y),
                w: Math.round(el.getBoundingClientRect().width),
                h: Math.round(el.getBoundingClientRect().height)
            }
        }));

        return {
            categoryRowHTML: categoryRow.innerHTML.substring(0, 1000),
            descendants: allDescendants.filter(d => d.rect.w > 0 && d.rect.h > 0)
        };
    })()`);

    console.log('Category structure:', JSON.stringify(structure, null, 2));
    cdp.close();
}

main().catch(console.error);
