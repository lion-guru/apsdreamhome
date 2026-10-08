<?php
/**
 * Cron: process monthly salary-grant payouts for all active grants.
 * Standalone: php scripts/cron_salary_grants.php [YYYY-MM]
 * Idempotent: already-paid (grant, month) pairs are skipped, so re-runs
 * within the same month never double-pay.
 */
require_once __DIR__ . '/../config/bootstrap.php';

$month = $argv[1] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    fwrite(STDERR, "Usage: php scripts/cron_salary_grants.php [YYYY-MM]\n");
    exit(1);
}

$svc = new \App\Services\MLM\SalaryIncentiveService();
$res = $svc->processMonthlyGrants($month);
echo "month=$month processed=" . ($res['processed'] ?? 0) . " amount=" . ($res['amount'] ?? 0);
if (!empty($res['errors'])) echo " skipped=" . count($res['errors']);
echo PHP_EOL;
foreach (($res['errors'] ?? []) as $e) echo "  - $e\n";
exit(!empty($res['success']) ? 0 : 1);