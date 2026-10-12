<?php
namespace App\Http\Controllers\Admin;

use App\Services\PropertyTaxService;
use App\Http\Controllers\AdminController;
use App\Traits\TenantAwareTrait;

class PropertyTaxController extends AdminController
{
    use TenantAwareTrait;

    private PropertyTaxService $taxService;

    public function __construct()
    {
        parent::__construct();
        $this->taxService = new PropertyTaxService();
    }

    public function index()
    {
        $this->requireAdmin();

        try {
            $filters = [
                'colony_id' => $_GET['colony_id'] ?? null,
                'plot_id' => $_GET['plot_id'] ?? null,
                'assessment_year' => $_GET['assessment_year'] ?? null,
                'tax_type' => $_GET['tax_type'] ?? null,
                'status' => $_GET['status'] ?? null,
            ];

            error_log('PROPERTY-TAX-INDEX: calling getTaxMasterList');
            $masters = $this->taxService->getTaxMasterList($filters);
            error_log('PROPERTY-TAX-INDEX: got masters, count=' . count($masters));
            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$this->tenantId()]);
            $years = range(date('Y'), 2020);
            $taxTypes = ['property_tax', 'water_tax', 'sewerage_tax', 'fire_tax', 'education_cess', 'other'];

            $stats = [
                'total' => count($masters),
                'draft' => 0,
                'published' => 0,
                'demand_generated' => 0,
                'closed' => 0,
                'total_demand' => 0,
            ];
            foreach ($masters as $m) {
                $stats[$m['status']] = ($stats[$m['status']] ?? 0) + 1;
                $stats['total_demand'] += (float)$m['net_tax_amount'];
            }

