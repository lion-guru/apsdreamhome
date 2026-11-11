import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/app-dashboard');
    await cdp.wait(5000);

    const status = await cdp.eval(`(() => {
        return {
            text: document.body.innerText.substring(0, 1500)
        };
    })()`);

    console.log('Dashboard text:', status.text);
    await cdp.screenshot('dashboard_10_of_11_verified.png');

    cdp.close();
}

main().catch(console.error);
