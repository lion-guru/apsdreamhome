// Full E2E with separate agent-reply script
import { chromium } from 'playwright';
import { writeFileSync, readFileSync, unlinkSync } from 'fs';
import { execSync } from 'child_process';

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

    await page.goto('http://localhost/apsdreamhome/', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => localStorage.removeItem('lcw_session'));
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1500);

    await page.click('#lcw-launcher');
    await page.waitForSelector('#lcw-prechat:not([hidden])');

    await page.fill('#lcw-name', 'Final Test');
    await page.fill('#lcw-email', 'final@test.com');
    await page.fill('#lcw-first-message', 'Quick test question.');
    await Promise.all([
        page.waitForResponse((r) => r.url().includes('/api/chat/start')),
        page.click('#lcw-start-btn')
    ]);
    await page.waitForSelector('#lcw-thread:not([hidden])');
    await page.waitForTimeout(500);

    const sess = await page.evaluate(() => JSON.parse(localStorage.getItem('lcw_session')));
    console.log('Session created:', sess);

    // Write a temp PHP script to insert the agent reply
    const tmpScript = 'C:\\Users\\abhay\\AppData\\Local\\Temp\\opencode\\insert_agent.php';
    const scriptBody = `<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_id, sender_name, message, message_type, is_internal_note, read_by_visitor, read_by_agent) VALUES (?, 'agent', 1, 'Priya (Sales)', ?, 'text', 0, 0, 1)");
$stmt->execute([${sess.id}, 'Hi! Yes, Suryoday Heights has 12 plots left. Rs 15-22 Lakh range.']);
$id = $pdo->lastInsertId();
$pdo->prepare("UPDATE chat_sessions SET message_count = message_count + 1, unread_visitor_count = unread_visitor_count + 1, last_message_at = NOW(), last_message_by = 'agent' WHERE id = ?")->execute([${sess.id}]);
echo $id;
`;
    writeFileSync(tmpScript, scriptBody);
    const result = execSync(`php "${tmpScript}"`, { encoding: 'utf-8' }).trim();
    unlinkSync(tmpScript);
    console.log('Inserted agent msg id:', result);

    // Wait for poll to pick it up (poll runs every 4s)
    console.log('Waiting for visitor to see agent reply...');
    let received = false;
    for (let i = 0; i < 15; i++) {
        await page.waitForTimeout(500);
        const count = await page.evaluate(() => document.querySelectorAll('.lcw-msg-agent').length);
        if (count > 0) {
            received = true;
            console.log(`SUCCESS: agent reply visible after ~${(i+1)*500}ms`);
            break;
        }
    }

    if (received) {
        const text = await page.evaluate(() => {
            const b = document.querySelectorAll('.lcw-msg-agent .lcw-msg-bubble');
            return b.length ? b[b.length-1].textContent : null;
        });
        console.log('Last agent message:', text);
        await page.screenshot({ path: 'testing/screenshots/livechat-05-agent-reply.png' });
    } else {
        console.log('FAIL: agent reply not received');
        await page.screenshot({ path: 'testing/screenshots/livechat-05-no-reply.png' });
    }

    await browser.close();
    process.exit(received ? 0 : 1);
})().catch((e) => { console.error('FATAL:', e); process.exit(1); });
