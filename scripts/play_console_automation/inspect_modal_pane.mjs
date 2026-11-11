import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Click Add from library
    console.log('Clicking Add from library...');
    await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const target = btns.find(b => (b.innerText || '').includes('Add from library'));
        if (target) target.click();
    })()`);
    await cdp.wait(3000);

    // 2. Inspect children of .pane.modal
    const paneInfo = await cdp.eval(`(() => {
        const pane = document.querySelector('.pane.modal') || document.querySelector('.modal');
        if (!pane) return { error: 'No pane.modal found' };

        const allInPane = Array.from(pane.querySelectorAll('*')).map(el => {
            const r = el.getBoundingClientRect();
            return {
                tag: el.tagName,
                cls: el.className,
                text: (el.innerText || '').trim().replace(/\\n/g, ' ').substring(0, 40),
                rect: { x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height) }
            };
        }).filter(el => el.rect.w > 0 && el.rect.h > 0);

        return {
            paneRect: pane.getBoundingClientRect(),
            interactive: allInPane.filter(el => 
                el.tag === 'BUTTON' || 
                el.tag === 'INPUT' || 
                el.tag.includes('CHECKBOX') || 
                el.cls.includes('checkbox') || 
                el.cls.includes('row') || 
                el.text.includes('1.2.5') || 
                el.text.includes('Add to release')
            )
        };
    })()`);

    console.log('Pane modal info:\n', JSON.stringify(paneInfo, null, 2));

    cdp.close();
}

main().catch(console.error);
