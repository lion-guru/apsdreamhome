import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click Start declaration
    const clickStart = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button, a'));
        const btn = btns.find(b => (b.innerText || '').includes('Start declaration') || (b.innerText || '').includes('Manage'));
        if (btn) {
            btn.scrollIntoView({ block: 'center' });
            btn.click();
            return { clicked: true, text: btn.innerText.trim() };
        }
        return { error: 'Start declaration button not found' };
    })()`);

    console.log('Clicked Start declaration:', clickStart);
    await cdp.wait(5000);

    const formInfo = await cdp.eval(`(() => {
        const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, [role="heading"]')).map(h => h.innerText.trim());
        const radios = Array.from(document.querySelectorAll('input[type="radio"], mat-radio-button')).map(r => ({
            name: r.getAttribute('name'),
            value: r.value || r.getAttribute('aria-label'),
            text: r.closest('label, mat-radio-button')?.innerText?.replace(/\\n+/g, ' ')?.trim(),
            checked: r.checked || r.classList.contains('mat-radio-checked') || r.classList.contains('mdc-radio--selected')
        }));
        const checkboxes = Array.from(document.querySelectorAll('input[type="checkbox"], mat-checkbox')).map(c => ({
            value: c.value || c.getAttribute('aria-label'),
            text: c.closest('label, mat-checkbox')?.innerText?.replace(/\\n+/g, ' ')?.trim(),
            checked: c.checked || c.classList.contains('mat-checkbox-checked') || c.classList.contains('mdc-checkbox--selected')
        }));
        const textareas = Array.from(document.querySelectorAll('textarea, input[type="text"]')).map(t => ({
            name: t.name || t.getAttribute('aria-label'),
            placeholder: t.placeholder
        }));
        return {
            headings,
            radios,
            checkboxes,
            textareas,
            bodySnippet: document.body.innerText.substring(0, 1500)
        };
    })()`);

    console.log('Photo and Video Form Info:', JSON.stringify(formInfo, null, 2));
    await cdp.screenshot('photo_video_form.png');

    cdp.close();
}

main().catch(console.error);
