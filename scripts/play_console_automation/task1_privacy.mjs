import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();
    console.log(`Connected to app ${cdp.appId}`);

    // Check where Privacy Policy is
    const clickRes = await cdp.eval(`(() => {
        // Find the section or card that has 'Privacy policy' as heading
        const headings = Array.from(document.querySelectorAll('h3, h2, div[role="heading"]'));
        const privacyH = headings.find(h => h.innerText.trim() === 'Privacy policy');
        if (!privacyH) return { error: 'Privacy policy heading not found' };

        // Find button inside this card
        let parent = privacyH;
        for (let i = 0; i < 6; i++) {
            if (!parent) break;
            const btn = parent.querySelector('button, a[role="button"], a');
            if (btn && btn.innerText.includes('declaration')) {
                btn.scrollIntoView({ block: 'center' });
                btn.click();
                return { clicked: true, text: btn.innerText.trim() };
            }
            parent = parent.parentElement;
        }
        return { error: 'Start declaration button not found for privacy' };
    })()`);

    console.log('Click result:', clickRes);
    await cdp.wait(4000);

    console.log('Current URL after click:', await cdp.eval('window.location.href'));
    await cdp.screenshot('privacy_policy_page.png');

    // Inspect inputs on the page
    const inputs = await cdp.eval(`(() => {
        return Array.from(document.querySelectorAll('input, textarea')).map(i => ({
            tag: i.tagName,
            type: i.type,
            placeholder: i.placeholder,
            value: i.value,
            ariaLabel: i.getAttribute('aria-label')
        }));
    })()`);
    console.log('Inputs found:', inputs);

    cdp.close();
}

main().catch(console.error);
