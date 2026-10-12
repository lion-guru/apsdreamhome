<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\MaintenanceBillingService;
use App\Services\EMIAutomationService;

class CustomerMaintenanceController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    protected MaintenanceBillingService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new MaintenanceBillingService($this->db);
    }

    private function requireCustomer(): void
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    /**
     * GET /customer/maintenance-dues
     */
    public function index(): void
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];

        $dues = $this->service->getDuesForCustomer($userId);

        // Calculate stats
        $totalPending = 0.00;
        $totalPaid = 0.00;
        foreach ($dues as &$d) {
            if (in_array($d['status'], ['pending', 'partially_paid'])) {
                $rem = max(0.00, (float)$d['amount_due'] - (float)$d['paid_amount']);
                $totalPending += $rem;
                $d['pending_balance'] = $rem;
                // Generate UPI intent link
                $d['upi_pay_link'] = EMIAutomationService::emiPayLink(
                    $rem,
                    (string)($d['bill_month'] ?? 'MNT'),
                    'Plot-' . (string)($d['plot_number'] ?? $d['plot_id'])
                );
            } else {
                $totalPaid += (float)$d['paid_amount'];
                $d['pending_balance'] = 0.00;
                $d['upi_pay_link'] = '';
            }
        }
        unset($d);

        $this->render('customer/maintenance_dues', [
            'page_title' => 'My Society Maintenance Dues',
            'dues' => $dues,
            'total_pending' => $totalPending,
            'total_paid' => $totalPaid
        ]);
    }
}
