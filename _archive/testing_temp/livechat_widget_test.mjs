// Live Chat Widget Smoke Test
import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

    const errors = [];
    page.on('pageerror', (err) => errors.push('PAGE: ' + err.message));
    page.on('console', (msg) => {
        if (msg.type() === 'error') errors.push('CONSOLE: ' + msg.text());
    });

    // 1. Load homepage
    console.log('1. Loading homepage...');
    const resp = await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForTimeout(1500); // allow deferred scripts to load
    console.log('   HTTP:', resp.status());

    // 2. Verify widget markup
    console.log('2. Checking widget DOM...');
    await page.waitForSelector('#lcw-root', { timeout: 5000 });
    const launcher = await page.$('#lcw-launcher');
    const win = await page.$('#lcw-window');
    console.log('   Launcher present:', !!launcher);
    console.log('   Window present:', !!win);

    // 3. Screenshot 1: floating button (closed state)
    await page.screenshot({ path: 'testing/screenshots/livechat-01-launcher.png' });
    console.log('   Screenshot 1 saved: livechat-01-launcher.png');

    // 4. Click to open
    console.log('3. Clicking launcher...');
    await page.click('#lcw-launcher');
    await page.waitForSelector('#lcw-prechat:not([hidden])', { timeout: 3000 });
    await page.waitForTimeout(400);
    await page.screenshot({ path: 'testing/screenshots/livechat-02-prechat.png' });
    console.log('   Screenshot 2 saved: livechat-02-prechat.png');

    // 5. Fill pre-chat form
    console.log('4. Filling pre-chat form...');
    await page.fill('#lcw-name', 'Smoke Test Visitor');
    await page.fill('#lcw-email', 'smoke@widget.test');
    await page.fill('#lcw-phone', '+91 98765 43210');
    await page.fill('#lcw-first-message', 'Hi, I am interested in plots in Suryoday Colony.');

    // 6. Submit form
    console.log('5. Submitting form...');
    await Promise.all([
        page.waitForResponse((r) => r.url().includes('/api/chat/start'), { timeout: 10000 }),
        page.click('#lcw-start-btn')
    ]);
    await page.waitForSelector('#lcw-thread:not([hidden])', { timeout: 5000 });
    await page.waitForTimeout(800);
    await page.screenshot({ path: 'testing/screenshots/livechat-03-thread.png' });
    console.log('   Screenshot 3 saved: livechat-03-thread.png');

    // 7. Send a follow-up message
    console.log('6. Sending follow-up message...');
    await page.fill('#lcw-input', 'Yes, I am interested. Please share more details.');
    await page.click('#lcw-send');
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'testing/screenshots/livechat-04-after-send.png' });
    console.log('   Screenshot 4 saved: livechat-04-after-send.png');

    // 8. Verify localStorage has session token
    const session = await page.evaluate(() => localStorage.getItem('lcw_session'));
    console.log('7. localStorage session:', session ? 'present' : 'MISSING');

    // 9. Summary
    console.log('\n=== SUMMARY ===');
    console.log('Errors:', errors.length);
    errors.forEach((e) => console.log('  -', e));
    console.log('All screenshots in: testing/screenshots/livechat-*.png');

    await browser.close();
    process.exit(errors.length > 0 ? 1 : 0);
})().catch((e) => {
    console.error('FATAL:', e);
    process.exit(1);
});
