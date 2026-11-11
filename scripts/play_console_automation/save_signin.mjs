import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Click "Add details" button
    const clickAddDetails = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const addBtn = btns.find(b => b.innerText.includes('Add details'));
        if (addBtn) {
            addBtn.scrollIntoView({ block: 'center' });
            addBtn.click();
            return { clicked: true };
        }
        return { error: 'Add details button not found' };
    })()`);
    console.log('Clicked Add details:', clickAddDetails);
    await cdp.wait(2000);

    // Now fill the drawer inputs:
    // 1. Name input
    // 2. Username input
    // 3. Password input
    // 4. Any other instructions textarea
    // 5. Checkbox
    const fillRes = await cdp.eval(`(() => {
        // Name input is the first visible text input without aria-label or specific placeholder
        const textInputs = Array.from(document.querySelectorAll('input[type=\"text\"]')).filter(i => i.offsetParent !== null);
        const nameInput = textInputs[0];
        const usernameInput = textInputs.find(i => (i.getAttribute('aria-label') || '').toLowerCase().includes('username')) || textInputs[1];
        const pwdInput = textInputs.find(i => (i.getAttribute('aria-label') || '').toLowerCase().includes('password')) || textInputs[2];
        const textarea = document.querySelector('textarea');
        const checkbox = document.querySelector('input[type=\"checkbox\"], material-checkbox, [role=\"checkbox\"]');

        const nativeSet = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        const nativeSetTextarea = Object.getOwnPropertyDescriptor(window.HTMLTextAreaElement.prototype, 'value').set;

        if (nameInput) {
            nativeSet.call(nameInput, 'Reviewer Access');
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            nameInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (usernameInput) {
            nativeSet.call(usernameInput, 'testassociate@example.com');
            usernameInput.dispatchEvent(new Event('input', { bubbles: true }));
            usernameInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (pwdInput) {
            nativeSet.call(pwdInput, 'Aps@2026');
            pwdInput.dispatchEvent(new Event('input', { bubbles: true }));
            pwdInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (textarea) {
            nativeSetTextarea.call(textarea, 'Use Email Login tab with provided credentials to access associate dashboard, listings, and customer bookings.');
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            textarea.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (checkbox) {
            checkbox.click();
        }

        // Find "Add" button in modal/drawer
        const addDrawerBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Add' && b.offsetParent !== null);

        return {
            nameVal: nameInput?.value,
            userVal: usernameInput?.value,
            pwdVal: pwdInput?.value,
            notesVal: textarea?.value,
            checkboxChecked: checkbox?.checked || checkbox?.getAttribute('aria-checked'),
            addDrawerBtnFound: !!addDrawerBtn,
            addDrawerBtnDisabled: addDrawerBtn?.disabled
        };
    })()`);
    console.log('Fill result in drawer:\n', JSON.stringify(fillRes, null, 2));
    await cdp.wait(1000);

    // Click "Add" button in drawer
    const clickDrawerAdd = await cdp.eval(`(() => {
        const addDrawerBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Add' && b.offsetParent !== null);
        if (addDrawerBtn && !addDrawerBtn.disabled) {
            addDrawerBtn.click();
            return { clicked: true };
        }
        return { clicked: false, disabled: addDrawerBtn?.disabled };
    })()`);
    console.log('Clicked Add inside drawer:', clickDrawerAdd);
    await cdp.wait(2000);

    // Now click the main "Save" button
    const clickSave = await cdp.eval(`(() => {
        const saveBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Save' && b.offsetParent !== null);
        if (saveBtn && !saveBtn.disabled) {
            saveBtn.click();
            return { saved: true };
        }
        return { saved: false, disabled: saveBtn?.disabled };
    })()`);
    console.log('Clicked main Save:', clickSave);
    await cdp.wait(3000);

    await cdp.screenshot('signin_saved.png');

    // Navigate back to overview
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-content/overview`);
    await cdp.wait(3000);

    cdp.close();
}

main().catch(console.error);
