<?php
/**
 * MLM Daily Digest Script
 * -----------------------
 * Usage: php tools/mlm_digest.php
 *
 * Collects key MLM metrics and emails a summary to the admin team.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../app/services/NotificationService.php';
require_once __DIR__ . '/../app/services/CommissionService.php';
require_once __DIR__ . '/../app/services/PayoutService.php';
require_once __DIR__ . '/../app/services/MlmSettings.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$notificationService = new NotificationService();
$commissionService = new CommissionService();
$payoutService = new PayoutService();
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if (!$conn) {
    fwrite(STDERR, "Unable to connect to database.\n");
    exit(1);
}

$metrics = gatherMetrics($conn);
$body = buildEmailBody($metrics);

$sent = $notificationService->notifyAdmin(
    'MLM Daily Digest - ' . date('d M Y'),
    $body,
    'mlm_daily_digest',
    $metrics
);

echo $sent ? "Digest email sent successfully.\n" : "Failed to send digest email.\n";

/**
 * Gather dashboard metrics for the digest.
 */
function gatherMetrics(mysqli $conn): array
{
    $metrics = [
        'generated_at' => date('c'),
        'pending_commissions' => ['count' => 0, 'amount' => 0.0],
        'approved_commissions_24h' => ['count' => 0, 'amount' => 0.0],
        'batches' => ['draft' => 0, 'processing' => 0],
        'top_beneficiaries_7d' => [],
        'settings' => [
            'pending_threshold' => (float) MlmSettings::getFloat('pending_commission_threshold', 0),
        ],
    ];

    // Pending commissions
    $sql = "SELECT COUNT(*) AS cnt, IFNULL(SUM(amount),0) AS total
            FROM mlm_commission_ledger WHERE status = 'pending'";
    $result = $conn->query($sql)->fetch_assoc();
    $metrics['pending_commissions'] = [
        'count' => (int) ($result['cnt'] ?? 0),
        'amount' => (float) ($result['total'] ?? 0),
    ];

    // Approved commissions in last 24 hours
    $sql = "SELECT COUNT(*) AS cnt, IFNULL(SUM(amount),0) AS total
            FROM mlm_commission_ledger
            WHERE status = 'approved'
              AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
    $result = $conn->query($sql)->fetch_assoc();
    $metrics['approved_commissions_24h'] = [
        'count' => (int) ($result['cnt'] ?? 0),
        'amount' => (float) ($result['total'] ?? 0),
    ];

    // Batches awaiting action
    $sql = "SELECT status, COUNT(*) AS cnt
            FROM mlm_payout_batches
            WHERE status IN ('draft','processing')
            GROUP BY status";
    $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    foreach ($result as $row) {
        $metrics['batches'][$row['status']] = (int) $row['cnt'];
    }

    // Top beneficiaries in last 7 days
    $sql = "SELECT u.name, u.email, SUM(l.amount) AS total_amount
            FROM mlm_commission_ledger l
            JOIN users u ON l.beneficiary_user_id = u.id
            WHERE l.status IN ('approved','paid')
              AND l.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY l.beneficiary_user_id, u.name, u.email
            ORDER BY total_amount DESC
            LIMIT 5";
    $metrics['top_beneficiaries_7d'] = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    return $metrics;
}

/**
 * Build the HTML email content.
 */
function buildEmailBody(array $metrics): string
{
    $pending = $metrics['pending_commissions'];
    $approved24h = $metrics['approved_commissions_24h'];
    $threshold = $metrics['settings']['pending_threshold'];

    $body = '<h2>MLM Daily Digest</h2>';
    $body .= '<p>Generated at: ' . htmlspecialchars($metrics['generated_at']) . '</p>';

    $body .= '<h3>Commission Overview</h3>';
    $body .= '<ul>';
    $body .= '<li><strong>Pending commissions:</strong> ' . $pending['count'] . ' records / ₹' . number_format($pending['amount'], 2) . '</li>';
    $body .= '<li><strong>Approved in last 24h:</strong> ' . $approved24h['count'] . ' records / ₹' . number_format($approved24h['amount'], 2) . '</li>';
    if ($threshold > 0) {
        $status = $pending['amount'] >= $threshold ? '⚠️ exceeds threshold' : '✅ under threshold';
        $body .= '<li><strong>Threshold (' . number_format($threshold, 2) . '):</strong> ' . $status . '</li>';
    }
    $body .= '</ul>';

    $body .= '<h3>Payout Batches</h3>';
    $body .= '<ul>';
    $body .= '<li><strong>Draft:</strong> ' . ($metrics['batches']['draft'] ?? 0) . '</li>';
    $body .= '<li><strong>Processing:</strong> ' . ($metrics['batches']['processing'] ?? 0) . '</li>';
    $body .= '</ul>';

    $body .= '<h3>Top Beneficiaries (Last 7 days)</h3>';
    if (!empty($metrics['top_beneficiaries_7d'])) {
        $body .= '<ol>';
        foreach ($metrics['top_beneficiaries_7d'] as $beneficiary) {
            $body .= '<li>' . htmlspecialchars($beneficiary['name']) . ' (' . htmlspecialchars($beneficiary['email']) . ') - ₹' . number_format($beneficiary['total_amount'], 2) . '</li>';
        }
        $body .= '</ol>';
    } else {
        $body .= '<p>No beneficiary data in the last 7 days.</p>';
    }

    $body .= '<p>Access detailed analytics at <a href="' . BASE_URL . 'admin/mlm/analytics">Admin Analytics</a> or review payout batches at <a href="' . BASE_URL . 'admin/payouts">Admin Payouts</a>.</p>';

    return $body;
}
