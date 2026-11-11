import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/privacy-policy`);
    await cdp.wait(2000);

    // Focus input and clear it
    await cdp.eval(`(() => {
        const input = document.querySelector('input');
        input.focus();
        input.select();
    })()`);

    // Use CDP Input.insertText
    await cdp.send('Input.insertText', { text: 'https://apsdreamhome.com/privacy' });
    await cdp.wait(1000);

    const check = await cdp.eval(`(() => {
        const input = document.querySelector('input');
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save');
        return {
            val: input.value,
            saveDisabled: saveBtn ? saveBtn.disabled : null
        };
    })()`);
    console.log('After Input.insertText:', check);

    if (check.saveDisabled === false) {
        await cdp.eval(`Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save').click()`);
        console.log('Clicked enabled Save button!');
        await cdp.wait(3000);
    }

    await cdp.screenshot('privacy_policy_cdp_type.png');
    cdp.close();
}

main().catch(console.error);
