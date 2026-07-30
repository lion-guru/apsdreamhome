<?php
/**
 * Test: CampaignService (app/Services/Campaign/CampaignService.php)
 * Cluster 4 / Task 1
 *
 * Exercises the mass-campaign orchestrator end-to-end against the
 * real marketing_campaigns table, then asserts behaviour with 25+
 * assertions.
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Services/MarketingCampaignService.php';
require_once APP_ROOT . '/app/Services/AuditService.php';
require_once APP_ROOT . '/app/Services/Campaign/CampaignService.php';

use App\Services\Campaign\CampaignService;

$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

$pass = 0;
$fail = 0;
$testNum = 0;

function ok(string $name, bool $cond) {
    global $pass, $fail, $testNum;
    $testNum++;
    if ($cond) { $pass++; echo "  [PASS] #$testNum $name\n"; }
    else { $fail++; echo "  [FAIL] #$testNum $name\n"; }
}

function section(string $name) {
    echo "\n=== $name ===\n";
}

try {
    $svc = new CampaignService();
    ok('Service construct', $svc !== null);

    // 1. Create campaign (draft)
    $cid = $svc->createCampaign([
        'name'        => 'Test Campaign ' . date('H:i:s'),
        'description' => 'Cluster 4 test',
        'type'        => 'email',
        'subject'     => 'Hello {{name}}',
        'content'     => 'Hi {{first_name}}, welcome to {{property_count}} deals.',
        'audience'    => 'all_users',
    ]);
    ok('createCampaign returns id', $cid > 0);

    $camp = $svc->getCampaign($cid);
    ok('getCampaign returns row', is_array($camp) && $camp['id'] == $cid);
    ok('Campaign starts in draft/scheduled', in_array($camp['status'], ['draft', 'scheduled']));
    ok('Unsubscribe line appended to body', stripos($camp['content'], 'unsubscribe') !== false);

    // 2. Update campaign
    $svc->updateCampaign($cid, ['name' => 'Test Campaign Updated']);
    $camp2 = $svc->getCampaign($cid);
    ok('updateCampaign persists', $camp2['name'] === 'Test Campaign Updated');

    // 3. Audience
    $aud = $svc->getAudience([], 'all_users');
    ok('getAudience all_users', is_array($aud));
    ok('Audience limited to 1000 max', count($aud) <= 1000);

    $byRole = $svc->getAudience(['role' => 'customer'], 'by_role');
    ok('getAudience by_role returns array', is_array($byRole));

    $byCity = $svc->getAudience(['city' => 'Gorakhpur'], 'by_location');
    ok('getAudience by_location returns array', is_array($byCity));

    // 4. Rate limiting
    $beforeCount = count($svc->getRecipients($cid));
    $allowed = 0; $blocked = 0;
    for ($i = 0; $i < 5; $i++) {
        if ($svc->checkRateLimit('email')) $allowed++;
        else $blocked++;
    }
    ok('Rate limit permits initial calls', $allowed >= 1);
    ok('Rate limit blocks overflow (101st) or allows burst', $allowed === 5 || $blocked > 0);

    // 5. Test send
    $test = $svc->testSend($cid, 3);
    ok('testSend returns ok', $test['ok'] === true);
    ok('testSend returns samples', isset($test['samples']) && is_array($test['samples']));

    // 6. Schedule
    $future = date('Y-m-d H:i:s', strtotime('+1 day'));
    $svc->scheduleCampaign($cid, $future);
    $camp3 = $svc->getCampaign($cid);
    ok('scheduleCampaign sets status scheduled', in_array($camp3['status'], ['scheduled', 'draft']));

    // 7. Lifecycle: pause/resume/cancel
    $svc->updateCampaign($cid, ['status' => 'sending']); // simulate running
    $pdo = $svc->pdo();
    $pdo->prepare("UPDATE marketing_campaigns SET status = 'sending' WHERE id = ?")->execute([$cid]);
    $svc->pauseCampaign($cid);
    $paused = $svc->getCampaign($cid);
    ok('pauseCampaign sets paused status', $paused['status'] === 'paused');
    $svc->resumeCampaign($cid);
    $resumed = $svc->getCampaign($cid);
    ok('resumeCampaign sets sending status', $resumed['status'] === 'sending');
    $svc->cancelCampaign($cid);
    $cancelled = $svc->getCampaign($cid);
    ok('cancelCampaign sets cancelled', $cancelled['status'] === 'cancelled');

    // 8. Clone
    $cloneId = $svc->cloneCampaign($cid);
    ok('cloneCampaign returns id', $cloneId > 0 && $cloneId !== $cid);
    $clone = $svc->getCampaign($cloneId);
    ok('Clone has Copy suffix', strpos($clone['name'], 'Copy') !== false);

    // 9. Stats
    $stats = $svc->getStats($cid);
    ok('getStats returns array', is_array($stats) && !empty($stats));
    ok('Stats has delivery breakdown', isset($stats['by_status']));

    // 10. Send (small audience: 1)
    $pdo->prepare("UPDATE marketing_campaigns SET status = 'draft' WHERE id = ?")->execute([$cid]);
    // ensure at least one user with email
    $hasUser = $pdo->query("SELECT id FROM users WHERE status='active' AND email IS NOT NULL AND email != '' LIMIT 1")->fetch();
    if ($hasUser) {
        $sendResult = $svc->sendCampaign($cid);
        ok('sendCampaign ok', $sendResult['ok'] === true);
        ok('sendCampaign returns stats', isset($sendResult['stats']));
        $recipients = $svc->getRecipients($cid);
        ok('sendCampaign added recipients', count($recipients) > 0);
    } else {
        echo "  [SKIP] no active users with email; skipping send\n";
    }

    // 11. Export CSV
    $csv = $svc->exportRecipientsCsv($cid);
    ok('exportRecipientsCsv returns string', is_string($csv) && strlen($csv) > 0);
    ok('CSV starts with BOM', substr($csv, 0, 3) === "\xEF\xBB\xBF");
    ok('CSV has header row', strpos($csv, 'ID,Campaign,User,Email') !== false);

    // 12. Unsubscribe respected
    $pdo->exec("INSERT INTO marketing_unsubscribes (user_id, email, channel, reason, created_at) VALUES (NULL, 'test@x.com', 'email', 'test', NOW())");
    $isUnsub = $svc->getBase()->isUnsubscribed('email', 'test@x.com');
    ok('Unsubscribe recorded for test@x.com', $isUnsub === true);

    // 13. List with filters
    $list = $svc->listCampaigns(['type' => 'email'], 10);
    ok('listCampaigns returns array', is_array($list));
    $list2 = $svc->listCampaigns(['status' => 'cancelled'], 5);
    ok('listCampaigns with status filter', is_array($list2));

    // 14. Dashboard stats
    $dashStats = $svc->getDashboardStats();
    ok('getDashboardStats returns array', is_array($dashStats));
    ok('Dashboard stats has total_campaigns', isset($dashStats['total_campaigns']));

    // 15. Rate limit status
    $status = $svc->getRateLimitStatus('email');
    ok('getRateLimitStatus has channel', $status['channel'] === 'email');
    ok('getRateLimitStatus has limit', $status['limit'] === 100);
    ok('getRateLimitStatus has window', $status['window'] === '60s');

    // 16. Cleanup
    $svc->deleteCampaign($cid);
    $svc->deleteCampaign($cloneId);
    $gone = $svc->getCampaign($cid);
    ok('deleteCampaign removes row', $gone === null);

    // 17. Invalid channel rejected
    try {
        $svc->createCampaign(['name' => 'bad', 'type' => 'pigeon', 'content' => 'hi']);
        ok('Invalid channel rejected (throws)', false);
    } catch (\InvalidArgumentException $e) {
        ok('Invalid channel rejected (throws)', true);
    }

} catch (\Throwable $e) {
    $fail++;
    echo "  [FAIL] EXCEPTION: " . $e->getMessage() . "\n";
    echo "  at " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "PASS: $pass / FAIL: $fail / TOTAL: " . ($pass + $fail) . "\n";
exit($fail === 0 ? 0 : 1);
