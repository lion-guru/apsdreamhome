import fs from 'node:fs';

const TARGET_URL = 'http://127.0.0.1:9222/json';

async function run() {
    const tabsRes = await fetch(TARGET_URL);
    const tabs = await tabsRes.json();
    const tab = tabs.find(t => t.url && t.url.includes('play.google.com/console'));
    if (!tab) {
        console.error('Play console tab not found');
        process.exit(1);
    }
    const ws = new WebSocket(tab.webSocketDebuggerUrl);
    await new Promise(r => ws.onopen = r);

    let id = 1;
    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const curId = id++;
        const handler = (evt) => {
            const data = JSON.parse(evt.data);
            if (data.id === curId) {
                ws.removeEventListener('message', handler);
                if (data.error) reject(data.error);
                else resolve(data.result);
            }
        };
        ws.addEventListener('message', handler);
        ws.send(JSON.stringify({ id: curId, method, params }));
    });

    const res = await send('Runtime.evaluate', {
        expression: `(() => {
            const cards = [];
            // In Google Play Console App Content overview:
            // Look for headings and their action buttons
            const headings = Array.from(document.querySelectorAll('h3, h2, [role="heading"]'));
            for (const h of headings) {
                const title = h.innerText.trim();
                // Find nearest button or anchor that says 'Start declaration' or 'Manage'
                let el = h;
                let btn = null;
                for (let i = 0; i < 6 && el; i++) {
                    el = el.parentElement;
                    if (el) {
                        btn = el.querySelector('button, a[role="button"], a');
                        if (btn && (btn.innerText.includes('declaration') || btn.innerText.includes('Manage') || btn.innerText.includes('Start'))) {
                            break;
                        }
                    }
                }
                if (title && title.length > 2 && title.length < 50) {
                    cards.push({
                        title,
                        btnText: btn ? btn.innerText.trim() : null
                    });
                }
            }
            return cards;
        })()`,
        returnByValue: true
    });

    console.log('Cards detected:\n', JSON.stringify(res.result ? res.result.value : res, null, 2));
    ws.close();
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
