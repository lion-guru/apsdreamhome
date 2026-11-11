import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Remove the invalid merged email
    console.log('Removing shubamchaurasiyanandani87389@gmail.com...');
    await cdp.eval(`(() => {
        const btn = document.querySelector('button[aria-label="Remove email address shubamchaurasiyanandani87389@gmail.com"]');
        if (btn) btn.click();
    })()`);
    await cdp.wait(1000);

    // 2. Remove shubhamchaurasiagkp@gmail.com
    console.log('Removing shubhamchaurasiagkp@gmail.com...');
    await cdp.eval(`(() => {
        const btn = document.querySelector('button[aria-label="Remove email address shubhamchaurasiagkp@gmail.com"]');
        if (btn) btn.click();
    })()`);
    await cdp.wait(1000);

    // 3. Check remaining list
    const remaining = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button[aria-label*="Remove email address"]'))
            .map(b => b.getAttribute('aria-label'));
        return btns;
    })()`);
    console.log('Remaining items:', remaining);

    // 4. Try saving with just nandani87389@gmail.com
    console.log('Clicking Save changes for nandani87389@gmail.com...');
    await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const saveBtn = btns.find(b => (b.innerText || '').trim() === 'Save changes');
        if (saveBtn) saveBtn.click();
    })()`);
    await cdp.wait(3000);

    // 5. Check if confirmation dialog appeared or if errors appeared
    const state = await cdp.eval(`(() => {
        const dialogs = Array.from(document.querySelectorAll('[role="dialog"], focus-trap, .popup, .modal')).map(d => d.innerText.replace(/\\n/g, ' '));
        const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"], [aria-invalid="true"]')).map(e => e.innerText.trim()).filter(Boolean);
        const hasErrorText = document.body.innerText.includes("Your changes couldn't be saved") || document.body.innerText.includes("doesn't exist");
        return {
            dialogs,
            errors,
            hasErrorText
        };
    })()`);
    console.log('Save result with nandani87389@gmail.com:', JSON.stringify(state, null, 2));

    await cdp.screenshot('nandani_only_save.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
