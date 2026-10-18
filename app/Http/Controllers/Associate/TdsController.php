<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;

/**
 * Associate self-service TDS slips & annual deduction certificate.
 * Data mirrors the admin 194H report (mlm_payouts + TdsConfigService),
 * scoped to the logged-in associate.
 */
class TdsController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    private function fyRange(string $fy): array
    {
        if (!preg_match('/^(\d{4})-(\d{4})$/', $fy, $m) || ((int)$m[2] !== (int)$m[1] + 1)) {
            $y = (int)date('Y');
            if ((int)date('m') < 4) {
                $y--;
            }
            $fy = $y . '-' . ($y + 1);
            $m = [$fy, (string)$y, (string)($y + 1)];
        }
        return [$fy, sprintf('%04d-04-01', (int)$m[1]), sprintf('%04d-03-31', (int)$m[2])];
    }

    private function fyOptions(): array
    {
        $y = (int)date('Y');
        if ((int)date('m') < 4) {
            $y--;
        }
        $opts = [];
        for ($i = 0; $i < 4; $i++) {
            $a = $y - $i;
            $opts[] = $a . '-' . ($a + 1);
        }
        return $opts;
    }

    private function myTdsData(int $userId, string $fy): array
    {
        $db = \App\Core\Database\Database::getInstance();
        [$fyLabel, $start, $end] = $this->fyRange($fy);
        $tid = \App\Core\Middleware\TenantContext::getId();
        $tSql = $tid > 1 ? " AND tenant_id = {$tid}" : '';

        $me = $db->fetchOne(
            "SELECT u.id, u.name, u.email, u.phone, u.referral_code, e.pan_number
             FROM users u LEFT JOIN employees e ON e.user_id = u.id WHERE u.id = ? LIMIT 1",
            [$userId]
        ) ?: [];

        $payouts = [];
        $gross = 0.0; $tds = 0.0; $net = 0.0;
        try {
            $payouts = $db->fetchAll(
                "SELECT id, gross_amount, tds_amount, other_deductions, net_amount, payment_mode,
                        transaction_ref, cheque_number, status, paid_date, created_at
                 FROM mlm_payouts
                 WHERE associate_user_id = ? AND DATE(COALESCE(paid_date, created_at)) BETWEEN ? AND ?{$tSql}
                 ORDER BY COALESCE(paid_date, created_at) DESC",
                [$userId, $start, $end]
            ) ?: [];
            foreach ($payouts as $p) {
                $gross += (float)($p['gross_amount'] ?? 0);
                $tds += (float)($p['tds_amount'] ?? 0);
                $net += (float)($p['net_amount'] ?? 0);
            }
        } catch (\Throwable $e) { error_log('TdsController slips: ' . $e->getMessage()); }

        // Cross-check rate via canonical calculator (informational)
        $rate = 5.0;
        try {
            $svc = new \App\Services\MLM\TdsConfigService();
            $calc = $svc->calculateForCommission($gross, !empty($me['pan_number']) ? $me['pan_number'] : null);
            $rate = (float)($calc['rate_used'] ?? 5.0);
        } catch (\Throwable $e) { /* keep default */ }

        return [
            'fy' => $fyLabel, 'start' => $start, 'end' => $end,
            'me' => $me, 'payouts' => $payouts,
            'gross' => $gross, 'tds' => $tds, 'net' => $net, 'rate' => $rate,
        ];
    }

    /**
     * GET /associate/tds-slips?fy=YYYY-YYYY
     */
    public function slips()
    {
        $this->requireAuth();
        $data = $this->myTdsData((int)$_SESSION['user_id'], (string)($_GET['fy'] ?? ''));
        $this->render('associate/tds_slips', array_merge([
            'page_title' => 'My TDS Slips',
            'page_description' => 'Commission, TDS deducted & payouts per financial year',
            'fy_options' => $this->fyOptions(),
        ], $data), 'layouts/associate');
    }

    /**
     * Printable annual TDS deduction certificate (Form 16A style).
     * GET /associate/tds-slips/certificate?fy=YYYY-YYYY
     */
    public function certificate()
    {
        $this->requireAuth();
        $data = $this->myTdsData((int)$_SESSION['user_id'], (string)($_GET['fy'] ?? ''));
        $tan = '—';
        try {
            $db = \App\Core\Database\Database::getInstance();
            $row = $db->fetchOne("SELECT value FROM service_configs WHERE `key` IN ('company_tan','tan','deductor_tan') LIMIT 1");
            if (!empty($row['value'])) {
                $tan = (string)$row['value'];
            }
        } catch (\Throwable $e) { /* fallback dash */ }
        $this->render('associate/tds_certificate', array_merge([
            'page_title' => 'TDS Deduction Certificate',
            'tan' => $tan,
        ], $data));
    }
}
