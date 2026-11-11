import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click the category selector button
    const clickRes = await cdp.eval(`(() => {
        const catVal = document.querySelector('[debug-id="category-value"]');
        if (catVal) {
            const btn = catVal.closest('button, [role="button"], [role="combobox"]') || catVal;
            btn.click();
            return { clicked: true, tagName: btn.tagName };
        }
        return { error: 'category-value not found' };
    })()`);
    console.log('Category click result:', clickRes);
    await cdp.wait(2000);

    // List all visible options in popup
    const selectRes = await cdp.eval(`(() => {
        const options = Array.from(document.querySelectorAll('*')).filter(el => {
            const txt = (el.innerText || '').trim();
            return (txt === 'House & Home' || txt === 'Business') && el.children.length === 0;
        });

        if (options.length > 0) {
            const target = options[0];
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { selected: target.innerText.trim(), tag: target.tagName };
        }

        // Return whatever is currently visible
        const anyOptions = Array.from(document.querySelectorAll('material-select-item, [role="option"], li')).map(o => o.innerText.trim());
        return { error: 'Option not clicked', found: anyOptions };
    })()`);
    console.log('Select option result:', selectRes);
    await cdp.wait(2000);

    // Click Save
    const saveRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => b.innerText.trim() === 'Save');
        if (saveBtn) {
            saveBtn.click();
            return { clicked: true };
        }
        return { error: 'Save button not found' };
    })()`);
    console.log('Save result:', saveRes);
    await cdp.wait(4000);

    await cdp.screenshot('category_saved_final.png');
    cdp.close();
}

main().catch(console.error);
