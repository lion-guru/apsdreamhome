<?php

namespace App\Http\Controllers\Admin;

class LoanController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $activeLoans = 0;
            $completedLoans = 0;
            $defaultedLoans = 0;
            $totalDisbursed = 0;
            $totalEmiAmount = 0;
            $overdueCount = 0;
            $overdueAmount = 0;
            $penaltyAmount = 0;
            $emiPlans = [];

            try {
                $activeLoans = (int)($this->db->query("SELECT COUNT(*) FROM emi_plans WHERE status = 'active'")->fetchColumn());
                $completedLoans = (int)($this->db->query("SELECT COUNT(*) FROM emi_plans WHERE status = 'completed'")->fetchColumn());
                $defaultedLoans = (int)($this->db->query("SELECT COUNT(*) FROM emi_plans WHERE status = 'defaulted'")->fetchColumn());
                $totalDisbursed = (float)($this->db->query("SELECT COALESCE(SUM(total_amount),0) FROM emi_plans")->fetchColumn());
                $totalEmiAmount = (float)($this->db->query("SELECT COALESCE(SUM(emi_amount),0) FROM emi_plans WHERE status = 'active'")->fetchColumn());
                $overdueCount = (int)($this->db->query("SELECT COUNT(*) FROM booking_payment_schedules WHERE status = 'overdue'")->fetchColumn());
                $overdueAmount = (float)($this->db->query("SELECT COALESCE(SUM(amount - paid_amount),0) FROM booking_payment_schedules WHERE status = 'overdue'")->fetchColumn());
                $penaltyAmount = (float)($this->db->query("SELECT COALESCE(SUM(accrued_penalty),0) FROM booking_payment_schedules WHERE accrued_penalty > 0")->fetchColumn());
                $emiPlans = $this->db->query("SELECT ep.*, u.name as customer_name, p.plot_no, c.name as colony_name FROM emi_plans ep LEFT JOIN users u ON ep.customer_id = u.id LEFT JOIN plots p ON ep.property_id = p.id LEFT JOIN colonies c ON p.colony_id = c.id ORDER BY ep.created_at DESC LIMIT 15")->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log('LoanController::index() query error: ' . $e->getMessage());
            }

            $this->data['page_title'] = 'Loan Management - APS Dream Home';
            $this->data['activeLoans'] = $activeLoans;
            $this->data['completedLoans'] = $completedLoans;
            $this->data['defaultedLoans'] = $defaultedLoans;
            $this->data['totalDisbursed'] = $totalDisbursed;
            $this->data['totalEmiAmount'] = $totalEmiAmount;
            $this->data['overdueCount'] = $overdueCount;
            $this->data['overdueAmount'] = $overdueAmount;
            $this->data['penaltyAmount'] = $penaltyAmount;
            $this->data['emiPlans'] = $emiPlans;

            $this->render('admin/loans/index', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load loans');
            $this->redirect('/admin/dashboard');
        }
    }
}
