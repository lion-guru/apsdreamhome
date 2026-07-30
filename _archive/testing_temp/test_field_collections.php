<?php
/**
 * E2E test for Field Collections module
 * Run: php testing/test_field_collections.php
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $passed = 0;
    $failed = 0;

    // 1. Check table exists
    $exists = $pdo->query("SHOW TABLES LIKE 'field_collections'")->fetch();
    echo ($exists ? "✓" : "✗") . " Table field_collections exists\n";
    if ($exists) $passed++; else $failed++;

    // 2. Check table columns
    $cols = $pdo->query("SHOW COLUMNS FROM field_collections")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['id','user_id','user_role','collection_date','customer_name','customer_phone','plot_booking_id','amount','payment_mode','cheque_number','receipt_generated','status','created_at'];
    $allPresent = count(array_intersect($required, $cols)) === count($required);
    echo ($allPresent ? "✓" : "✗") . " Table has all required columns\n";
    if ($allPresent) $passed++; else $failed++;

    // 3. Check data exists
    $count = $pdo->query("SELECT COUNT(*) as c FROM field_collections")->fetch()['c'];
    echo ($count > 0 ? "✓" : "✗") . " Has $count rows of data\n";
    if ($count > 0) $passed++; else $failed++;

    // 4. Check associate collections route
    $ch = curl_init('http://localhost/apsdreamhome/associate/collections');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo ($httpCode === 302 ? "✓" : "✗") . " GET /associate/collections returns $httpCode (302=unauth redirect)\n";
    if ($httpCode === 302) $passed++; else $failed++;

    // 5. Check agent collections route
    $ch = curl_init('http://localhost/apsdreamhome/agent/collections');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo ($httpCode === 302 ? "✓" : "✗") . " GET /agent/collections returns $httpCode (302=unauth redirect)\n";
    if ($httpCode === 302) $passed++; else $failed++;

    // 6. Check views exist
    $viewFiles = [
        $root . '/app/views/associate/collections/index.php',
        $root . '/app/views/associate/collections/create.php',
        $root . '/app/views/associate/collections/show.php',
        $root . '/app/views/agent/collections/index.php',
        $root . '/app/views/agent/collections/create.php',
        $root . '/app/views/agent/collections/show.php',
    ];
    $allViews = true;
    foreach ($viewFiles as $vf) {
        if (!file_exists($vf)) { echo "✗ Missing view: $vf\n"; $allViews = false; $failed++; }
    }
    if ($allViews) { echo "✓ All 6 view files exist\n"; $passed++; }

    // 7. Verify PHP syntax on all view files
    $allSyntax = true;
    foreach ($viewFiles as $vf) {
        $out = shell_exec("php -l \"$vf\" 2>&1");
        if (!str_contains($out, 'No syntax errors')) {
            echo "✗ Syntax error in $vf: $out\n";
            $allSyntax = false;
            $failed++;
        }
    }
    if ($allSyntax) { echo "✓ All 6 view files pass PHP syntax check\n"; $passed++; }

    // 8. Check payment_mode ENUM values
    $enumVals = $pdo->query("SHOW COLUMNS FROM field_collections WHERE Field='payment_mode'")->fetch()['Type'] ?? '';
    $hasCash = str_contains($enumVals, "'cash'");
    $hasCheque = str_contains($enumVals, "'cheque'");
    $hasOnline = str_contains($enumVals, "'online'");
    echo ($hasCash && $hasCheque && $hasOnline ? "✓" : "✗") . " payment_mode ENUM has cash/cheque/online\n";
    if ($hasCash && $hasCheque && $hasOnline) $passed++; else $failed++;

    // 9. Check status ENUM values
    $statusEnum = $pdo->query("SHOW COLUMNS FROM field_collections WHERE Field='status'")->fetch()['Type'] ?? '';
    $hasPending = str_contains($statusEnum, "'pending'");
    $hasVerified = str_contains($statusEnum, "'verified'");
    $hasRejected = str_contains($statusEnum, "'rejected'");
    echo ($hasPending && $hasVerified && $hasRejected ? "✓" : "✗") . " status ENUM has pending/verified/rejected\n";
    if ($hasPending && $hasVerified && $hasRejected) $passed++; else $failed++;

    // 10. Verify routes in web.php
    $webPhp = file_get_contents($root . '/routes/web.php');
    $assocRoutesOk = preg_match('/associate\/collections/', $webPhp) === 1;
    $agentRoutesOk = preg_match('/agent\/collections/', $webPhp) === 1;
    $createRoutesOk = preg_match('/create/', $webPhp) === 1 && preg_match('/store/', $webPhp) === 1;
    echo ($assocRoutesOk && $agentRoutesOk ? "✓" : "✗") . " Routes exist for /associate/collections and /agent/collections\n";
    if ($assocRoutesOk && $agentRoutesOk) $passed++; else $failed++;

    echo "\n─── Results ───\n";
    echo "✓ Passed: $passed\n";
    echo ($failed > 0 ? "✗" : "✓") . " Failed: $failed\n";
    echo ($failed === 0 ? "✓ ALL TESTS PASSED" : "✗ SOME TESTS FAILED") . "\n";
    exit($failed > 0 ? 1 : 0);
} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
