import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const res = await cdp.eval(`(() => {
        const ths = Array.from(document.querySelectorAll('th, ess-header-cell, [role="columnheader"]')).map(h => h.innerText.trim());
        const tds = Array.from(document.querySelectorAll('td, ess-cell, [role="cell"]')).map(c => c.innerText.trim());
        return { ths, tds };
    })()`);

    console.log('Table details:', res);
    cdp.close();
}

try {
    await main();
} catch (e) {
    console.error(e);
}
