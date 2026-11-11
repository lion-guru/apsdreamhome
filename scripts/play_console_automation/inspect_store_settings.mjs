import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/store-settings');
    await cdp.wait(5000);

    const info = await cdp.eval(`(() => {
        const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4')).map(h => h.innerText.trim());
        const selects = Array.from(document.querySelectorAll('mat-select, select')).map(s => ({
            id: s.id,
            ariaLabel: s.getAttribute('aria-label'),
            text: s.innerText.trim()
        }));
        const inputs = Array.from(document.querySelectorAll('input')).map(i => ({
            id: i.id,
            type: i.type,
            name: i.name,
            ariaLabel: i.getAttribute('aria-label'),
            value: i.value,
            placeholder: i.placeholder
        }));

        return {
            headings,
            selects,
            inputs,
            bodySnippet: document.body.innerText.substring(0, 1500)
        };
    })()`);

    console.log('Store Settings Info:', JSON.stringify(info, null, 2));
    await cdp.screenshot('store_settings.png');

    cdp.close();
}

main().catch(console.error);
