import { CDPClient } from './cdp_helper.mjs';

async function run() {
    try {
        console.log('Connecting to Play Console via CDPClient...');
        const cdp = new CDPClient();
        await cdp.init();

        const curUrl = await cdp.eval('window.location.href');
        console.log('Current URL:', curUrl);

        // Check current page info
        const pageInfo = await cdp.eval(`(() => {
            return {
                url: window.location.href,
                title: document.title,
                inputs: Array.from(document.querySelectorAll('input, textarea')).map(i => ({
                    tagName: i.tagName,
                    placeholder: i.placeholder,
                    value: i.value,
                    ariaLabel: i.getAttribute('aria-label')
                })),
                buttons: Array.from(document.querySelectorAll('button')).map(b => ({
                    text: b.innerText.trim(),
                    disabled: b.disabled,
                    visible: b.offsetParent !== null
                })).filter(b => b.text.length > 0 && b.text.length < 50),
                alerts: Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(a => a.innerText.trim())
            };
        })()`);
        console.log('Page info:', JSON.stringify(pageInfo, null, 2));

        // Fix release name to keep strictly under 50 chars: "1.2.8 (7) - Web Parity & VIP Car Visits" = 39 chars
        const fixNameResult = await cdp.eval(`(() => {
            const inputs = Array.from(document.querySelectorAll('input'));
            const nameInput = inputs.find(i => 
                i.placeholder?.includes('Release name') || 
                i.getAttribute('aria-label')?.includes('Release name') || 
                i.name?.includes('releaseName')
            );
            if (nameInput) {
                nameInput.value = '1.2.8 (7) - Web Parity & VIP Car Visits';
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                nameInput.dispatchEvent(new Event('change', { bubbles: true }));
                return { fixed: true, val: nameInput.value, len: nameInput.value.length };
            }
            return { fixed: false };
        })()`);
        console.log('Fix release name result:', fixNameResult);

        await cdp.wait(2000);

        // Click Next button
        const clickNextResult = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            const nextBtn = btns.find(b => !b.disabled && b.innerText.trim() === 'Next' && b.offsetParent !== null);
            if (nextBtn) {
                nextBtn.scrollIntoView({ block: 'center' });
                nextBtn.click();
                return { clicked: true, text: nextBtn.innerText.trim() };
            }
            return { clicked: false, available: btns.filter(b => !b.disabled && b.offsetParent !== null).map(b => b.innerText.trim()) };
        })()`);
        console.log('Click Next result:', clickNextResult);

        await cdp.wait(6000);
        await cdp.screenshot('v7_after_next.png');
        console.log('Saved v7_after_next.png');

        // Check if now on Preview/Confirm page
        const previewInfo = await cdp.eval(`(() => {
            const btns = Array.from(document.querySelectorAll('button')).filter(b => !b.disabled && b.offsetParent !== null);
            const rolloutBtn = btns.find(b => 
                b.innerText.includes('Start rollout to Internal testing') ||
                b.innerText.includes('Start rollout') ||
                b.innerText.includes('Save and publish') ||
                b.innerText.includes('Save')
            );
            const errors = Array.from(document.querySelectorAll('.error-message, mat-error, [role="alert"]')).map(a => a.innerText.trim());
            return {
                url: window.location.href,
                hasRolloutBtn: !!rolloutBtn,
                buttonText: rolloutBtn ? rolloutBtn.innerText.trim() : null,
                availableButtons: btns.map(b => b.innerText.trim()),
                errors
            };
        })()`);
        console.log('Preview/Rollout page info:', previewInfo);

        if (previewInfo.hasRolloutBtn && previewInfo.buttonText && !previewInfo.buttonText.includes('Save as draft')) {
            console.log(`Clicking "${previewInfo.buttonText}"...`);
            const confirmClick = await cdp.eval(`(() => {
                const btns = Array.from(document.querySelectorAll('button')).filter(b => !b.disabled && b.offsetParent !== null);
                const rolloutBtn = btns.find(b => 
                    b.innerText.includes('Start rollout to Internal testing') ||
                    b.innerText.includes('Start rollout') ||
                    b.innerText.includes('Save and publish') ||
                    (b.innerText.trim() === 'Save' && !b.innerText.includes('draft'))
                );
                if (rolloutBtn) {
                    rolloutBtn.scrollIntoView({ block: 'center' });
                    rolloutBtn.click();
                    return { clicked: true, text: rolloutBtn.innerText.trim() };
                }
                return { clicked: false };
            })()`);
            console.log('Confirm click result:', confirmClick);

            await cdp.wait(4000);

            // Check if a modal dialog popped up with "Rollout" / "Start rollout" / "Confirm"
            const dialogResult = await cdp.eval(`(() => {
                const dialogBtns = Array.from(document.querySelectorAll('mat-dialog-container button, [role="dialog"] button')).filter(b => !b.disabled);
                const confirmBtn = dialogBtns.find(b => 
                    b.innerText.includes('Rollout') || 
                    b.innerText.includes('Start rollout') || 
                    b.innerText.includes('Save') ||
                    b.innerText.includes('Publish')
                );
                if (confirmBtn) {
                    confirmBtn.click();
                    return { clickedDialog: true, text: confirmBtn.innerText.trim() };
                }
                return { clickedDialog: false, dialogButtons: dialogBtns.map(b => b.innerText.trim()) };
            })()`);
            console.log('Dialog result:', dialogResult);

            await cdp.wait(4000);
            await cdp.screenshot('v7_final_rollout.png');
            console.log('Saved v7_final_rollout.png');
        }

        cdp.close();
        console.log('Finalize release script finished.');
    } catch (e) {
        console.error('Error in finalize script:', e);
        process.exit(1);
    }
}

run();
