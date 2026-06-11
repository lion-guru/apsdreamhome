<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Admin\AdminController;

class FinancialReportController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $totalReceipts = 0;
            $totalBookings = 0;
            $gstOutput = 0;
            $gstInput = 0;
            $gstPayable = 0;
            $totalTds = 0;
            $depositedTds = 0;
            $pendingTds = 0;
            $bankAccounts = [];
            $totalBankBalance = 0;
            $escrowBalance = 0;
            $reconciliations = 0;
            $pendingRecon = 0;
            $monthlyData = [];
            $methodBreakdown = [];
            $recentPayments = [];

            try {
                $totalReceipts = (float)($this->db->query("SELECT COALESCE(SUM(amount),0) FROM payment_transactions WHERE payment_status = 'completed'")->fetchColumn());
                $totalBookings = (float)($this->db->query("SELECT COALESCE(SUM(booking_amount),0) FROM plot_bookings WHERE status != 'cancelled'")->fetchColumn());
                $gstOutput = (float)($this->db->query("SELECT COALESCE(SUM(total_tax),0) FROM gst_transactions WHERE transaction_type = 'output'")->fetchColumn());
                $gstInput = (float)($this->db->query("SELECT COALESCE(SUM(total_tax),0) FROM gst_transactions WHERE transaction_type = 'input'")->fetchColumn());
                $gstPayable = max(0, $gstOutput - $gstInput);
                $totalTds = (float)($this->db->query("SELECT COALESCE(SUM(total_tds),0) FROM tds_register")->fetchColumn());
                $depositedTds = (float)($this->db->query("SELECT COALESCE(SUM(total_tds),0) FROM tds_register WHERE status IN ('deposited','verified')")->fetchColumn());
                $pendingTds = $totalTds - $depositedTds;
                $bankAccounts = $this->db->query("SELECT * FROM bank_accounts_master WHERE active = 1")->fetchAll(PDO::FETCH_ASSOC);
                $totalBankBalance = array_sum(array_map(function($a) { return (float)$a['current_balance']; }, $bankAccounts));
                $escrowBalance = array_sum(array_map(function($a) { return $a['is_escrow'] ? (float)$a['current_balance'] : 0; }, $bankAccounts));
                $reconciliations = (int)($this->db->query("SELECT COUNT(*) FROM bank_reconciliation WHERE status = 'completed'")->fetchColumn());
                $pendingRecon = (int)($this->db->query("SELECT COUNT(*) FROM bank_reconciliation WHERE status != 'completed'")->fetchColumn());
                $monthlyData = $this->db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue, payment_method FROM payment_transactions WHERE payment_status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month, payment_method ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);
                $methodBreakdown = $this->db->query("SELECT payment_method, COUNT(*) as cnt, SUM(amount) as total FROM payment_transactions WHERE payment_status = 'completed' GROUP BY payment_method ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
                $recentPayments = $this->db->query("SELECT pt.*, u.name FROM payment_transactions pt LEFT JOIN users u ON pt.user_id = u.id ORDER BY pt.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log('FinancialReportController::index() query error: ' . $e->getMessage());
            }

            $netIncome = $totalReceipts;

            $this->data['page_title'] = 'Financial Reports - APS Dream Home';
            $this->data['totalReceipts'] = $totalReceipts;
            $this->data['totalBookings'] = $totalBookings;
            $this->data['gstOutput'] = $gstOutput;
            $this->data['gstInput'] = $gstInput;
            $this->data['gstPayable'] = $gstPayable;
            $this->data['totalTds'] = $totalTds;
            $this->data['depositedTds'] = $depositedTds;
            $this->data['pendingTds'] = $pendingTds;
            $this->data['bankAccounts'] = $bankAccounts;
            $this->data['totalBankBalance'] = $totalBankBalance;
            $this->data['escrowBalance'] = $escrowBalance;
            $this->data['reconciliations'] = $reconciliations;
            $this->data['pendingRecon'] = $pendingRecon;
            $this->data['monthlyData'] = $monthlyData;
            $this->data['methodBreakdown'] = $methodBreakdown;
            $this->data['recentPayments'] = $recentPayments;
            $this->data['netIncome'] = $netIncome;

            $this->render('admin/reports/financial', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load financial reports');
            $this->redirect('/admin/dashboard');
        }
    }
}
