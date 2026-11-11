import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/app-content/overview');
    await cdp.wait(5000);

    const overview = await cdp.eval(`(() => {
        const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6, [role="heading"]')).map(h => h.innerText.trim());
        const cards = Array.from(document.querySelectorAll('.task-card, mat-card, .unified-task-list-card, [role="listitem"]')).map(c => ({
            title: c.querySelector('h3, h4, .title, .task-title')?.innerText?.trim() || '',
            status: c.querySelector('.status, .task-status, [role="status"]')?.innerText?.trim() || '',
            fullText: c.innerText.replace(/\\n+/g, ' ').substring(0, 150)
        }));

        const body = document.body.innerText;
        return {
            headings,
            cards: cards.filter(c => c.title || c.fullText.length > 20),
            bodySnippet: body.substring(0, 1000)
        };
    })()`);

    console.log('App Content Overview:', JSON.stringify(overview, null, 2));
    await cdp.screenshot('app_content_v4_overview.png');

    cdp.close();
}

main().catch(console.error);
