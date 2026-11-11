import fs from 'node:fs';

const TARGET_URL = 'http://127.0.0.1:9222/json';

export class CDPClient {
    constructor() {
        this.ws = null;
        this.id = 1;
        this.pending = new Map();
        this.pageUrl = null;
        this.appId = '4972265606945357144';
        this.devId = '6737128363617329736';
    }

    async init() {
        const tabsRes = await fetch(TARGET_URL);
        const tabs = await tabsRes.json();
        // Prefer Play Console tab, else find any top-level page tab
        let tab = tabs.find(t => t.url && t.url.includes('play.google.com/console'));
        if (!tab) {
            tab = tabs.find(t => t.type === 'page' && !t.url.startsWith('chrome://'));
        }
        if (!tab && tabs.length > 0) {
            tab = tabs[0];
        }
        if (!tab) throw new Error('No browser tab found');

        this.pageUrl = tab.url;
        const devMatch = (tab.url || '').match(/developers\/(\d+)/);
        if (devMatch) this.devId = devMatch[1];
        const appMatch = (tab.url || '').match(/app\/(\d+)/);
        if (appMatch) this.appId = appMatch[1];

        this.ws = new WebSocket(tab.webSocketDebuggerUrl);
        await new Promise((resolve, reject) => {
            this.ws.onopen = resolve;
            this.ws.onerror = reject;
        });

        this.ws.onmessage = (evt) => {
            const data = JSON.parse(evt.data);
            if (data.id && this.pending.has(data.id)) {
                const { resolve, reject } = this.pending.get(data.id);
                this.pending.delete(data.id);
                if (data.error) reject(data.error);
                else resolve(data.result);
            }
        };

        await this.send('Page.enable');
        await this.send('DOM.enable');
    }

    send(method, params = {}) {
        return new Promise((resolve, reject) => {
            const curId = this.id++;
            this.pending.set(curId, { resolve, reject });
            this.ws.send(JSON.stringify({ id: curId, method, params }));
        });
    }

    async eval(expr) {
        const res = await this.send('Runtime.evaluate', {
            expression: expr,
            returnByValue: true
        });
        if (res?.exceptionDetails) {
            console.error('CDP Eval Exception:', res.exceptionDetails.exception?.description || res.exceptionDetails.text);
        }
        return res?.result?.value;
    }

    async clickByText(tag, textPart) {
        return await this.eval(`(() => {
            const els = Array.from(document.querySelectorAll('${tag}'));
            const target = els.find(el => (el.innerText || '').includes('${textPart}'));
            if (target) {
                target.scrollIntoView({ block: 'center' });
                target.click();
                return { success: true, text: target.innerText.trim() };
            }
            return { success: false, notFound: '${textPart}' };
        })()`);
    }

    async screenshot(filename) {
        const res = await this.send('Page.captureScreenshot', { format: 'png' });
        const filePath = `C:\\Users\\abhay\\.gemini\\antigravity-ide\\brain\\77d56415-63e2-4e2e-8588-47cd8302a454\\${filename}`;
        fs.writeFileSync(filePath, Buffer.from(res.data, 'base64'));
        console.log(`Saved screenshot: ${filename}`);
        return filePath;
    }

    async wait(ms) {
        return new Promise(r => setTimeout(r, ms));
    }

    async navigate(url) {
        await this.send('Page.navigate', { url });
        await this.wait(3000);
    }

    close() {
        if (this.ws) this.ws.close();
    }
}
