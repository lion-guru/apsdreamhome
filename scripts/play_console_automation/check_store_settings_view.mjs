import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/store-settings');
    await cdp.wait(5000);

    const values = await cdp.eval(`(() => {
        const text = document.body.innerText;
        return {
            snippet: text.substring(0, 1500)
        };
    })()`);

    console.log('Store Settings text:', values.snippet);
    await cdp.screenshot('store_settings_current_view.png');

    cdp.close();
}

main().catch(console.error);
