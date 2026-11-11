import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const questions = await cdp.eval(`(() => {
        const blocks = Array.from(document.querySelectorAll('section, [class*="question"], div[class*="content"]')).filter(b => b.innerText && b.innerText.includes('?'));
        return blocks.map(b => b.innerText.split('\\n')[0]);
    })()`);

    console.log('Question texts:\n', JSON.stringify(questions, null, 2));
    cdp.close();
}

main().catch(console.error);
