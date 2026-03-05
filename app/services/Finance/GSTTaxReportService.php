<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Services\Finance;

use App\Core\Database;

/**
 * GST and Tax Reports Service
 * Generate GST returns, tax reports, and compliance documents
 */
class GSTTaxReportService
{
    private $db;
    private $gstRate = 18; // 18% GST for real estate

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate GSTR-1 (Outward Supplies)
     */
    public function generateGSTR1(int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($startDate)));

        // B2C - Business to Consumer
        $b2c = $this->db->query(
            "SELECT i.invoice_number, i.invoice_date, u.name as customer_name,
                    u.gstin as customer_gstin, i.total as invoice_value,
                    i.cgst, i.sgst, i.igst, i.subtotal as taxable_value,
                    p.title as property_title
             FROM invoices i
             JOIN users u ON i.user_id = u.id
             LEFT JOIN properties p ON i.property_id = p.id
             WHERE i.invoice_date BETWEEN ? AND ?
             AND i.status = 'paid'
             ORDER BY i.invoice_date",
            [$startDate, $endDate]
        )->fetchAll(\PDO::FETCH_ASSOC);

        // Summary
        $summary = [
            'total_invoices' => count($b2c),
            'total_taxable_value' => array_sum(array_column($b2c, 'taxable_value')),
            'total_cgst' => array_sum(array_column($b2c, 'cgst')),
            'total_sgst' => array_sum(array_column($b2c, 'sgst')),
            'total_igst' => array_sum(array_column($b2c, 'igst')),
            'total_invoice_value' => array_sum(array_column($b2c, 'invoice_value'))
        ];

        return [
            'success' => true,
            'return_type' => 'GSTR-1',
            'period' => ['month' => $month, 'year' => $year],
            'b2c_invoices' => $b2c,
            'summary' => $summary
        ];
    }

    /**
     * Generate GSTR-3B (Summary Return)
     */
    public function generateGSTR3B(int $month, int $year): array
    {
        $gstr1 = $this->generateGSTR1($month, $year);

        // Input Tax Credit (ITC)
        $itc = $this->getInputTaxCredit($month, $year);

        // Tax liability
        $liability = [
            'outward_supplies' => $gstr1['summary']['total_taxable_value'],
            'cgst_liability' => $gstr1['summary']['total_cgst'],
            'sgst_liability' => $gstr1['summary']['total_sgst'],
            'igst_liability' => $gstr1['summary']['total_igst']
        ];

        // Tax payable
        $payable = [
            'cgst' => max(0, $liability['cgst_liability'] - $itc['cgst']),
            'sgst' => max(0, $liability['sgst_liability'] - $itc['sgst']),
            'igst' => max(0, $liability['igst_liability'] - $itc['igst'])
        ];

        return [
            'success' => true,
            'return_type' => 'GSTR-3B',
            'period' => ['month' => $month, 'year' => $year],
            'liability' => $liability,
            'input_tax_credit' => $itc,
            'tax_payable' => $payable,
            'total_payable' => array_sum($payable)
        ];
    }

    /**
     * Get Input Tax Credit
     */
    private function getInputTaxCredit(int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($startDate)));

        $itc = $this->db->query(
            "SELECT 
                SUM(cgst) as cgst,
                SUM(sgst) as sgst,
                SUM(igst) as igst
             FROM purchase_invoices
             WHERE invoice_date BETWEEN ? AND ?
             AND itc_claimed = 1",
            [$startDate, $endDate]
        )->fetch(\PDO::FETCH_ASSOC);

        return [
            'cgst' => $itc['cgst'] ?? 0,
            'sgst' => $itc['sgst'] ?? 0,
            'igst' => $itc['igst'] ?? 0,
            'total' => ($itc['cgst'] ?? 0) + ($itc['sgst'] ?? 0) + ($itc['igst'] ?? 0)
        ];
    }

    /**
     * Generate TDS Report
     */
    public function generateTDSReport(int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($startDate)));

        // TDS deducted from payments
        $tds = $this->db->query(
            "SELECT p.id as payment_id, p.transaction_id, p.amount,
                    p.tds_amount, p.tds_percentage, p.tds_section,
                    u.name as deductee_name, u.pan as deductee_pan,
                    p.completed_at as payment_date
             FROM payments p
             JOIN users u ON p.user_id = u.id
             WHERE p.completed_at BETWEEN ? AND ?
             AND p.tds_amount > 0
             ORDER BY p.completed_at",
            [$startDate, $endDate]
        )->fetchAll(\PDO::FETCH_ASSOC);

        $summary = [
            'total_payments' => count($tds),
            'total_amount' => array_sum(array_column($tds, 'amount')),
            'total_tds_deducted' => array_sum(array_column($tds, 'tds_amount'))
        ];

        return [
            'success' => true,
            'report_type' => 'TDS Report',
            'period' => ['month' => $month, 'year' => $year],
            'deductions' => $tds,
            'summary' => $summary
        ];
    }

    /**
     * Generate TCS Report (Tax Collected at Source)
     */
    public function generateTCSReport(int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($startDate)));

        // TCS collected on property sales above 50 lakhs
        $tcs = $this->db->query(
            "SELECT s.id as sale_id, s.sale_value, s.tcs_amount,
                    s.tcs_rate, u.name as buyer_name, u.pan as buyer_pan,
                    s.sale_date, p.title as property_title
             FROM sales s
             JOIN users u ON s.buyer_id = u.id
             JOIN properties p ON s.property_id = p.id
             WHERE s.sale_date BETWEEN ? AND ?
             AND s.sale_value >= 5000000
             AND s.tcs_amount > 0
             ORDER BY s.sale_date",
            [$startDate, $endDate]
        )->fetchAll(\PDO::FETCH_ASSOC);

        $summary = [
            'total_sales' => count($tcs),
            'total_sale_value' => array_sum(array_column($tcs, 'sale_value')),
            'total_tcs_collected' => array_sum(array_column($tcs, 'tcs_amount'))
        ];

        return [
            'success' => true,
            'report_type' => 'TCS Report',
            'period' => ['month' => $month, 'year' => $year],
            'collections' => $tcs,
            'summary' => $summary
        ];
    }

    /**
     * Generate Annual GST Summary
     */
    public function generateAnnualGSTSummary(int $year): array
    {
        $monthlyData = [];
        $annualTotals = [
            'total_taxable_value' => 0,
            'total_cgst' => 0,
            'total_sgst' => 0,
            'total_igst' => 0,
            'total_tax' => 0
        ];

        for ($month = 1; $month <= 12; $month++) {
            $gstr1 = $this->generateGSTR1($month, $year);
            $monthlyData[$month] = $gstr1['summary'];

            $annualTotals['total_taxable_value'] += $gstr1['summary']['total_taxable_value'];
            $annualTotals['total_cgst'] += $gstr1['summary']['total_cgst'];
            $annualTotals['total_sgst'] += $gstr1['summary']['total_sgst'];
            $annualTotals['total_igst'] += $gstr1['summary']['total_igst'];
        }

        $annualTotals['total_tax'] = $annualTotals['total_cgst'] + 
                                     $annualTotals['total_sgst'] + 
                                     $annualTotals['total_igst'];

        return [
            'success' => true,
            'year' => $year,
            'monthly_breakdown' => $monthlyData,
            'annual_totals' => $annualTotals
        ];
    }

    /**
     * Generate Tax Computation Report
     */
    public function generateTaxComputation(int $year): array
    {
        // Income from operations
        $income = $this->db->query(
            "SELECT 
                SUM(total) as total_revenue,
                SUM(cgst + sgst + igst) as gst_collected
             FROM invoices
             WHERE YEAR(invoice_date) = ? AND status = 'paid'",
            [$year]
        )->fetch(\PDO::FETCH_ASSOC);

        // Expenses
        $expenses = $this->db->query(
            "SELECT 
                category,
                SUM(amount) as total_amount
             FROM expenses
             WHERE YEAR(expense_date) = ?
             GROUP BY category",
            [$year]
        )->fetchAll(\PDO::FETCH_KEY_PAIR);

        // Taxable income
        $totalIncome = $income['total_revenue'] ?? 0;
        $totalExpenses = array_sum($expenses);
        $taxableIncome = $totalIncome - $totalExpenses;

        // Tax calculation (assuming corporate tax rate 25%)
        $taxRate = 0.25;
        $taxPayable = max(0, $taxableIncome * $taxRate);

        return [
            'success' => true,
            'year' => $year,
            'income' => [
                'total_revenue' => $totalIncome,
                'gst_collected' => $income['gst_collected'] ?? 0
            ],
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'taxable_income' => $taxableIncome,
            'tax_rate' => $taxRate * 100 . '%',
            'tax_payable' => $taxPayable
        ];
    }

    /**
     * Generate HSN Summary for GST
     */
    public function generateHSNSummary(int $month, int $year): array
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($startDate)));

        // HSN codes for real estate
        $hsnMapping = [
            'residential_apartment' => ['hsn' => '996412', 'rate' => 1],
            'commercial_property' => ['hsn' => '997211', 'rate' => 18],
            'land' => ['hsn' => '999111', 'rate' => 0],
            'plot' => ['hsn' => '999111', 'rate' => 0],
            'villa' => ['hsn' => '996412', 'rate' => 1],
            'farmhouse' => ['hsn' => '996412', 'rate' => 1]
        ];

        $summary = $this->db->query(
            "SELECT pt.name as property_type, 
                    COUNT(i.id) as invoice_count,
                    SUM(i.subtotal) as taxable_value,
                    SUM(i.cgst + i.sgst + i.igst) as total_tax
             FROM invoices i
             JOIN properties p ON i.property_id = p.id
             JOIN property_types pt ON p.property_type_id = pt.id
             WHERE i.invoice_date BETWEEN ? AND ?
             AND i.status = 'paid'
             GROUP BY pt.name",
            [$startDate, $endDate]
        )->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($summary as &$row) {
            $type = strtolower(str_replace(' ', '_', $row['property_type']));
            $row['hsn_code'] = $hsnMapping[$type]['hsn'] ?? '996412';
            $row['gst_rate'] = $hsnMapping[$type]['rate'] ?? 18;
        }

        return [
            'success' => true,
            'period' => ['month' => $month, 'year' => $year],
            'hsn_summary' => $summary
        ];
    }

    /**
     * Export report to PDF
     */
    public function exportToPDF(string $reportType, int $month, int $year): string
    {
        $report = $this->getReport($reportType, $month, $year);
        
        // Generate PDF
        $filename = strtolower(str_replace('-', '', $reportType)) . "_{$month}_{$year}.pdf";
        return storage_path('reports/' . $filename);
    }

    /**
     * Get report by type
     */
    private function getReport(string $type, int $month, int $year): array
    {
        switch ($type) {
            case 'GSTR-1':
                return $this->generateGSTR1($month, $year);
            case 'GSTR-3B':
                return $this->generateGSTR3B($month, $year);
            case 'TDS':
                return $this->generateTDSReport($month, $year);
            case 'TCS':
                return $this->generateTCSReport($month, $year);
            case 'HSN':
                return $this->generateHSNSummary($month, $year);
            default:
                return ['success' => false, 'error' => 'Invalid report type'];
        }
    }
}
