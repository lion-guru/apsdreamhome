import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Enable Clipboard permission or get link element
    const linkRes = await cdp.eval(`(() => {
        const all = Array.from(document.querySelectorAll('a, button, [role="button"]'));
        const copyBtn = all.find(el => (el.innerText || '').includes('Copy link'));
        
        let url = null;
        if (copyBtn) {
            // Check if it has an href
            url = copyBtn.getAttribute('href');
            // If button, look at data attributes or nearby elements
            if (!url) {
                const parent = copyBtn.closest('div, section');
                const nearbyLinks = parent ? Array.from(parent.querySelectorAll('a')).map(a => a.href) : [];
                return { hasBtn: true, text: copyBtn.innerText.trim(), href: url, nearbyLinks };
            }
        }
        return { hasBtn: !!copyBtn, url };
    })()`);
    console.log('Copy button inspection:', linkRes);

    // Click "Copy link"
    await cdp.eval(`(() => {
        const all = Array.from(document.querySelectorAll('a, button, [role="button"]'));
        const copyBtn = all.find(el => (el.innerText || '').includes('Copy link'));
        if (copyBtn) copyBtn.click();
    })()`);
    await cdp.wait(2000);

    // Read clipboard
    const clipText = await cdp.eval(`navigator.clipboard.readText().catch(e => e.message)`);
    console.log('Clipboard Opt-In URL:', clipText);

    // If clipboard read failed, let's inspect the link format
    // Internal test link standard format: https://play.google.com/apps/internaltest/<package_hash>
    const htmlSnippet = await cdp.eval(`document.body.innerHTML.match(/https:\\/\\/play\\.google\\.com\\/apps\\/internaltest\\/[^"'\s<]+/g)`);
    console.log('HTML regex match:', htmlSnippet);

    cdp.close();
}

main().catch(console.error);
