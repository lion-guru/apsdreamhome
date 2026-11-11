import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Find all tabs on page
    const tabs = await cdp.eval(`(() => {
        const tabEls = Array.from(document.querySelectorAll('[role="tab"], .mat-tab-label, mat-tab-header .mdc-tab')).map(t => ({
            text: t.innerText.trim(),
            selected: t.getAttribute('aria-selected') === 'true' || t.classList.contains('mdc-tab--active')
        }));
        return tabEls;
    })()`);

    console.log('Available tabs on track page:', tabs);

    // 2. Click Testers tab if not selected
    const clickTesters = await cdp.eval(`(() => {
        const tabEls = Array.from(document.querySelectorAll('[role="tab"], .mat-tab-label, mat-tab-header .mdc-tab, button, a'));
        const testersTab = tabEls.find(t => (t.innerText || '').trim().toLowerCase() === 'testers');
        if (testersTab) {
            testersTab.scrollIntoView({ block: 'center' });
            testersTab.click();
            return { clicked: true };
        }
        return { clicked: false };
    })()`);

    console.log('Clicked Testers tab:', clickTesters);
    await cdp.wait(3000);

    // 3. Inspect the testers section
    const testersInfo = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const lists = Array.from(document.querySelectorAll('tr, [role="row"], div[role="listitem"], mat-row')).map(r => ({
            text: r.innerText.trim().replace(/\\n/g, ' '),
            editBtn: !!r.querySelector('button, a[href*="edit"], a[aria-label*="Edit"]')
        }));

        const buttons = Array.from(document.querySelectorAll('button')).map(b => ({
            text: b.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: b.getAttribute('aria-label') || '',
            debugId: b.getAttribute('debug-id') || '',
            visible: b.offsetParent !== null
        })).filter(b => b.visible && b.text.length < 40);

        return { lists, buttons, snippet: body.slice(0, 1500).replace(/\\n/g, ' ') };
    })()`);

    console.log('Testers Info:', JSON.stringify(testersInfo, null, 2));
    cdp.close();
}

main().catch(console.error);
