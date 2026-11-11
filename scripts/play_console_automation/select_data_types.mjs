import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // 1. Check Approximate location
    const clickLoc = await cdp.eval(`(() => {
        const labels = Array.from(document.querySelectorAll('label, material-checkbox, [role="checkbox"]'));
        const approx = labels.find(l => (l.innerText || '').includes('Approximate location'));
        if (approx) {
            approx.scrollIntoView({ block: 'center' });
            approx.click();
            return { clickedApprox: true };
        }
        return { error: 'Approximate location not found' };
    })()`);
    console.log('Location:', clickLoc);
    await cdp.wait(500);

    // 2. Expand Personal info, Financial info, Photos and videos
    const expandRes = await cdp.eval(`(() => {
        const results = [];
        const sections = ['Personal info', 'Financial info', 'Photos and videos'];
        for (const sec of sections) {
            // Find heading with sec text
            const headings = Array.from(document.querySelectorAll('*')).filter(el => 
                el.children.length === 0 && el.innerText.trim() === sec
            );
            if (headings.length > 0) {
                let p = headings[0].parentElement;
                for (let i = 0; i < 5 && p; i++) {
                    const showBtn = p.querySelector('button, [role="button"]');
                    if (showBtn && (showBtn.innerText || '').includes('Show')) {
                        showBtn.click();
                        results.push('expanded ' + sec);
                        break;
                    }
                    p = p.parentElement;
                }
            }
        }
        return results;
    })()`);
    console.log('Expanded sections:', expandRes);
    await cdp.wait(1500);

    // 3. Select items: Name, Email address, Phone number, Purchase history, Photos
    const selectItems = await cdp.eval(`(() => {
        const targets = ['Name', 'Email address', 'Phone number', 'Purchase history', 'Photos'];
        const clicked = [];
        const checkboxes = Array.from(document.querySelectorAll('material-checkbox, [role="checkbox"], label'));
        for (const target of targets) {
            const found = checkboxes.find(c => {
                const text = (c.innerText || '').trim();
                return text === target || text.startsWith(target + '\\n') || text.startsWith(target + ' help');
            });
            if (found && found.getAttribute('aria-checked') !== 'true') {
                found.scrollIntoView({ block: 'center' });
                found.click();
                clicked.push(target);
            }
        }
        return clicked;
    })()`);
    console.log('Selected items:', selectItems);
    await cdp.wait(1000);

    // 4. Click Next
    const clickNext = await cdp.eval(`(() => {
        const nextBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'Next' && !b.disabled);
        if (nextBtn) {
            nextBtn.scrollIntoView({ block: 'center' });
            nextBtn.click();
            return { clicked: true };
        }
        return { error: 'Next button not found' };
    })()`);
    console.log('Clicked Next to Step 4:', clickNext);
    await cdp.wait(4000);

    await cdp.screenshot('data_safety_step4_handling.png');

    const state = await cdp.eval(`(() => ({
        url: window.location.href,
        step: document.querySelector('.stepper-step-active, [aria-selected="true"]')?.innerText,
        bodySnippet: document.body.innerText.substring(0, 1000)
    }))()`);
    console.log('Step 4 state:\n', JSON.stringify(state, null, 2));

    cdp.close();
}

main().catch(console.error);
