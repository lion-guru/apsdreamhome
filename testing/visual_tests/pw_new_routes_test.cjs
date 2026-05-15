const { chromium } = require('playwright');
const BASE = 'http://localhost/apsdreamhome';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    
    const routes = [
        { path: '/admin/ceo-dashboard', label: 'CEO Dashboard' },
        { path: '/admin/cfo-dashboard', label: 'CFO Dashboard' },
        { path: '/admin/builder-dashboard', label: 'Builder Dashboard' },
        { path: '/admin/loyalty', label: 'Loyalty Program' },
        { path: '/admin/scheduler', label: 'Scheduler' },
        { path: '/admin/files', label: 'File Manager' },
        { path: '/admin/commission-manage', label: 'Commission Mgmt' },
        { path: '/admin/land', label: 'Land Management' },
        { path: '/admin/emi', label: 'EMI Calculator' },
        { path: '/admin/careers', label: 'Careers' },
        { path: '/admin/scheduler/logs', label: 'Scheduler Logs' },
        { path: '/admin/scheduler/health', label: 'Scheduler Health' },
        { path: '/admin/land/stats', label: 'Land Stats' },
    ];
    
    let passed = 0, failed = 0, expected302 = 0;
    for (const r of routes) {
        try {
            const resp = await page.goto(BASE + r.path, { waitUntil: 'domcontentloaded', timeout: 15000 });
            const code = resp.status();
            if (code === 200) {
                console.log('PASS: ' + code + ' ' + r.path + '  [' + r.label + ']');
                passed++;
            } else if (code === 302) {
                console.log('AUTH: ' + code + ' ' + r.path + '  (redirect to login)');
                expected302++;
            } else {
                console.log('FAIL: ' + code + ' ' + r.path + '  [' + r.label + ']');
                failed++;
            }
        } catch (e) {
            console.log('ERR:  ' + r.path + '  ' + e.message.substring(0, 80));
            failed++;
        }
    }
    
    console.log('\n=== Results ===');
    console.log('Passed: ' + passed + ' | Auth (302): ' + expected302 + ' | Failed: ' + failed);
    
    await context.close();
    await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
