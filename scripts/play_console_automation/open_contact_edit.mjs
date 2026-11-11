import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click "Not now" on the popup
    const clickNotNow = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const btn = btns.find(b => b.innerText.trim() === 'Not now');
        if (btn) {
            btn.click();
            return { clicked: true };
        }
        return { error: 'Not now not found' };
    })()`);
    console.log('Clicked Not now:', clickNotNow);
    await cdp.wait(2000);

    // 2. Click Edit on "Store listing contact details"
    const clickEditContact = await cdp.eval(`(() => {
        const sections = Array.from(document.querySelectorAll('section, [role="region"], div'));
        const contactSec = sections.find(s => s.innerText.includes('Store listing contact details') && s.querySelector('button, a'));
        if (contactSec) {
            const btns = Array.from(contactSec.querySelectorAll('button, a'));
            const editBtn = btns.find(b => b.innerText.trim() === 'Edit');
            if (editBtn) {
                editBtn.click();
                return { clicked: true };
            }
        }
        // Fallback: look for Edit button near the bottom
        const allEdits = Array.from(document.querySelectorAll('button, a')).filter(b => b.innerText.trim() === 'Edit');
        if (allEdits[allEdits.length - 1]) {
            allEdits[allEdits.length - 1].click();
            return { clicked: true, index: allEdits.length - 1 };
        }
        return { error: 'Edit contact not found' };
    })()`);
    console.log('Clicked Edit contact:', clickEditContact);
    await cdp.wait(3000);

    // 3. Inspect inputs
    const inputsInfo = await cdp.eval(`(() => {
        const inputs = Array.from(document.querySelectorAll('input')).map(i => ({
            id: i.id,
            type: i.type,
            name: i.name,
            ariaLabel: i.getAttribute('aria-label') || '',
            placeholder: i.placeholder,
            value: i.value,
            rect: {
                x: Math.round(i.getBoundingClientRect().x),
                y: Math.round(i.getBoundingClientRect().y),
                w: Math.round(i.getBoundingClientRect().width),
                h: Math.round(i.getBoundingClientRect().height)
            }
        }));
        return inputs.filter(i => i.rect.w > 0 && i.rect.h > 0);
    })()`);
    console.log('Visible inputs:', inputsInfo);
    await cdp.screenshot('contact_details_edit.png');

    cdp.close();
}

main().catch(console.error);