            $this->render('admin/property_tax/index', [
                'page_title' => 'Property Tax Management',
                'masters' => $masters,
                'colonies' => $colonies,
                'years' => $years,
                'taxTypes' => $taxTypes,
                'filters' => $filters,
                'stats' => $stats,
            ]);
        } catch (\Throwable $e) {
            error_log('PropertyTaxController::index: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to load tax assessments: ' . $e->getMessage());
            $this->redirect('/admin/property-tax');
        }
    }

    public function create()
    {
        $this->requireAdmin();

        $colonies = $this->db->fetchAll("SELECT id, name FROM colonies WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$this->tenantId()]);
        $plots = $this->db->fetchAll("SELECT id, plot_number, colony_id FROM plots WHERE tenant_id = ? ORDER BY plot_number", [$this->tenantId()]);
        $taxTypes = ['property_tax', 'water_tax', 'sewerage_tax', 'fire_tax', 'education_cess', 'other'];
        $categories = ['residential', 'commercial', 'industrial', 'agricultural', 'vacant_land'];
        $rateUnits = ['per_sqft', 'per_sqm', 'percent_arv', 'percent_cv', 'flat'];

        $this->render('admin/property_tax/form', [
            'page_title' => 'Create Tax Assessment',
            'mode' => 'create',
            'colonies' => $colonies,
            'plots' => $plots,
            'taxTypes' => $taxTypes,
            'categories' => $categories,
            'rateUnits' => $rateUnits,
            'data' => [
                'assessment_year' => date('Y'),
                'tax_type' => 'property_tax',
                'property_category' => 'residential',
                'rate_unit' => 'per_sqft',
                'tax_rate' => 0,
                'rebate_percent' => 0,
                'surcharge_percent' => 0,
                'penalty_rate_per_month' => 1.0,
                'max_penalty_percent' => 25.0,
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'draft',
            ],
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'tenant_id' => $this->tenantId(),
            'colony_id' => (int)($_POST['colony_id'] ?? 0) ?: null,
            'plot_id' => (int)($_POST['plot_id'] ?? 0) ?: null,
            'tax_type' => $_POST['tax_type'] ?? 'property_tax',
            'assessment_year' => (int)($_POST['assessment_year'] ?? date('Y')),
            'tax_authority' => trim($_POST['tax_authority'] ?? ''),
            'ward_zone' => trim($_POST['ward_zone'] ?? ''),
            'property_category' => $_POST['property_category'] ?? 'residential',
            'built_up_area' => (float)($_POST['built_up_area'] ?? 0),
            'land_area' => (float)($_POST['land_area'] ?? 0),
            'annual_rental_value' => (float)($_POST['annual_rental_value'] ?? 0),
            'capital_value' => (float)($_POST['capital_value'] ?? 0),
            'tax_rate' => (float)($_POST['tax_rate'] ?? 0),
            'rate_unit' => $_POST['rate_unit'] ?? 'per_sqft',
            'rebate_percent' => (float)($_POST['rebate_percent'] ?? 0),
            'surcharge_percent' => (float)($_POST['surcharge_percent'] ?? 0),
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'penalty_rate_per_month' => (float)($_POST['penalty_rate_per_month'] ?? 1.0),
            'max_penalty_percent' => (float)($_POST['max_penalty_percent'] ?? 25.0),
            'status' => $_POST['status'] ?? 'draft',
            'notes' => trim($_POST['notes'] ?? ''),
            'created_by' => $_SESSION['admin_id'] ?? 1,
        ];

        if (empty($data['tax_authority'])) {
            $this->setFlash('error', 'Tax authority is required');
            return $this->redirect('/admin/property-tax/create');
        }

        try {
            $id = $this->taxService->createTaxMaster($data);
            $this->setFlash('success', "Tax assessment created successfully (ID: $id)");
            return $this->redirect('/admin/property-tax');
        } catch (\Throwable $e) {
            error_log('PropertyTaxController::store: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create tax assessment: ' . $e->getMessage());
            return $this->redirect('/admin/property-tax/create');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();

        $master = $this->taxService->getTaxMaster((int)$id);
        if (!$master) {
            $this->setFlash('error', 'Tax assessment not found');
            return $this->redirect('/admin/property-tax');
        }

        $colonies = $this->db->fetchAll("SELECT id, name FROM colonies WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$this->tenantId()]);
        $plots = $this->db->fetchAll("SELECT id, plot_number, colony_id FROM plots WHERE tenant_id = ? ORDER BY plot_number", [$this->tenantId()]);
        $taxTypes = ['property_tax', 'water_tax', 'sewerage_tax', 'fire_tax', 'education_cess', 'other'];
        $categories = ['residential', 'commercial', 'industrial', 'agricultural', 'vacant_land'];
        $rateUnits = ['per_sqft', 'per_sqm', 'percent_arv', 'percent_cv', 'flat'];

        $this->render('admin/property_tax/form', [
            'page_title' => 'Edit Tax Assessment',
            'mode' => 'edit',
            'master' => $master,
            'colonies' => $colonies,
            'plots' => $plots,
            'taxTypes' => $taxTypes,
            'categories' => $categories,
            'rateUnits' => $rateUnits,
            'data' => $master,
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $master = $this->taxService->getTaxMaster((int)$id);
        if (!$master) {
            $this->setFlash('error', 'Tax assessment not found');
            return $this->redirect('/admin/property-tax');
        }

        $data = [
            'colony_id' => (int)($_POST['colony_id'] ?? 0) ?: null,
            'plot_id' => (int)($_POST['plot_id'] ?? 0) ?: null,
            'tax_type' => $_POST['tax_type'] ?? 'property_tax',
            'assessment_year' => (int)($_POST['assessment_year'] ?? date('Y')),
            'tax_authority' => trim($_POST['tax_authority'] ?? ''),
            'ward_zone' => trim($_POST['ward_zone'] ?? ''),
            'property_category' => $_POST['property_category'] ?? 'residential',
            'built_up_area' => (float)($_POST['built_up_area'] ?? 0),
            'land_area' => (float)($_POST['land_area'] ?? 0),
            'annual_rental_value' => (float)($_POST['annual_rental_value'] ?? 0),
            'capital_value' => (float)($_POST['capital_value'] ?? 0),
            'tax_rate' => (float)($_POST['tax_rate'] ?? 0),
            'rate_unit' => $_POST['rate_unit'] ?? 'per_sqft',
            'rebate_percent' => (float)($_POST['rebate_percent'] ?? 0),
            'surcharge_percent' => (float)($_POST['surcharge_percent'] ?? 0),
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'penalty_rate_per_month' => (float)($_POST['penalty_rate_per_month'] ?? 1.0),
            'max_penalty_percent' => (float)($_POST['max_penalty_percent'] ?? 25.0),
            'status' => $_POST['status'] ?? 'draft',
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        if (empty($data['tax_authority'])) {
            $this->setFlash('error', 'Tax authority is required');
            return $this->redirect("/admin/property-tax/edit/$id");
        }

        try {
            $this->taxService->updateTaxMaster((int)$id, $data);
            $this->setFlash('success', 'Tax assessment updated successfully');
            return $this->redirect('/admin/property-tax');
        } catch (\Throwable $e) {
            error_log('PropertyTaxController::update: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to update tax assessment: ' . $e->getMessage());
            return $this->redirect("/admin/property-tax/edit/$id");
        }
    }

    public function generateDemand($id)
    {
        $this->requireAdmin();

        $noticeNumber = $this->taxService->generateDemandNotice((int)$id);
        if ($noticeNumber) {
            $this->setFlash('success', "Demand notice generated: $noticeNumber");
        } else {
            $this->setFlash('error', 'Failed to generate demand notice');
        }
        return $this->redirect('/admin/property-tax');
    }

    public function payments($taxMasterId)
    {
        $this->requireAdmin();

        $master = $this->taxService->getTaxMaster((int)$taxMasterId);
        if (!$master) {
            $this->setFlash('error', 'Tax assessment not found');
            return $this->redirect('/admin/property-tax');
        }

        $payments = $this->taxService->getPaymentHistory((int)$taxMasterId);

        $this->render('admin/property_tax/payments', [
            'page_title' => "Payments for {$master['tax_authority']} - {$master['assessment_year']}",
            'master' => $master,
            'payments' => $payments,
        ]);
    }

    public function recordPayment($taxMasterId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $master = $this->taxService->getTaxMaster((int)$taxMasterId);
        if (!$master) {
            $this->setFlash('error', 'Tax assessment not found');
            return $this->redirect('/admin/property-tax');
        }

        $data = [
            'tax_master_id' => (int)$taxMasterId,
            'plot_id' => (int)($_POST['plot_id'] ?? 0) ?: null,
            'customer_id' => (int)($_POST['customer_id'] ?? 0) ?: null,
            'booking_id' => (int)($_POST['booking_id'] ?? 0) ?: null,
            'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'payment_mode' => $_POST['payment_mode'] ?? 'cash',
            'cheque_dd_number' => trim($_POST['cheque_dd_number'] ?? ''),
            'cheque_dd_date' => $_POST['cheque_dd_date'] ?? null,
            'bank_name' => trim($_POST['bank_name'] ?? ''),
            'bank_branch' => trim($_POST['bank_branch'] ?? ''),
            'transaction_ref' => trim($_POST['transaction_ref'] ?? ''),
            'upi_ref' => trim($_POST['upi_ref'] ?? ''),
            'base_tax_paid' => (float)($_POST['base_tax_paid'] ?? $master['net_tax_amount']),
            'interest_paid' => (float)($_POST['interest_paid'] ?? 0),
            'rebate_conditions' => json_decode($_POST['rebate_conditions'] ?? '{}', true),
            'notes' => trim($_POST['notes'] ?? ''),
            'created_by' => $_SESSION['admin_id'] ?? 1,
        ];

        try {
            $paymentId = $this->taxService->recordPayment($data);
            $this->setFlash('success', "Payment recorded (ID: $paymentId). Awaiting verification.");
            return $this->redirect("/admin/property-tax/payments/$taxMasterId");
        } catch (\Throwable $e) {
            error_log('PropertyTaxController::recordPayment: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to record payment: ' . $e->getMessage());
            return $this->redirect("/admin/property-tax/payments/$taxMasterId");
        }
    }

    public function verifyPayment($paymentId)
    {
        $this->requireAdmin();

        $this->taxService->verifyPayment((int)$paymentId, $_SESSION['admin_id'] ?? 1);
        $this->setFlash('success', 'Payment verified and receipt generated');
        return $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/property-tax');
    }

    public function demandRegister()
    {
        $this->requireAdmin();

        $colonies = $this->db->fetchAll("SELECT id, name FROM colonies WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$this->tenantId()]);
        $year = (int)($_GET['year'] ?? date('Y'));
        $colonyId = (int)($_GET['colony_id'] ?? 0);

        $register = null;
        $masters = [];
        if ($colonyId) {
            $register = $this->taxService->getColonyDemandRegister($colonyId, $year);
            if (!$register && $_GET['generate'] ?? false) {
                $register = $this->taxService->generateDemandRegister($colonyId, $year);
            }
            $masters = $this->taxService->getTaxMasterList(['colony_id' => $colonyId, 'assessment_year' => $year]);
        }

        $this->render('admin/property_tax/demand_register', [
            'page_title' => 'Property Tax Demand Register',
            'colonies' => $colonies,
            'year' => $year,
            'colony_id' => $colonyId,
            'register' => $register,
            'masters' => $masters,
        ]);
    }

    public function rebateRules()
    {
        $this->requireAdmin();

        $rules = $this->taxService->getRebateRules();

        $this->render('admin/property_tax/rebate_rules', [
            'page_title' => 'Tax Rebate Rules',
            'rules' => $rules,
        ]);
    }

    public function rebateRuleStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'rule_name' => trim($_POST['rule_name'] ?? ''),
            'rule_code' => trim($_POST['rule_code'] ?? ''),
            'applicable_tax_types' => json_decode($_POST['applicable_tax_types'] ?? '[]', true),
            'rebate_type' => $_POST['rebate_type'] ?? 'percent',
            'rebate_value' => (float)($_POST['rebate_value'] ?? 0),
            'max_rebate_amount' => $_POST['max_rebate_amount'] !== '' ? (float)$_POST['max_rebate_amount'] : null,
            'conditions_json' => json_decode($_POST['conditions_json'] ?? '{}', true),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'effective_from' => $_POST['effective_from'] ?? date('Y-m-d'),
            'effective_to' => $_POST['effective_to'] ?? null,
        ];

        if (empty($data['rule_name']) || empty($data['rule_code'])) {
            $this->setFlash('error', 'Rule name and code are required');
            return $this->redirect('/admin/property-tax/rebate-rules');
        }

        try {
            $this->taxService->createRebateRule($data);
            $this->setFlash('success', 'Rebate rule created');
        } catch (\Throwable $e) {
            error_log('PropertyTaxController::rebateRuleStore: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create rebate rule: ' . $e->getMessage());
        }
        return $this->redirect('/admin/property-tax/rebate-rules');
    }

    public function rebateRuleUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'rule_name' => trim($_POST['rule_name'] ?? ''),
            'rule_code' => trim($_POST['rule_code'] ?? ''),
            'applicable_tax_types' => json_decode($_POST['applicable_tax_types'] ?? '[]', true),
            'rebate_type' => $_POST['rebate_type'] ?? 'percent',
            'rebate_value' => (float)($_POST['rebate_value'] ?? 0),
            'max_rebate_amount' => $_POST['max_rebate_amount'] !== '' ? (float)$_POST['max_rebate_amount'] : null,
            'conditions_json' => json_decode($_POST['conditions_json'] ?? '{}', true),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'effective_from' => $_POST['effective_from'] ?? date('Y-m-d'),
            'effective_to' => $_POST['effective_to'] ?? null,
        ];

        try {
            $this->taxService->updateRebateRule((int)$id, $data);
            $this->setFlash('success', 'Rebate rule updated');
        } catch (\Throwable $e) {
            error_log('PropertyTaxController::rebateRuleUpdate: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to update rebate rule: ' . $e->getMessage());
        }
        return $this->redirect('/admin/property-tax/rebate-rules');
    }

    public function rebateRuleDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $this->taxService->deleteRebateRule((int)$id);
            $this->setFlash('success', 'Rebate rule deleted');
        } catch (\Throwable $e) {
            error_log('PropertyTaxController::rebateRuleDelete: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to delete rebate rule: ' . $e->getMessage());
        }
        return $this->redirect('/admin/property-tax/rebate-rules');
    }

    public function printReceipt($paymentId)
    {
        $this->requireAdmin();

        $payment = $this->taxService->getPayment((int)$paymentId);
        if (!$payment) {
            $this->setFlash('error', 'Payment not found');
            return $this->redirect('/admin/property-tax');
        }

        $master = $this->taxService->getTaxMaster($payment['tax_master_id']);
        $customer = $payment['customer_id'] ? $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$payment['customer_id']]) : null;
        $plot = $payment['plot_id'] ? $this->db->fetchOne("SELECT * FROM plots WHERE id = ?", [$payment['plot_id']]) : null;

        $this->render('admin/property_tax/receipt_print', [
            'page_title' => "Tax Receipt #{$payment['receipt_number']}",
            'payment' => $payment,
            'master' => $master,
            'customer' => $customer,
            'plot' => $plot,
        ]);
    }
}