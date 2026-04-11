<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;

/**
 * ExportController - Associate Export Functions
 * Handles CSV exports for associate data
 */
class ExportController extends BaseController
{
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
            "SELECT created_at, payout_amount as amount, status FROM payouts WHERE associate_id=? ORDER BY created_at DESC",
            [$associate_id]
        );
        foreach ($payouts as $row) {
            fputcsv($out, [$row['created_at'], 'Payout', $row['amount'], ucfirst($row['status'])]);
        }

        // Export plot sales (from property table)
        $property = $this->db->fetchAll(
            "SELECT created_at, amount, status FROM property WHERE associate_id=? ORDER BY created_at DESC",
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

        $total = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM associates WHERE parent_id = ?", [$associate_id])['cnt'] ?? 0);
        $active = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM associates WHERE parent_id = ? AND status = 'active'", [$associate_id])['cnt'] ?? 0);
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

        $payouts = $this->db->fetchAll(
            "SELECT payout_amount, payout_percent, period, status, generated_on FROM payouts WHERE associate_id=? ORDER BY generated_on DESC",
            [$associate_id]
        );

        foreach ($payouts as $row) {
            fputcsv($out, [
                $row['generated_on'],
                $row['payout_amount'],
                $row['payout_percent'] . '%',
                $row['period'],
                ucfirst($row['status'])
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
        fputcsv($out, ['Level', 'Name', 'Post', 'Business Volume', 'Join Date', 'Phone']);

        $this->output = $out;
        $this->exportDownlineCSV($associate_id, 1);

        fclose($out);
        exit;
    }

    /**
     * Recursive function to export downline
     */
    private function exportDownlineCSV($parent_id, $level)
    {
        $associates = $this->db->fetchAll(
            "SELECT id, name, post, business_volume, join_date, phone FROM associates WHERE parent_id = ? ORDER BY join_date DESC",
            [$parent_id]
        );

        foreach ($associates as $row) {
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

        $from = $this->request()->get('from', date('Y-m-01'));
        $to = $this->request()->get('to', date('Y-m-d'));

        $associates = $this->db->fetchAll(
            "SELECT name, join_date, status FROM associates WHERE parent_id = ? AND join_date >= ? AND join_date <= ? ORDER BY join_date DESC",
            [$associate_id, $from, $to]
        );

        foreach ($associates as $row) {
            fputcsv($out, [$row['name'], $row['join_date'], ucfirst($row['status'])]);
        }

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

        $from = $this->request()->get('from', date('Y-m-01'));
        $to = $this->request()->get('to', date('Y-m-d'));

        $sales = $this->db->fetchAll(
            "SELECT id, amount, created_at, status FROM property WHERE associate_id = ? AND created_at >= ? AND created_at <= ? ORDER BY created_at DESC",
            [$associate_id, $from, $to]
        );

        foreach ($sales as $row) {
            fputcsv($out, [$row['id'], $row['amount'], $row['created_at'], ucfirst($row['status'])]);
        }

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

        $from = $this->request()->get('from', date('Y-m-01'));
        $to = $this->request()->get('to', date('Y-m-d'));

        $registry = $this->db->fetchAll(
            "SELECT id, plot_id, created_at, status FROM registry WHERE associate_id = ? AND created_at >= ? AND created_at <= ? ORDER BY created_at DESC",
            [$associate_id, $from, $to]
        );

        foreach ($registry as $row) {
            fputcsv($out, [$row['id'], $row['plot_id'], $row['created_at'], ucfirst($row['status'])]);
        }

        fclose($out);
        exit;
    }
}
