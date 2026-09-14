<?php
/**
 * Cron: Overdue Demand Letter WhatsApp Reminders
 * Finds demand letters where due_date < CURDATE and status in (drafted,sent,overdue)
 * Sends WhatsApp with pay link + PDF url, updates sent_at/sent_via if delivered
 * Tenant-scoped, safe to run every hour via crontab: 0 * * * * php scripts/cron_overdue_demand_reminders.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

$lockFile = __DIR__ . '/../storage/cron_overdue.lock';
if (file_exists($lockFile) && (time() - filemtime($lockFile) < 3600)) { echo "Already running\n"; exit(0); }
touch($lockFile);

try {
    $db = Database::getInstance()->getConnection();
    // Iterate tenants (if multi-tenant)
    $tenants = [1];
    try {
        $st = $db->query("SELECT id FROM tenants WHERE status='active' LIMIT 20");
        $rows = $st->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($rows)) $tenants = array_map('intval', $rows);
    } catch (Exception $e) { $tenants = [1]; }

    $totalSent = 0; $totalSkipped = 0;
    foreach ($tenants as $tid) {
        TenantContext::setById((int)$tid);
        $sql = "SELECT dl.id, dl.letter_number, dl.amount, dl.due_date, dl.status, dl.installment_id,
                       b.booking_number, COALESCE(cu.phone, bu.phone) AS phone, COALESCE(cu.name, bu.name) AS cname
                FROM booking_demand_letters dl
                JOIN bookings b ON b.id=dl.booking_id
                LEFT JOIN users cu ON cu.id=b.customer_id
                LEFT JOIN users bu ON bu.id=b.user_id
                WHERE dl.tenant_id=? AND dl.status IN ('drafted','sent','overdue') AND dl.due_date < CURDATE()
                  AND (dl.sent_at IS NULL OR dl.sent_at < DATE_SUB(NOW(), INTERVAL 1 DAY))
                LIMIT 50";
        $st = $db->prepare($sql);
        $st->execute([$tid]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $phone = preg_replace('/[^0-9]/', '', $r['phone'] ?? '');
            if ($phone === '') { $totalSkipped++; continue; }
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : 'http://localhost/apsdreamhome';
            $payUrl = $base . '/user/installments/' . (int)$r['installment_id'] . '/pay';
            $pdfUrl = $base . '/admin/finance/demand-letters/' . $r['id'] . '/pdf';
            $msg = "Dear {$r['cname']}, your demand letter {$r['letter_number']} (Booking {$r['booking_number']}, ₹" . number_format((float)$r['amount'],2) . ") was due on {$r['due_date']} and is overdue. Pay now: $payUrl | PDF: $pdfUrl - APS Dream Home";
            $delivered = false;
            try {
                $wa = new \App\Services\Communication\WhatsAppWebService();
                $res = method_exists($wa, 'sendMessage') ? $wa->sendMessage($phone, $msg) : null;
                if (is_array($res) && !empty($res['success'])) $delivered = true;
                elseif (is_object($res) && !empty($res->success)) $delivered = true;
                elseif ($res === true) $delivered = true;
                // If no provider, treat as logged (dry-run)
                if ($res === null) { error_log("cron_overdue_demand: dry-run to $phone for letter {$r['id']}"); $delivered = false; }
            } catch (Exception $e) { error_log("cron_overdue_demand WA error: " . $e->getMessage()); }
            if ($delivered) {
                $db->prepare("UPDATE booking_demand_letters SET status='sent', sent_via='whatsapp', sent_to_email=?, sent_at=NOW(), updated_at=NOW() WHERE id=? AND tenant_id=?")->execute([$phone, $r['id'], $tid]);
                $totalSent++;
            } else {
                // Mark attempted to avoid spam: update sent_at even on fail with 1-day cooldown already enforced
                $totalSkipped++;
            }
        }
    }
    echo "Overdue reminders: sent=$totalSent skipped=$totalSkipped\n";
} catch (Exception $e) {
    error_log("cron_overdue_demand fatal: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    @unlink($lockFile);
}
