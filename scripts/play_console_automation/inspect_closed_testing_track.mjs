import { CDPClient } from './cdp_helper.mjs';

async function main() {
    const cdp = new CDPClient();
    await cdp.init();

    // Navigate to dashboard
    await cdp.navigate(`https://play.google.com/console/u/0/developers/${cdp.devId}/app/${cdp.appId}/app-dashboard`);
    await cdp.wait(5000);

    // Let's inspect the Closed testing card and links/buttons on Dashboard
    const closedTestInfo = await cdp.eval(`(() => {
        const links = Array.from(document.querySelectorAll('a, button')).map(el => {
            const r = el.getBoundingClientRect();
            return {
                tag: el.tagName,
                text: el.innerText.trim(),
                href: el.getAttribute('href'),
                visible: r.width > 0 && r.height > 0
            };
        }).filter(el => el.visible && (el.text.includes('Closed testing') || el.text.includes('Publish a closed testing') || el.text.includes('tasks') || (el.href && el.href.includes('track'))));

        return { links };
    })()`);
    console.log('Closed testing links/buttons:', JSON.stringify(closedTestInfo, null, 2));

    // Also let's check the left sidebar navigation items
    const navItems = await cdp.eval(`(() => {
        const items = Array.from(document.querySelectorAll('nav a, material-list-item a, [role="treeitem"] a, a')).map(a => {
            return {
                text: a.innerText.trim().replace(/\\n/g, ' '),
                href: a.getAttribute('href')
            };
        }).filter(a => a.href && (a.href.includes('track') || a.href.includes('test')));
        return items;
    })()`);
    console.log('Navigation test tracks:', JSON.stringify(navItems, null, 2));

    cdp.close();
}

main().catch(console.error);
