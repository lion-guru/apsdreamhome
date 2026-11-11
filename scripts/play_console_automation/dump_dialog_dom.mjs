import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const prepUrl = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/tracks/4700085989411864804/releases/1/prepare`;
    await cdp.navigate(prepUrl);
    await cdp.wait(4000);

    // Click Add from library
    await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const target = btns.find(b => (b.innerText || '').includes('Add from library'));
        if (target) target.click();
    })()`);
    await cdp.wait(3000);

    // Dump all elements inside material-dialog
    const dialogDom = await cdp.eval(`(() => {
        const dialog = document.querySelector('material-dialog, [role="dialog"]');
        if (!dialog) return { error: 'no dialog' };

        const all = Array.from(dialog.querySelectorAll('*')).map(el => {
            const r = el.getBoundingClientRect();
            return {
                tag: el.tagName,
                cls: el.className,
                role: el.getAttribute('role'),
                text: el.innerText ? el.innerText.substring(0, 30).trim() : '',
                rect: { x: Math.round(r.left), y: Math.round(r.top), w: Math.round(r.width), h: Math.round(r.height) }
            };
        }).filter(el => el.rect.w > 0 && el.rect.h > 0);

        return {
            total: all.length,
            clickable: all.filter(el => el.tag.includes('CHECKBOX') || el.role === 'checkbox' || el.tag === 'BUTTON' || el.cls.includes('checkbox') || el.cls.includes('row'))
        };
    })()`);

    console.log('Dialog Clickable Elements:\n', JSON.stringify(dialogDom, null, 2));

    cdp.close();
}

main().catch(console.error);
