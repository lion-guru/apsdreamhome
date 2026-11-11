import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const url = `https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/internal-testing?tab=testers`;
    console.log('Navigating to Internal Testing testers tab:', url);
    await cdp.navigate(url);
    await cdp.wait(4000);

    const info = await cdp.eval(`(() => {
        const body = document.body.innerText;
        const lists = Array.from(document.querySelectorAll('tr, [role="row"], material-list-item, div[class*="tester"]')).map(el => ({
            text: el.innerText.trim().replace(/\\n/g, ' '),
            hasCheckbox: !!el.querySelector('mat-checkbox, input[type="checkbox"]'),
            buttons: Array.from(el.querySelectorAll('button, a')).map(b => b.innerText.trim() || b.getAttribute('aria-label') || '')
        })).filter(l => l.text.includes('Tester') || l.text.includes('@') || l.text.includes('email') || l.text.includes('list'));

        const allButtons = Array.from(document.querySelectorAll('button, a')).map(b => ({
            tag: b.tagName,
            text: b.innerText.trim().replace(/\\n/g, ' '),
            ariaLabel: b.getAttribute('aria-label') || '',
            href: b.getAttribute('href') || ''
        })).filter(b => b.text.includes('list') || b.text.includes('email') || b.text.includes('tester') || b.ariaLabel.includes('edit') || b.ariaLabel.includes('Edit') || b.ariaLabel.includes('arrow'));

        return { lists, allButtons, bodySnippet: body.slice(0, 800).replace(/\\n/g, ' ') };
    })()`);

    console.log('Internal testers state:', JSON.stringify(info, null, 2));
    await cdp.screenshot('internal_testers_page.png');
    cdp.close();
}

main().catch(console.error);
