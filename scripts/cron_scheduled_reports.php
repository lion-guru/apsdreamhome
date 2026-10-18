<?php

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Core\Cron\CronDb;
use App\Core\Middleware\TenantContext;
use App\Services\Reports\ReportService;
use App\Services\EmailService;

$db = CronDb::connection('scheduled_reports');
if ($db === null) {
    echo "DB unreachable\n";
    exit(0);
}

$stmt = $db->prepare("SELECT * FROM scheduled_reports WHERE status = 'active' AND next_run IS NOT NULL AND next_run <= NOW()");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    TenantContext::setById((int)$row['tenant_id']);
    $reportService = new ReportService();
    $emailService = new EmailService();

    $parameters = json_decode($row['parameters'] ?? '{}', true);
    if (!is_array($parameters)) {
        $parameters = [];
    }
    $startDate = $parameters['start_date'] ?? date('Y-m-01');
    $endDate = $parameters['end_date'] ?? date('Y-m-t');
    $status = $parameters['status'] ?? null;
    $format = $row['format'] ?? 'csv';
    if ($format === 'array') {
        $format = 'csv';
    }

    $report = null;
    switch ($row['report_type']) {
        case 'sales':
            $report = $reportService->generateSalesReport($startDate, $endDate, $format);
            break;
        case 'property':
            $report = $reportService->generatePropertyReport($status, $format);
            break;
        case 'associate':
            $report = $reportService->generateAssociateReport($startDate, $endDate, $format);
            break;
        case 'customer':
            $report = $reportService->generateCustomerReport($startDate, $endDate, $format);
            break;
        case 'financial':
            $report = $reportService->generateFinancialReport($startDate, $endDate, $format);
            break;
        default:
            $report = false;
            break;
    }

    if ($report === false || $report === null) {
        echo "Failed to generate report ID " . $row['id'] . "\n";
        continue;
    }

    if (!is_string($report)) {
        $report = json_encode($report);
        $format = 'json';
    }

    $extension = 'txt';
    if ($format === 'csv' || $format === 'excel') {
        $extension = 'csv';
    } elseif ($format === 'json') {
        $extension = 'json';
    } elseif ($format === 'pdf') {
        $extension = 'txt';
    }

    $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'scheduled_report_' . $row['id'] . '.' . $extension;
    file_put_contents($tempFile, $report);

    $recipients = json_decode($row['recipients'] ?? '[]', true);
    if (!is_array($recipients)) {
        $recipients = array_filter(array_map('trim', explode(',', (string)$row['recipients'])));
    }

    $subject = ($row['title'] ?? 'Scheduled Report') . ' - ' . date('Y-m-d');
    $body = "<p>Your scheduled report is attached.</p><p>Report: " . htmlspecialchars($row['title'] ?? '') . "</p><p>Generated: " . date('Y-m-d H:i:s') . "</p>";

    $sent = false;
    foreach ($recipients as $recipient) {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            continue;
        }
        $result = $emailService->sendWithAttachment($recipient, $subject, $body, $tempFile, basename($tempFile));
        if ($result) {
            $sent = true;
        }
    }

    $nextRun = $reportService->getNextRunDate($row['frequency'], $row['day_of_week'] ?: $row['day_of_month']);
    $db->prepare("UPDATE scheduled_reports SET last_run = NOW(), next_run = ?, updated_at = NOW() WHERE id = ?")
        ->execute([$nextRun, $row['id']]);

    if (is_file($tempFile)) {
        unlink($tempFile);
    }

    echo "Processed report ID " . $row['id'] . " sent=" . ($sent ? '1' : '0') . "\n";
}

echo "DONE\n";
