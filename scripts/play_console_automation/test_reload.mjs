import { CDPClient } from './cdp_helper.mjs';

async function test() {
    const cdp = new CDPClient();
    await cdp.init();
    await cdp.send('Page.reload');
    await cdp.wait(4000);
    const val = await cdp.eval('document.querySelector("input")?.value');
    console.log('Value on fresh reload:', val);
    cdp.close();
}

test().catch(console.error);
