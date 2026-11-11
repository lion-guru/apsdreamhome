import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Find heading "SMS and Call log permissions"
    const clickRes = await cdp.eval(`(() => {
        const headings = Array.from(document.querySelectorAll('h3, h2, [role="heading"]'));
        const targetH = headings.find(h => (h.innerText || '').includes('SMS and Call log'));
        if (!targetH) return { error: 'Heading not found' };

        let p = targetH.parentElement;
        for (let i = 0; i < 6 && p; i++) {
            const btn = p.querySelector('button');
            if (btn && btn.innerText.includes('declaration')) {
                btn.scrollIntoView({ block: 'center' });
                btn.click();
                return { clicked: true, text: btn.innerText.trim() };
            }
            p = p.parentElement;
        }
        return { error: 'Button not found' };
    })()`);
    console.log('Clicked SMS and Call log:', clickRes);
    await cdp.wait(3000);

    const info = await cdp.eval(`({
        url: window.location.href,
        title: document.title,
        bodySnippet: document.body.innerText.substring(0, 1500)
    })`);
    console.log('Page info:\n', JSON.stringify(info, null, 2));
    await cdp.screenshot('sms_declaration_page.png');
    cdp.close();
}

main().catch(console.error);
