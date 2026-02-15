<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use Exception;

class ExportController extends BaseController
{
    private $output;

    public function __construct()
    {
        parent::__construct();
        
        // Register middlewares
        $this->middleware('role:associate');
        // Export routes are typically GET but we want to secure them with CSRF if possible,
        // however GET routes shouldn't use CSRF in standard way. 
        // If they are accessed via POST, we add CSRF.
        $this->middleware('csrf', ['only' => ['earnings', 'activeTeam', 'myPayouts']]);
    }

    /**
     * Export earnings history to CSV
     */
    public function earnings()
    {
        $associate_id = $this->getCurrentAssociateId();
        
        $filename = "earnings_history_" . date('Ymd') . ".csv";
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
            fputcsv($out, [$row['generated_on'], $row['payout_amount'], $row['payout_percent'], $row['period'], $row['status']]);
        }
        
        fclose($out);
        exit;
    }

    /**
     * Export my team (downline)
     */
    public function myTeam()
    {
        $associate_id = $this->getCurrentAssociateId();
        $filename = "my_team_" . date('Ymd') . ".csv";
        $this->sendCsvHeaders($filename);
        
        $this->output = fopen('php://output', 'w');
        fputs($this->output, "\xEF\xBB\xBF");
        fputcsv($this->output, ['Name', 'Post', 'Business Volume', 'Join Date', 'Phone', 'Level']);

        $this->exportDownlineCSV($associate_id);
        
        fclose($this->output);
        exit;
    }

    /**
     * Recursive helper for team export
     */
    private function exportDownlineCSV($parent_id, $level = 1)
    {
        $associates = $this->db->fetchAll(
            "SELECT id, name, post, business_volume, join_date, phone FROM associates WHERE parent_id=?",
            [$parent_id]
        );
        foreach ($associates as $row) {
            fputcsv($this->output, [
                $row['name'],
                $row['post'],
                $row['business_volume'],
                $row['join_date'],
                $row['phone'],
                $level
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
