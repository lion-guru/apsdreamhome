<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Tax/GST Reporting Model
 * Handles GST calculations, returns filing, tax reports, and compliance
 */
class Tax extends Model
{
    protected $table = 'gst_settings';

    /**
     * Get GST settings
     */
    public function getGstSettings(): ?array
    {
        return $this->query("SELECT * FROM gst_settings WHERE is_active = 1 LIMIT 1")->fetch();
    }

    /**
     * Update GST settings
     */
    public function updateGstSettings(array $data): array
    {
        $settings = $this->getGstSettings();

        if ($settings) {
            $this->update($settings['id'], array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s')
            ]));
        } else {
            $this->insert(array_merge($data, [
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }

        return [
            'success' => true,
            'message' => 'GST settings updated successfully'
        ];
    }

    /**
     * Calculate GST for invoice items
     */
    public function calculateGST(array $items, string $placeOfSupply, string $invoiceType = 'b2b'): array
    {
        $gstSettings = $this->getGstSettings();
        $businessStateCode = $gstSettings['state_code'] ?? '07'; // Default to Delhi

        $calculations = [
            'items' => [],
            'totals' => [
                'subtotal' => 0,
                'cgst_total' => 0,
                'sgst_total' => 0,
                'igst_total' => 0,
                'cess_total' => 0,
                'grand_total' => 0
            ]
        ];

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $hsnCode = $item['hsn_code'] ?? null;

            // Get HSN rates
            $hsnRates = $this->getHsnRates($hsnCode);

            $lineTotal = $quantity * $unitPrice;
            $cgstAmount = 0;
            $sgstAmount = 0;
            $igstAmount = 0;
            $cessAmount = 0;

            // Determine GST rates based on place of supply and invoice type
            if ($placeOfSupply === $businessStateCode) {
                // Intra-state supply - CGST + SGST
                $cgstAmount = ($lineTotal * $hsnRates['cgst_rate']) / 100;
                $sgstAmount = ($lineTotal * $hsnRates['sgst_rate']) / 100;
            } else {
                // Inter-state supply - IGST
                $igstAmount = ($lineTotal * $hsnRates['igst_rate']) / 100;
            }

            $cessAmount = ($lineTotal * $hsnRates['cess_rate']) / 100;
            $totalWithTax = $lineTotal + $cgstAmount + $sgstAmount + $igstAmount + $cessAmount;

            $calculations['items'][] = [
                'item_name' => $item['item_name'],
                'hsn_code' => $hsnCode,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'cgst_rate' => $hsnRates['cgst_rate'],
                'cgst_amount' => round($cgstAmount, 2),
                'sgst_rate' => $hsnRates['sgst_rate'],
                'sgst_amount' => round($sgstAmount, 2),
                'igst_rate' => $hsnRates['igst_rate'],
                'igst_amount' => round($igstAmount, 2),
                'cess_rate' => $hsnRates['cess_rate'],
                'cess_amount' => round($cessAmount, 2),
                'total_with_tax' => round($totalWithTax, 2)
            ];

            // Update totals
            $calculations['totals']['subtotal'] += $lineTotal;
            $calculations['totals']['cgst_total'] += $cgstAmount;
            $calculations['totals']['sgst_total'] += $sgstAmount;
            $calculations['totals']['igst_total'] += $igstAmount;
            $calculations['totals']['cess_total'] += $cessAmount;
            $calculations['totals']['grand_total'] += $totalWithTax;
        }

        // Round totals
        foreach ($calculations['totals'] as &$total) {
            $total = round($total, 2);
        }

        return $calculations;
    }

    /**
     * Get HSN/SAC rates
     */
    public function getHsnRates(string $hsnCode = null): array
    {
        if (!$hsnCode) {
            // Default rates for services
            return [
                'cgst_rate' => 9,
                'sgst_rate' => 9,
                'igst_rate' => 18,
                'cess_rate' => 0
            ];
        }

        $hsnData = $this->query(
            "SELECT * FROM hsn_sac_codes WHERE code = ? AND is_active = 1 ORDER BY effective_from DESC LIMIT 1",
            [$hsnCode]
        )->fetch();

        if ($hsnData) {
            return [
                'cgst_rate' => $hsnData['cgst_rate'],
                'sgst_rate' => $hsnData['sgst_rate'],
                'igst_rate' => $hsnData['igst_rate'],
                'cess_rate' => $hsnData['cess_rate']
            ];
        }

        // Fallback to default rates
        return [
            'cgst_rate' => 9,
            'sgst_rate' => 9,
            'igst_rate' => 18,
            'cess_rate' => 0
        ];
    }

    /**
     * Generate GSTR-1 report (Outward supplies)
     */
    public function generateGSTR1(string $fromDate, string $endDate): array
    {
        $db = Database::getInstance();

        // Get all invoices in the period
        $invoices = $db->query(
            "SELECT i.*, gid.place_of_supply, gid.reverse_charge, gid.invoice_type
             FROM invoices i
             LEFT JOIN gst_invoice_details gid ON i.id = gid.invoice_id
             WHERE i.invoice_date BETWEEN ? AND ?
             AND i.status IN ('sent', 'paid')
             ORDER BY i.invoice_date ASC",
            [$fromDate, $endDate]
        )->fetchAll();

        $gstr1 = [
            'summary' => [
                'total_invoices' => count($invoices),
                'total_taxable_value' => 0,
                'total_igst' => 0,
                'total_cgst' => 0,
                'total_sgst' => 0,
                'total_cess' => 0
            ],
            'invoices' => []
        ];

        foreach ($invoices as $invoice) {
            // Get invoice items with GST details
            $items = $db->query(
                "SELECT ii.*, hsn.code as hsn_code, hsn.cgst_rate, hsn.sgst_rate, hsn.igst_rate, hsn.cess_rate
                 FROM invoice_items ii
                 LEFT JOIN hsn_sac_codes hsn ON ii.item_name LIKE CONCAT('%', hsn.code, '%')
                 WHERE ii.invoice_id = ?",
                [$invoice['id']]
            )->fetchAll();

            $invoiceData = [
                'invoice_number' => $invoice['invoice_number'],
                'invoice_date' => $invoice['invoice_date'],
                'client_name' => $invoice['client_name'],
                'gstin' => $this->getClientGSTIN($invoice['client_id'], $invoice['client_type']),
                'place_of_supply' => $invoice['place_of_supply'],
                'reverse_charge' => $invoice['reverse_charge'],
                'invoice_type' => $invoice['invoice_type'],
                'items' => $items,
                'subtotal' => $invoice['subtotal'],
                'tax_amount' => $invoice['tax_amount'],
                'total_amount' => $invoice['total_amount']
            ];

            $gstr1['invoices'][] = $invoiceData;

            // Update summary
            $gstr1['summary']['total_taxable_value'] += $invoice['subtotal'];

            // Calculate GST components (simplified)
            if ($invoice['place_of_supply'] === '07') { // Same state as business
                $gstr1['summary']['total_cgst'] += $invoice['tax_amount'] / 2;
                $gstr1['summary']['total_sgst'] += $invoice['tax_amount'] / 2;
            } else {
                $gstr1['summary']['total_igst'] += $invoice['tax_amount'];
            }
        }

        return $gstr1;
    }

    /**
     * Generate GSTR-3B report (Monthly return)
     */
    public function generateGSTR3B(string $fromDate, string $endDate): array
    {
        $db = Database::getInstance();

        // Get outward supplies (sales)
        $outwardSupplies = $db->query(
            "SELECT SUM(subtotal) as taxable_value, SUM(tax_amount) as tax_amount
             FROM invoices
             WHERE invoice_date BETWEEN ? AND ?
             AND status IN ('sent', 'paid')",
            [$fromDate, $endDate]
        )->fetch();

        // Get inward supplies (purchases) - This would need a purchases/inward supplies table
        // For now, using placeholder values
        $inwardSupplies = [
            'taxable_value' => 0,
            'tax_amount' => 0
        ];

        $gstr3b = [
            'period' => date('M Y', strtotime($fromDate)),
            'gstin' => $this->getGstSettings()['gstin'] ?? '',
            'outward_supplies' => [
                'taxable_value' => $outwardSupplies['taxable_value'] ?? 0,
                'integrated_tax' => 0, // Would need to calculate IGST specifically
                'central_tax' => ($outwardSupplies['tax_amount'] ?? 0) / 2,
                'state_tax' => ($outwardSupplies['tax_amount'] ?? 0) / 2,
                'cess' => 0
            ],
            'inward_supplies' => [
                'taxable_value' => $inwardSupplies['taxable_value'],
                'integrated_tax' => 0,
                'central_tax' => $inwardSupplies['tax_amount'] / 2,
                'state_tax' => $inwardSupplies['tax_amount'] / 2,
                'cess' => 0
            ],
            'net_tax_liability' => [
                'integrated_tax' => 0,
                'central_tax' => (($outwardSupplies['tax_amount'] ?? 0) - $inwardSupplies['tax_amount']) / 2,
                'state_tax' => (($outwardSupplies['tax_amount'] ?? 0) - $inwardSupplies['tax_amount']) / 2,
                'cess' => 0
            ]
        ];

        return $gstr3b;
    }

    /**
     * Get tax ledger summary
     */
    public function getTaxLedgerSummary(string $fromDate, string $endDate): array
    {
        $db = Database::getInstance();

        $summary = $db->query(
            "SELECT
                ledger_type,
                SUM(CASE WHEN debit_amount > 0 THEN debit_amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN credit_amount > 0 THEN credit_amount ELSE 0 END) as total_credit,
                MAX(balance) as current_balance
             FROM tax_ledgers
             WHERE transaction_date BETWEEN ? AND ?
             GROUP BY ledger_type
             ORDER BY ledger_type",
            [$fromDate, $endDate]
        )->fetchAll();

        return $summary;
    }

    /**
     * Generate tax reconciliation report
     */
    public function generateTaxReconciliation(string $fromDate, string $endDate): array
    {
        $db = Database::getInstance();

        // Books vs Returns comparison
        $booksData = $db->query(
            "SELECT
                SUM(CASE WHEN i.invoice_date BETWEEN ? AND ? THEN i.tax_amount ELSE 0 END) as books_tax,
                COUNT(CASE WHEN i.invoice_date BETWEEN ? AND ? THEN 1 END) as books_invoices
             FROM invoices i
             WHERE i.status IN ('sent', 'paid')",
            [$fromDate, $endDate, $fromDate, $endDate]
        )->fetch();

        // GST returns data (would come from gst_returns table)
        $returnsData = $db->query(
            "SELECT
                SUM(CASE WHEN gr.period_from >= ? AND gr.period_to <= ? THEN 1 ELSE 0 END) as filed_returns
             FROM gst_returns gr
             WHERE gr.status = 'filed'",
            [$fromDate, $endDate]
        )->fetch();

        return [
            'period' => date('M Y', strtotime($fromDate)),
            'books_data' => [
                'total_tax' => $booksData['books_tax'] ?? 0,
                'total_invoices' => $booksData['books_invoices'] ?? 0
            ],
            'returns_data' => [
                'filed_returns' => $returnsData['filed_returns'] ?? 0
            ],
            'reconciliation' => [
                'variance' => 0, // Would need actual returns data to calculate
                'status' => 'pending_verification'
            ]
        ];
    }

    /**
     * Get HSN/SAC wise summary
     */
    public function getHsnWiseSummary(string $fromDate, string $endDate): array
    {
        $db = Database::getInstance();

        $summary = $db->query(
            "SELECT
                COALESCE(hsn.code, '999999') as hsn_code,
                COALESCE(hsn.description, ii.item_name) as description,
                SUM(ii.quantity) as total_quantity,
                SUM(ii.line_total) as taxable_value,
                SUM(ii.tax_amount) as tax_amount,
                SUM(ii.line_total + ii.tax_amount) as total_value
             FROM invoice_items ii
             LEFT JOIN invoices i ON ii.invoice_id = i.id
             LEFT JOIN hsn_sac_codes hsn ON ii.item_name LIKE CONCAT('%', hsn.code, '%')
             WHERE i.invoice_date BETWEEN ? AND ?
             AND i.status IN ('sent', 'paid')
             GROUP BY hsn.code, hsn.description, ii.item_name
             ORDER BY taxable_value DESC",
            [$fromDate, $endDate]
        )->fetchAll();

        return $summary;
    }

    /**
     * Get client GSTIN (helper method)
     */
    private function getClientGSTIN(int $clientId = null, string $clientType = 'customer'): ?string
    {
        if (!$clientId) return null;

        // This would need to be implemented based on your client tables
        // For now, return a placeholder
        return '22AAAAA0000A1Z5'; // Sample GSTIN
    }

    /**
     * Generate TDS report
     */
    public function generateTDSReport(string $fromDate, string $endDate): array
    {
        // TDS calculation logic would go here
        // This is a simplified implementation

        return [
            'period' => date('M Y', strtotime($fromDate)),
            'tds_summary' => [
                'total_deducted' => 0,
                'deposited' => 0,
                'outstanding' => 0
            ],
            'deductions' => []
        ];
    }

    /**
     * Export GST report as JSON for GST portal
     */
    public function exportGSTR1Json(string $fromDate, string $endDate): string
    {
        $gstr1Data = $this->generateGSTR1($fromDate, $endDate);

        $gstSettings = $this->getGstSettings();

        $jsonData = [
            'gstin' => $gstSettings['gstin'] ?? '',
            'fp' => date('Ym', strtotime($fromDate)),
            'gt' => 0, // Total turnover - would need to calculate
            'cur_gt' => 0, // Current turnover
            'b2b' => [], // B2B invoices
            'b2c' => [], // B2C invoices
            'exp' => [], // Export invoices
            'cdn' => [], // Credit/Debit notes
            'b2ba' => [], // Amended B2B invoices
            'cdnra' => []  // Amended CDN
        ];

        // Convert invoices to GST portal format
        foreach ($gstr1Data['invoices'] as $invoice) {
            $gstInvoice = [
                'ctin' => $invoice['gstin'] ?? 'URP', // URP for Unregistered Person
                'inv' => [[
                    'inum' => $invoice['invoice_number'],
                    'idt' => date('d-m-Y', strtotime($invoice['invoice_date'])),
                    'val' => $invoice['total_amount'],
                    'pos' => $invoice['place_of_supply'] ?? '07',
                    'rchrg' => $invoice['reverse_charge'] ?? 'N',
                    'inv_typ' => 'R', // Regular invoice
                    'itms' => [] // Items would go here
                ]]
            ];

            if ($invoice['invoice_type'] === 'b2b') {
                $jsonData['b2b'][] = $gstInvoice;
            } elseif ($invoice['invoice_type'] === 'b2c') {
                $jsonData['b2c'][] = $gstInvoice;
            }
        }

        return json_encode($jsonData, JSON_PRETTY_PRINT);
    }
}
