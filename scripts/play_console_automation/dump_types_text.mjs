import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const textDump = await cdp.eval(`(() => {
        // Find all headings and items under Data types section
        const lines = document.body.innerText.split('\\n').map(l => l.trim()).filter(Boolean);
        const dataTypesIdx = lines.findIndex(l => l === 'Data types');
        if (dataTypesIdx !== -1) {
            return lines.slice(dataTypesIdx, dataTypesIdx + 50);
        }
        return lines.slice(0, 50);
    })()`);

    console.log(JSON.stringify(textDump, null, 2));
    cdp.close();
}

main().catch(console.error);
