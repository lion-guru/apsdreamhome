import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click the first "Start declaration" button (Privacy Policy)
    const res = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button')).filter(b => b.innerText.includes('Start declaration'));
        if (btns.length === 0) return { error: 'No start declaration button found' };
        btns[0].scrollIntoView({ block: 'center' });
        btns[0].click();
        return { success: true, count: btns.length, text: btns[0].innerText.trim() };
    })()`);

    console.log('Clicked first button:', res);
    await cdp.wait(3000);

    const pageInfo = await cdp.eval(`({
        title: document.title,
        url: window.location.href,
        inputs: Array.from(document.querySelectorAll('input, textarea')).map(i => ({
            tag: i.tagName,
            type: i.type,
            placeholder: i.placeholder,
            value: i.value,
            id: i.id,
            ariaLabel: i.getAttribute('aria-label')
        })),
        buttons: Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(t => t.length > 0 && t.length < 30)
    })`);

    console.log('Page info after click:\n', JSON.stringify(pageInfo, null, 2));
    await cdp.screenshot('privacy_policy_opened.png');
    cdp.close();
}

main().catch(console.error);
