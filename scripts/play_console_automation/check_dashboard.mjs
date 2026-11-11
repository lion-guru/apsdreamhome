import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    await cdp.navigate('https://play.google.com/console/u/0/developers/6737128363617329736/app/4972265606945357144/app-dashboard');
    await cdp.wait(5000);

    const dashboard = await cdp.eval(`(() => {
        const sections = Array.from(document.querySelectorAll('.dashboard-card, mat-card, .task-list, [role="region"], section')).map(s => {
            const heading = s.querySelector('h1, h2, h3, h4, [role="heading"]')?.innerText?.trim() || '';
            const items = Array.from(s.querySelectorAll('li, .task-item, [role="listitem"]')).map(li => li.innerText.replace(/\\n+/g, ' ').trim());
            return {
                heading,
                items: items.slice(0, 10),
                fullText: s.innerText.replace(/\\n+/g, ' ').substring(0, 300)
            };
        });

        return {
            headings: Array.from(document.querySelectorAll('h1, h2, h3, h4')).map(h => h.innerText.trim()),
            sections: sections.filter(s => s.heading || s.fullText.length > 30),
            bodySnippet: document.body.innerText.substring(0, 1500)
        };
    })()`);

    console.log('Dashboard Data:', JSON.stringify(dashboard, null, 2));
    await cdp.screenshot('main_app_dashboard.png');

    cdp.close();
}

main().catch(console.error);
