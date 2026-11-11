import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const fileInputs = await cdp.eval(`(() => {
        const foundInputs = Array.from(document.querySelectorAll('input[type="file"]')).map(i => ({
            id: i.id,
            name: i.name,
            accept: i.accept,
            ariaLabel: i.getAttribute('aria-label'),
            className: i.className
        }));
        const uploadBtns = Array.from(document.querySelectorAll('button, a, label, span')).filter(el => (el.innerText || '').includes('Upload CSV file')).map(el => ({
            tag: el.tagName,
            text: el.innerText.trim(),
            html: el.outerHTML
        }));
        return { foundInputs, uploadBtns };
    })()`);

    console.log('File upload info:', JSON.stringify(fileInputs, null, 2));
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
