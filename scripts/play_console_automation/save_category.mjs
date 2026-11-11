import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Category dropdown (second dropdown)
    const openRes = await cdp.eval(`(() => {
        const selects = Array.from(document.querySelectorAll('mat-select, [role="combobox"]'));
        if (selects[1]) {
            selects[1].click();
            return { clicked: true, text: selects[1].innerText };
        }
        return { error: 'Dropdown not found' };
    })()`);
    console.log('Opened category dropdown:', openRes);
    await cdp.wait(2000);

    // List options and pick House & Home or Business
    const selectOption = await cdp.eval(`(() => {
        const options = Array.from(document.querySelectorAll('mat-option, [role="option"]'));
        const optNames = options.map(o => o.innerText.trim());
        
        // Prefer House & Home, else Business
        const target = options.find(o => o.innerText.trim().toLowerCase() === 'house & home') ||
                       options.find(o => o.innerText.trim().toLowerCase().includes('house')) ||
                       options.find(o => o.innerText.trim().toLowerCase() === 'business');
        
        if (target) {
            target.click();
            return { selected: target.innerText.trim(), totalOptions: options.length };
        }
        return { error: 'Target option not found', available: optNames.slice(0, 15) };
    })()`);

    console.log('Selected option:', selectOption);
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
    console.log('Clicked Save:', saveRes);
    await cdp.wait(4000);

    await cdp.screenshot('category_saved.png');
    cdp.close();
}

main().catch(console.error);
