import fs from 'node:fs';

const TARGET_URL = 'http://127.0.0.1:9222/json';

async function main() {
    const tabsRes = await fetch(TARGET_URL);
    const tabs = await tabsRes.json();
    const playTab = tabs.find(t => t.url && t.url.includes('play.google.com/console'));
    
    if (!playTab) {
        console.error('Play Console tab not found!');
        process.exit(1);
    }
    
    const ws = new WebSocket(playTab.webSocketDebuggerUrl);
    let msgId = 1;
    const pending = new Map();
    
    function send(method, params = {}) {
        return new Promise((resolve, reject) => {
            const id = msgId++;
            pending.set(id, { resolve, reject, method });
            ws.send(JSON.stringify({ id, method, params }));
        });
    }
    
    ws.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        if (msg.id && pending.has(msg.id)) {
            const { resolve, reject, method } = pending.get(msg.id);
            pending.delete(msg.id);
            if (msg.error) reject(msg.error);
            else resolve(msg.result);
        }
    };
    
    await new Promise((resolve, reject) => {
        ws.onopen = resolve;
        ws.onerror = reject;
    });
    
    console.log('Navigating to App Content Overview...');
    await send('Page.enable');
    
    const match = playTab.url.match(/app\/(\d+)/);
    const appId = match ? match[1] : '4972265606945357144';
    const devId = playTab.url.match(/developers\/(\d+)/)?.[1] || '6737128363617329736';
    const appContentUrl = `https://play.google.com/console/u/0/developers/${devId}/app/${appId}/app-content/overview`;
    
    await send('Page.navigate', { url: appContentUrl });
    console.log('Waiting 5 seconds...');
    await new Promise(r => setTimeout(r, 5000));
    
    const contentOverview = await send('Runtime.evaluate', {
        expression: `(() => {
            // Find all cards / sections in app content
            const sections = Array.from(document.querySelectorAll('[role="heading"], h2, h3, .section, mat-card')).map(s => s.innerText.trim());
            const actionRequiredLinks = Array.from(document.querySelectorAll('a, button')).map(a => ({
                text: a.innerText.trim(),
                href: a.href || ''
            })).filter(a => a.text.includes('Start') || a.text.includes('Manage') || a.text.includes('Action required') || a.text.includes('Complete') || a.text.includes('declaration'));
            
            return {
                title: document.title,
                url: window.location.href,
                bodySnippet: document.body.innerText.substring(0, 2000),
                actionLinks: actionRequiredLinks.slice(0, 30)
            };
        })()`,
        returnByValue: true
    });
    
    console.log('App Content Overview:\n', JSON.stringify(contentOverview.result.value, null, 2));
    
    // Screenshot
    const screenshot = await send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('C:\\Users\\abhay\\.gemini\\antigravity-ide\\brain\\77d56415-63e2-4e2e-8588-47cd8302a454\\app_content_overview.png', Buffer.from(screenshot.data, 'base64'));
    console.log('Saved screenshot: app_content_overview.png');
    
    ws.close();
}

main().catch(err => {
    console.error('FATAL:', err);
    process.exit(1);
});
