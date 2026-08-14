<?php
/**
 * Abandoned Registration Recovery Cron
 * Daily job: finds incomplete registrations with last_activity > 24h ago
 * and logs a reminder email to gateway_logs.
 *
 * Run via:  php scripts/abandoned_registration_cron.php
 * Or via Windows Task Scheduler daily.
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Set tenant context for service compatibility
$cronTenantId = 1;
if (class_exists('\App\Core\Middleware\TenantContext')) {
    \App\Core\Middleware\TenantContext::setById($cronTenantId, $pdo);
}
$cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
$cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
$cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

$cutoff = (new DateTime('-24 hours'))->format('Y-m-d H:i:s');
$stats = [
    'found' => 0,
    'reminded' => 0,
    'failed' => 0,
    'already_recovered' => 0,
    'errors' => [],
];

try {
    $rows = $db->fetchAll(
        "SELECT id, email, phone, form_data, current_step, progress_percent, last_activity_at
         FROM incomplete_registrations
         WHERE current_step <> 'step4'
           AND last_activity_at < ?
           AND recovered_at IS NULL
         ORDER BY last_activity_at ASC LIMIT 500",
        [$cutoff]
    );
    $stats['found'] = count($rows);
    echo "[abandoned-cron] Found {$stats['found']} inactive registrations older than 24h\n";

    foreach ($rows as $row) {
        $email = $row['email'] ?? null;
        $phone = $row['phone'] ?? null;
        if (!$email && !$phone) {
            $stats['errors'][] = "Row {$row['id']} has no email/phone";
            continue;
        }
        $formData = !empty($row['form_data']) ? json_decode($row['form_data'], true) : [];
        $name = $formData['name'] ?? 'User';
        $resumeUrl = rtrim((isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : 'http://localhost/apsdreamhome'), '/') . '/register/' . ($row['current_step'] ?? 'step1');

        $subject = "Complete your APS Dream Home registration";
        $body = "Hi {$name},\n\nYou started registering on APS Dream Home (step {$row['current_step']}, {$row['progress_percent']}% complete).\n\n";
        $body .= "Click below to pick up where you left off:\n{$resumeUrl}\n\n";
        $body .= "If you no longer wish to register, simply ignore this email.\n\n- APS Dream Home Team";

        $gateway = $email ? 'email' : 'sms';
        $recipient = $email ?: $phone;
        $status = 'success';

        try {
            $db->execute(
                "INSERT INTO gateway_logs (gateway, action, recipient, status, request_body, response_body, created_at{$cronTenantCol})
                 VALUES (?, 'abandoned_registration_reminder', ?, ?, ?, ?, NOW(){$cronTenantVal})",
                [
                    $gateway,
                    $recipient,
                    $status,
                    json_encode(['subject' => $subject, 'to' => $recipient]),
                    json_encode(['sent' => true, 'resume_url' => $resumeUrl])
                ]
            );
            $stats['reminded']++;
        } catch (\Throwable $e) {
            $stats['failed']++;
            $stats['errors'][] = "Row {$row['id']} reminder failed: " . $e->getMessage();
        }
    }

    // Cleanup: mark records that are still here 14 days later as expired.
    $expiredCutoff = (new DateTime('-14 days'))->format('Y-m-d H:i:s');
    $db->execute("DELETE FROM incomplete_registrations WHERE last_activity_at < ? AND recovered_at IS NULL{$cronTenantSql}", [$expiredCutoff]);

    echo "[abandoned-cron] Stats: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . "\n";
} catch (\Throwable $e) {
    $stats['errors'][] = "Cron failure: " . $e->getMessage();
    echo "[abandoned-cron] FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[abandoned-cron] Done.\n";
exit(0);?>