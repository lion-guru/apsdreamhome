import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Find the button with Add from library
    const clickBtn = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => (b.innerText || '').includes('Add from library'));
        if (btns.length > 0) {
            btns[0].scrollIntoView({ block: 'center' });
            btns[0].click();
            return { clicked: true, text: btns[0].innerText.trim() };
        }
        return { notFound: true };
    })()`);
    console.log('Click result:', clickBtn);
    await cdp.wait(3000);

    // Let's inspect the entire DOM body text and any new elements
    const overview = await cdp.eval(`(() => {
        // Let's find any element containing 'Add app bundle from library'
        const allEls = Array.from(document.querySelectorAll('*'));
        const titleEl = allEls.find(el => (el.innerText || '').trim() === 'Add app bundle from library');
        
        let containerInfo = null;
        if (titleEl) {
            let p = titleEl.parentElement;
            containerInfo = {
                titleTag: titleEl.tagName,
                parentTag: p ? p.tagName : null,
                parentCls: p ? p.className : null,
                grandParentTag: p && p.parentElement ? p.parentElement.tagName : null,
                grandParentCls: p && p.parentElement ? p.parentElement.className : null
            };
        }

        // Find all checkboxes on document
        const cbs = Array.from(document.querySelectorAll('input[type="checkbox"], [role="checkbox"], material-checkbox')).map(cb => {
            const r = cb.getBoundingClientRect();
            return {
                tag: cb.tagName,
                cls: cb.className,
                ariaLabel: cb.getAttribute('aria-label'),
                rect: { x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height) }
            };
        });

        // Find all buttons on document
        const btns = Array.from(document.querySelectorAll('button')).map(b => {
            const r = b.getBoundingClientRect();
            return {
                text: b.innerText.trim().replace(/\\n/g, ' '),
                disabled: b.disabled,
                rect: { x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height) }
            };
        }).filter(b => b.rect.w > 0);

        return { containerInfo, cbs, btns };
    })()`);

    console.log('Modal inspection:\n', JSON.stringify(overview, null, 2));
    await cdp.screenshot('add_from_library_inspect.png');

    cdp.close();
}

main().catch(console.error);
