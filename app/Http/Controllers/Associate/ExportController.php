<?php

namespace App\Http\Controllers\Associate;

use App\Core\Database\Database;
use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

/**
 * ExportController - Associate Export Functions
 * Handles CSV exports for associate data
 */
class ExportController extends BaseController
{
    use TenantAwareTrait;

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Get current associate ID from session
     * @return int
     */
    protected function getCurrentAssociateId()
    {
        @session_start();
        return $_SESSION['associate_id'] ?? 0;
    }

    /**
     * Send CSV headers for download
     * @param string $filename
     */
    protected function sendCsvHeaders($filename)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Export my earnings (payouts + plot sales)
     */
    public function myEarnings()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "my_earnings_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($out, ['Date', 'Type', 'Amount', 'Status']);

        // Export payouts
        $payouts = $this->db->fetchAll(
            "SELECT created_at, amount, status FROM payouts WHERE associate_id=? ORDER BY created_at DESC",
            [$associate_id]
        );
        foreach ($payouts as $row) {
            fputcsv($out, [$row['created_at'], 'Payout', $row['amount'], ucfirst($row['status'])]);
        }

        // Export plot sales (from user_properties table)
        $property = $this->db->fetchAll(
            "SELECT created_at, price as amount, status FROM user_properties WHERE user_id=? ORDER BY created_at DESC",
            [$associate_id]
        );
        foreach ($property as $row) {
            fputcsv($out, [$row['created_at'], 'Plot Sale', $row['amount'], ucfirst($row['status'])]);
        }

        fclose($out);
        exit;
    }

    /**
     * Export active team percentage to CSV
     */
    public function activeTeam()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "active_team_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Total Directs', 'Active Directs', 'Active Percentage']);

        $total = 0; $active = 0;
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $total = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM users WHERE referred_by = ? AND role = 'associate'{$tidSql}", array_merge([$associate_id], $tidParams))['cnt'] ?? 0);
            $active = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM users WHERE referred_by = ? AND role = 'associate' AND status = 'active'{$tidSql}", array_merge([$associate_id], $tidParams))['cnt'] ?? 0);
        } catch (\Exception $e) { error_log('ExportController exception: ' . $e->getMessage()); }
        $active_pct = ($total > 0) ? round(($active / $total) * 100, 1) : 0;

        fputcsv($out, [$total, $active, $active_pct . '%']);

        fclose($out);
        exit;
    }

    /**
     * Export my payouts
     */
    public function myPayouts()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "my_payouts_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Date', 'Amount', 'Percent', 'Period', 'Status']);

        $payouts = [];
        try {
            $payouts = $this->db->fetchAll(
                "SELECT amount as payout_amount, payout_percent, period, status, generated_on FROM payouts WHERE associate_id=? ORDER BY generated_on DESC",
                [$associate_id]
            );
        } catch (\Exception $e) { error_log('ExportController exception: ' . $e->getMessage()); }

        foreach ($payouts as $row) {
            fputcsv($out, [
                $row['generated_on'],
                $row['payout_amount'] ?? $row['amount'] ?? '0',
                ($row['payout_percent'] ?? '0') . '%',
                $row['period'] ?? '',
                ucfirst($row['status'] ?? 'pending')
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Export downline to CSV (recursive)
     */
    public function downline()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "downline_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Level', 'Name', 'Phone', 'Join Date', 'Status']);

        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $members = $this->db->fetchAll(
                "SELECT name, phone, created_at, status FROM users WHERE referred_by = ? AND role = 'associate'{$tidSql} ORDER BY created_at DESC",
                array_merge([$associate_id], $tidParams)
            );
            foreach ($members as $i => $row) {
                fputcsv($out, [$i+1, $row['name'], $row['phone'], $row['created_at'], $row['status'] ?? 'active']);
            }
        } catch (\Exception $e) { error_log('ExportController exception: ' . $e->getMessage()); }

        fclose($out);
        exit;
    }

    /**
     * Recursive function to export downline
     */
    private function exportDownlineCSV($parent_id, $level)
    {
        [$tidSql, $tidParams] = $this->tenantWhere();
        $users = $this->db->fetchAll(
            "SELECT id, name, post, business_volume, join_date, phone FROM users WHERE parent_id = ?{$tidSql} ORDER BY join_date DESC",
            array_merge([$parent_id], $tidParams)
        );

        foreach ($users as $row) {
            fputcsv($this->output, [
                $level,
                $row['name'],
                $row['post'],
                $row['business_volume'],
                $row['join_date'],
                $row['phone']
            ]);
            $this->exportDownlineCSV($row['id'], $level + 1);
        }
    }

    /**
     * Export new directs
     */
    public function newDirects()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "new_directs_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Name', 'Join Date', 'Status']);

        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $users = $this->db->fetchAll(
                "SELECT name, created_at as join_date, status FROM users WHERE parent_id = ? AND created_at >= ? AND created_at <= ?{$tidSql} ORDER BY created_at DESC",
                array_merge([$associate_id, $from, $to], $tidParams)
            );
            foreach ($users as $row) {
                fputcsv($out, [$row['name'], $row['join_date'], ucfirst($row['status'] ?? 'active')]);
            }
        } catch (\Exception $e) { error_log('ExportController exception: ' . $e->getMessage()); }

        fclose($out);
        exit;
    }

    /**
     * Export plot sales
     */
    public function plotSales()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "plot_sales_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Plot ID', 'Amount', 'Date', 'Status']);

        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        try {
            $sales = $this->db->fetchAll(
                "SELECT id, price as amount, created_at, status FROM user_properties WHERE associate_id = ? AND created_at >= ? AND created_at <= ? ORDER BY created_at DESC",
                [$associate_id, $from, $to]
            );
            foreach ($sales as $row) {
                fputcsv($out, [$row['id'], $row['amount'], $row['created_at'], ucfirst($row['status'] ?? 'pending')]);
            }
        } catch (\Exception $e) { error_log('ExportController exception: ' . $e->getMessage()); }

        fclose($out);
        exit;
    }

    /**
     * Export registry
     */
    public function registry()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "registry_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Registry ID', 'Plot ID', 'Date', 'Status']);

        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        try {
            $registry = $this->db->fetchAll(
                "SELECT r.id, r.plot_id, r.created_at, r.status FROM registries r WHERE r.associate_id = ? AND r.created_at >= ? AND r.created_at <= ? ORDER BY r.created_at DESC",
                [$associate_id, $from, $to]
            );
            foreach ($registry as $row) {
                fputcsv($out, [$row['id'], $row['plot_id'], $row['created_at'], ucfirst($row['status'] ?? 'pending')]);
            }
        } catch (\Exception $e) { error_log('ExportController exception: ' . $e->getMessage()); }

        fclose($out);
        exit;
    }
}
