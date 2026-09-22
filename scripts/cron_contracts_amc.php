<?php
/**
 * Cron: Contracts/AMC Expiry Check & Reminder
 * Runs daily to update statuses and send renewal reminders.
 * Usage: php scripts/cron_contracts_amc.php [--dry-run] [--status]
 */
require __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Services\ServiceConfigService;

$dryRun = in_array('--dry-run', $argv);
$showStatus = in_array('--status', $argv);

$db = Database::getInstance()->getConnection();
$tenantIds = [1]; // Single-tenant mode for now; extend for multi-tenant

if ($showStatus) {
    echo "=== Contracts/AMC Status ===\n";
    foreach ($tenantIds as $tid) {
        TenantContext::setById($tid);
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $tParams = $tid > 1 ? [$tid] : [];

        $counts = $db->fetchAll("
            SELECT status, COUNT(*) as c, SUM(amount) as value
            FROM contracts_amc
            WHERE 1=1{$tSql}
            GROUP BY status
        ", $tParams);
        echo "Tenant $tid:\n";
        foreach ($counts as $r) {
            echo "  {$r['status']}: {$r['c']} contracts, value " . number_format((float)$r['value'], 2) . "\n";
        }
        // Expiring soon
        $soon = $db->fetchAll("
            SELECT id, contract_number, party_name, end_date, DATEDIFF(end_date, CURDATE()) as days_left
            FROM contracts_amc
            WHERE status IN ('active','expiring_soon') AND end_date >= CURDATE() AND end_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
            {$tSql}
            ORDER BY end_date ASC
        ", $tParams);
        echo "  Expiring within 60 days: " . count($soon) . "\n";
        foreach ($soon as $s) {
            echo "    #{$s['id']} {$s['contract_number']} - {$s['party_name']} ends {$s['end_date']} ({$s['days_left']} days)\n";
        }
    }
    exit(0);
}

$results = [
    'status_updates' => 0,
    'reminders_sent' => 0,
    'errors' => [],
];

foreach ($tenantIds as $tid) {
    TenantContext::setById($tid);
    $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
    $tParams = $tid > 1 ? [$tid] : [];

    try {
        $db->beginTransaction();

        // 1. Update statuses: active -> expiring_soon -> expired
        $sql = "
            UPDATE contracts_amc
            SET status = CASE
                WHEN status = 'active' AND end_date >= CURDATE() AND end_date <= DATE_ADD(CURDATE(), INTERVAL renewal_notice_days DAY)
                THEN 'expiring_soon'
                WHEN status IN ('active','expiring_soon') AND end_date < CURDATE()
                THEN 'expired'
                ELSE status
            END,
            updated_at = NOW()
            WHERE status IN ('active','expiring_soon'){$tSql}
        ";
        if (!$dryRun) {
            $stmt = $db->prepare($sql);
            $stmt->execute($tParams);
            $results['status_updates'] += $stmt->rowCount();
        } else {
            $cnt = $db->fetchOne("SELECT COUNT(*) as c FROM contracts_amc WHERE status IN ('active','expiring_soon') AND (end_date < CURDATE() OR end_date <= DATE_ADD(CURDATE(), INTERVAL renewal_notice_days DAY)){$tSql}", $tParams);
            $results['status_updates'] += (int)$cnt['c'];
        }

        // 2. Send reminders for contracts expiring within notice window
        $due = $db->fetchAll("
            SELECT c.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email
            FROM contracts_amc c
            LEFT JOIN users u ON u.id = c.party_id AND c.party_type = 'customer'
            WHERE c.status IN ('active','expiring_soon')
              AND c.end_date >= CURDATE()
              AND c.end_date <= DATE_ADD(CURDATE(), INTERVAL c.renewal_notice_days DAY)
              AND (c.last_reminder_at IS NULL OR c.last_reminder_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY))
              {$tSql}
            ORDER BY c.end_date ASC
        ", $tParams);

        foreach ($due as $contract) {
            $daysLeft = (int)(new DateTime($contract['end_date']))->diff(new DateTime())->days;
            $sent = false;

            // WhatsApp reminder
            if ($contract['customer_phone']) {
                try {
                    $wa = new \App\Services\Communication\WhatsAppService();
                    $digits = preg_replace('/[^0-9]/', '', $contract['customer_phone']);
                    if (strlen($digits) === 10) $digits = '91' . $digits;
                    if (strlen($digits) >= 12) {
                        $msg = "Namaste {$contract['customer_name']}, APS Dream Home: ";
                        $msg .= "Your {$contract['type']} contract {$contract['contract_number']} ";
                        $msg .= "expires on {$contract['end_date']} ({$daysLeft} days left). ";
                        $msg .= "Amount: " . ($contract['currency'] === 'INR' ? '₹' : '$') . number_format((float)$contract['amount'], 2) . ". ";
                        $msg .= "Please renew to continue services. Contact: +91 92771 21112.";
                        $waUrl = 'https://wa.me/' . $digits . '?text=' . urlencode($msg);
                        $wa->sendMessage($digits, $msg);
                        $sent = true;
                    }
                } catch (\Throwable $e) {
                    error_log("AMC WhatsApp failed: " . $e->getMessage());
                }
            }

            // Email reminder (if configured)
            if ($contract['customer_email']) {
                try {
                    $email = new \App\Services\NotificationService($db);
                    $email->sendEmail([
                        'to' => $contract['customer_email'],
                        'subject' => "Contract Renewal Reminder: {$contract['contract_number']} expires in {$daysLeft} days",
                        'template' => 'contract_renewal_reminder',
                        'data' => [
                            'customer_name' => $contract['customer_name'] ?? 'Customer',
                            'contract_number' => $contract['contract_number'],
                            'contract_type' => ucfirst($contract['type']),
                            'end_date' => $contract['end_date'],
                            'days_left' => $daysLeft,
                            'amount' => ($contract['currency'] === 'INR' ? '₹' : '$') . number_format((float)$contract['amount'], 2),
                        ],
                    ]);
                    $sent = true;
                } catch (\Throwable $e) {
                    error_log("AMC Email failed: " . $e->getMessage());
                }
            }

            if ($sent && !$dryRun) {
                $db->execute("
                    UPDATE contracts_amc
                    SET reminder_count = COALESCE(reminder_count, 0) + 1, last_reminder_at = NOW()
                    WHERE id = ?{$tSql}
                ", array_merge([$contract['id']], $tParams));
                $results['reminders_sent']++;
            }
        }

        if (!$dryRun) {
            $db->commit();
        } else {
            $db->rollBack();
        }
    } catch (\Throwable $e) {
        if (!$dryRun && $db->inTransaction()) $db->rollBack();
        $results['errors'][] = "Tenant $tid: " . $e->getMessage();
        error_log("cron_contracts_amc error: " . $e->getMessage());
    }
}

echo "=== Contracts/AMC Cron " . ($dryRun ? '(DRY RUN)' : '') . " ===\n";
echo "Status updates: {$results['status_updates']}\n";
echo "Reminders sent: {$results['reminders_sent']}\n";
if (!empty($results['errors'])) {
    echo "Errors:\n";
    foreach ($results['errors'] as $err) echo "  - $err\n";
}
echo "DONE\n";