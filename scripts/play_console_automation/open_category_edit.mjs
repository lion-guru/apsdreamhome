import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Edit on App category
    const clickEdit = await cdp.eval(`(() => {
        const sections = Array.from(document.querySelectorAll('section, [role="region"], div'));
        const catSec = sections.find(s => s.innerText.includes('App category') && s.querySelector('button, a'));
        if (catSec) {
            const btns = Array.from(catSec.querySelectorAll('button, a'));
            const editBtn = btns.find(b => b.innerText.trim() === 'Edit');
            if (editBtn) {
                editBtn.click();
                return { clicked: true };
            }
        }
        // Fallback: all Edit buttons
        const allEdits = Array.from(document.querySelectorAll('button, a')).filter(b => b.innerText.trim() === 'Edit');
        if (allEdits[0]) {
            allEdits[0].click();
            return { clicked: true, index: 0 };
        }
        return { error: 'Edit not found' };
    })()`);

    console.log('Click Edit App category:', clickEdit);
    await cdp.wait(2000);

    // Inspect category dropdown
    const catOptions = await cdp.eval(`(() => {
        const selects = Array.from(document.querySelectorAll('mat-select, select, [role="combobox"]'));
        return selects.map(s => ({
            id: s.id,
            role: s.getAttribute('role'),
            text: s.innerText.trim()
        }));
    })()`);
    console.log('Selects:', catOptions);

    await cdp.screenshot('category_edit_open.png');
    cdp.close();
}

main().catch(console.error);
