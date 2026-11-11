import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const actions = await cdp.eval(`(() => {
        const res = {};
        // 1. Select Yes for Encrypted in transit (the second Yes radio)
        const yesRadios = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).filter(r => (r.innerText || '').trim() === 'Yes');
        if (yesRadios.length > 1) {
            yesRadios[1].scrollIntoView({ block: 'center' });
            yesRadios[1].click();
            res.encryptedYes = true;
        }

        // 2. Check "Username and password"
        const cbs = Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"]'));
        const userPwdCb = cbs.find(c => (c.innerText || '').includes('Username and password'));
        if (userPwdCb) {
            userPwdCb.scrollIntoView({ block: 'center' });
            userPwdCb.click();
            res.userPwdChecked = true;
        }

        return res;
    })()`);

    console.log('Actions taken:', actions);
    await cdp.wait(2000);

    const followUps = await cdp.eval(`(() => {
        const text = document.body.innerText;
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
            text: r.innerText.trim(),
            checked: r.getAttribute('aria-checked')
        }));
        const inputs = Array.from(document.querySelectorAll('input[type="text"]')).map(i => ({
            val: i.value,
            placeholder: i.placeholder,
            ariaLabel: i.getAttribute('aria-label')
        }));
        return {
            radiosCount: radios.length,
            radios,
            inputs,
            textSnippet: text.substring(0, 1500)
        };
    })()`);

    console.log('Follow-ups:\n', JSON.stringify(followUps, null, 2));
    await cdp.screenshot('data_sec_step1_answered.png');
    cdp.close();
}

main().catch(console.error);
