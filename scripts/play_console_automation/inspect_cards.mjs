import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    const overviewData = await cdp.eval(`(() => {
        // Collect all cards
        const results = [];
        const headings = Array.from(document.querySelectorAll('h3, h2, div[role="heading"]'));
        for (const h of headings) {
            const title = h.innerText.trim();
            // Let's find button inside the card or adjacent
            let card = h;
            let btn = null;
            for (let i = 0; i < 6; i++) {
                card = card?.parentElement;
                if (!card) break;
                const foundBtn = card.querySelector('button');
                if (foundBtn && (foundBtn.innerText.includes('declaration') || foundBtn.innerText.includes('Manage') || foundBtn.innerText.includes('Start'))) {
                    btn = foundBtn;
                    break;
                }
            }
            if (btn) {
                results.push({
                    title,
                    btnText: btn.innerText.trim()
                });
            }
        }
        return {
            title: document.title,
            tabText: document.querySelector('.mat-tab-label-active, [role="tab"][aria-selected="true"]')?.innerText,
            countText: document.querySelector('h2, h3')?.innerText,
            results
        };
    })()`);

    console.log(JSON.stringify(overviewData, null, 2));
    cdp.close();
}

main().catch(console.error);
