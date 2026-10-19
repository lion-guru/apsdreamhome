<?php

namespace App\Http\Controllers\Employee;

use \App\Traits\TenantAwareTrait;

class EmployeeTaxComplianceController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
        $this->data = [];
    }

    public function index()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'] ?? 0;

        $taxData = [];
        try {
            $taxData = $this->db->fetchAll("
                SELECT * FROM employee_tax_compliance 
                WHERE employee_id = ? 
                ORDER BY financial_year DESC
            ", [$employeeId]);
        } catch (\Exception $e) {}

        $this->data = [
            'page_title' => 'TDS & GST Compliance',
            'taxData' => $taxData,
        ];
        $this->render('employee/tax_compliance');
    }
}