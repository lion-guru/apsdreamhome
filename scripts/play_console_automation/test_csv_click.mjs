import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.send('Page.setInterceptFileChooserDialog', { enabled: true });

    let fileChooserEvent = null;
    cdp.ws.on('message', (data) => {
        const msg = JSON.parse(data);
        if (msg.method === 'Page.fileChooserOpened') {
            fileChooserEvent = msg.params;
            console.log('FileChooserOpened:', msg.params);
        }
    });

    console.log('Clicking "Upload CSV file" button...');
    const clickRes = await cdp.eval(`(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const csvBtn = btns.find(b => (b.innerText || '').includes('Upload CSV file'));
        if (csvBtn) {
            csvBtn.click();
            return { clicked: true };
        }
        return { clicked: false };
    })()`);
    console.log('Click result:', clickRes);

    await cdp.wait(3000);

    // Also check if any file input appeared in the DOM
    const fileInputs = await cdp.eval(`(() => {
        return Array.from(document.querySelectorAll('input[type="file"]')).map(i => ({
            accept: i.accept,
            outer: i.outerHTML
        }));
    })()`);
    console.log('File inputs in DOM:', fileInputs);

    await cdp.screenshot('csv_clicked.png');
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
