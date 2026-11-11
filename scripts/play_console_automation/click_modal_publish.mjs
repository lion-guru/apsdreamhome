import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const info = await cdp.eval(`(() => {
        const dialog = document.querySelector('mat-dialog-container, [role="dialog"], .mat-dialog-container');
        if (!dialog) return { error: 'No dialog' };
        
        const allElements = Array.from(dialog.querySelectorAll('*'));
        const clickable = allElements.filter(el => {
            const txt = (el.innerText || '').trim();
            return txt.includes('Save and publish') || txt.includes('Publish') || txt.includes('Cancel');
        }).map(el => ({
            tag: el.tagName,
            text: el.innerText.trim(),
            className: el.className,
            attributes: Array.from(el.attributes).map(a => a.name + '=' + a.value)
        }));

        // Find the specific button with "Save and publish"
        const target = allElements.find(el => (el.tagName === 'BUTTON' || el.tagName === 'SPAN' || el.getAttribute('role') === 'button') && el.innerText.trim() === 'Save and publish');
        let clickResult = false;
        if (target) {
            target.click();
            clickResult = true;
        }

        return {
            clickable,
            clickResult
        };
    })()`);

    console.log('Dialog Elements and Click:', JSON.stringify(info, null, 2));
    await cdp.wait(5000);
    await cdp.screenshot('after_modal_click.png');

    const curUrl = await cdp.eval(`window.location.href`);
    console.log('Current URL:', curUrl);

    cdp.close();
}

main().catch(console.error);
