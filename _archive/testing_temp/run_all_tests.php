<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}
header('Content-Type: text/plain; charset=UTF-8');
set_time_limit(120);

$testFiles = [
    'test_2fa_backup_codes.php',
    'test_ab_expand.php',
    'test_ab_testing.php',
    'test_api_docs.php',
    'test_audit_log.php',
    'test_autocomplete_query.php',
    'test_campaign_service.php',
    'test_communication_gateway.php',
    'test_email_templates.php',
    'test_email_templates_integration.php',
    'test_envelope_log.php',
    'test_exception_hook.php',
    'test_field_collections.php',
    'test_gmail_smtp.php',
    'test_hotpath_cache.php',
    'test_hybrid_commission_engine.php',
    'test_image_gallery.php',
    'test_maintenance_mode.php',
    'test_mobile_api.php',
    'test_notification_preferences.php',
    'test_payment_reconciliation.php',
    'test_pdf_service.php',
    'test_property_import.php',
    'test_property_listing_wizard.php',
    'test_push_sender.php',
    'test_razorpay_service.php',
    'test_rbac_e2e.php',
    'test_rbac_sidebar.php',
    'test_registration_wizard.php',
    'test_s3_cors.php',
    'test_s3_storage.php',
    'test_saved_searches.php',
    'test_saved_searches_http.php',
    'test_settings.php',
    'test_smtp_debug.php',
    'test_smtp_variants.php',
    'test_telecaller_and_side_volume.php',
    'test_translations.php',
    'test_twilio_service.php',
    'test_voice_integration.php',
    'test_websocket.php',
    'test_websocket_broadcast.php',
    'test_websocket_e2e.php',
    'test_websocket_full.php',
    'test_websocket_integration.php'
];

$chunk = isset($_GET['chunk']) ? (int)$_GET['chunk'] : 0;
$chunkSize = 5;

if ($chunk > 0) {
    $start = ($chunk - 1) * $chunkSize;
    $testFiles = array_slice($testFiles, $start, $chunkSize);
    echo "=== RUNNING TESTS CHUNK $chunk (Indices $start to " . ($start + count($testFiles) - 1) . ") ===\n\n";
} else {
    echo "=== RUNNING ALL TESTS ===\n\n";
}

$baseUrl = 'http://localhost/apsdreamhome/testing/';

$results = [];
foreach ($testFiles as $file) {
    $url = $baseUrl . $file . '?v=' . time();
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $status = 'PASS';
    $details = '';

    if ($httpCode !== 200) {
        $status = 'FAIL';
        $details = "HTTP Status: $httpCode";
    } else {
        // Look for PHP fatals/warnings
        if (preg_match('/(Fatal error|Parse error|Warning|Notice|Exception):/i', $output, $m)) {
            $status = 'FAIL';
            $details = trim(strip_tags($m[0] . ' found'));
        }
        
        // Check for specific fail indicators
        if ($status === 'PASS') {
            $lines = explode("\n", $output);
            $fails = [];
            foreach ($lines as $line) {
                if (preg_match('/^\s*\[FAIL\]/i', $line) || preg_match('/^\s*FAIL\s/i', $line)) {
                    $fails[] = trim(strip_tags($line));
                }
            }
            if (count($fails) > 0) {
                $status = 'FAIL';
                $details = implode('; ', array_slice($fails, 0, 3));
            }
        }
        
        // Check summary counts
        if ($status === 'PASS') {
            // Check for FAIL: X where X > 0
            if (preg_match('/FAIL:\s*([1-9]\d*)/i', $output, $m)) {
                $status = 'FAIL';
                $details = "Summary shows $m[0]";
            }
            // Check for X failed where X > 0
            elseif (preg_match('/([1-9]\d*)[ \t]+failed/i', $output, $m)) {
                $status = 'FAIL';
                $details = "Summary shows $m[0]";
            }
            // Check for FAILED: X where X > 0
            elseif (preg_match('/FAILED:\s*([1-9]\d*)/i', $output, $m)) {
                $status = 'FAIL';
                $details = "Summary shows $m[0]";
            }
        }
    }

    $results[$file] = ['status' => $status, 'details' => $details];
    echo sprintf("%-45s | %-5s | %s\n", $file, $status, $details);
}
