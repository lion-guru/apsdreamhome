import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Fill email
    await cdp.eval(`(() => {
        const emailInput = document.querySelector('input[type="email"]');
        if (emailInput) {
            emailInput.focus();
            emailInput.select();
        }
    })()`);
    await cdp.send('Input.insertText', { text: 'techguruabhay@gmail.com' });
    await cdp.wait(500);

    // Select "All Other App Types"
    const radioRes = await cdp.eval(`(() => {
        const radios = Array.from(document.querySelectorAll('material-radio, [role="radio"], label'));
        const target = radios.find(r => (r.innerText || '').includes('All Other App Types'));
        if (target) {
            target.scrollIntoView({ block: 'center' });
            target.click();
            return { clicked: true, text: target.innerText.substring(0, 30) };
        }
        return { error: 'Target radio not found' };
    })()`);
    console.log('Selected radio:', radioRes);
    await cdp.wait(1000);

    // Click Next
    const nextRes = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next');
        if (nextBtn && !nextBtn.disabled) {
            nextBtn.click();
            return { clicked: true };
        }
        return { clicked: false, disabled: nextBtn?.disabled };
    })()`);
    console.log('Clicked Next:', nextRes);
    await cdp.wait(3000);

    await cdp.screenshot('rating_step2.png');

    // Inspect the questions on step 2
    const questions = await cdp.eval(`(() => {
        const sections = Array.from(document.querySelectorAll('h3, [role="heading"], .question, div[class*="question"]')).map(s => s.innerText.trim()).filter(s => s.length > 5);
        const radioGroups = Array.from(document.querySelectorAll('material-radio, [role="radio"]')).map(r => ({
            text: r.innerText.trim(),
            checked: r.getAttribute('aria-checked')
        }));
        return {
            sections,
            radioCount: radioGroups.length,
            sampleRadios: radioGroups.slice(0, 10),
            buttons: Array.from(document.querySelectorAll('button')).map(b => b.innerText.trim()).filter(b => b.length > 0 && b.length < 30)
        };
    })()`);
    console.log('Step 2 details:\n', JSON.stringify(questions, null, 2));

    cdp.close();
}

main().catch(console.error);
