<?php
namespace App\Services\Filing;

use PDO;

/**
 * GSTFilingService — GST-specific e-filing operations
 * GSTR-1 JSON generation, GSTR-3B, HSN summary, GSTN API client
 */
class GSTFilingService
{
    private $db;
    private $efiling;

    public function __construct($pdo = null)
    {
        $this->db = $pdo;
        $this->efiling = new EFilingService($pdo);
    }

    private function getPdo(): PDO
    {
        if ($this->db instanceof PDO) return $this->db;
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
        return $this->db;
    }

    private function getCompanyGSTIN(): ?string
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT credential_value FROM company_credentials
                WHERE credential_type = 'gst' AND status = 'active' AND is_primary = 1 LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['credential_value'] ?? null;
        } catch (\Exception $e) { error_log("[GSTFilingService] getCompanyGSTIN: " . $e->getMessage()); return null; }
    }

    // ========== GSTR-1 ==========

    public function generateGSTR1(int $month, int $year, string $fy = null): array
    {
        $pdo = $this->getPdo();
        $gstin = $this->getCompanyGSTIN();
        $fy = $fy ?: $this->efiling->getCurrentFinancialYear();

        try {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            // B2B invoices (with GSTIN)
            $b2bStmt = $pdo->prepare("SELECT * FROM gst_transactions
                WHERE transaction_date BETWEEN ? AND ?
                AND transaction_type = 'output'
                AND party_gstin IS NOT NULL AND party_gstin != ''
                AND gstr1_status != 'filed'
                ORDER BY invoice_date ASC");
            $b2bStmt->execute([$startDate, $endDate]);
            $b2bRecords = $b2bStmt->fetchAll(PDO::FETCH_ASSOC);

            // B2C invoices (no GSTIN)
            $b2cStmt = $pdo->prepare("SELECT * FROM gst_transactions
                WHERE transaction_date BETWEEN ? AND ?
                AND transaction_type = 'output'
                AND (party_gstin IS NULL OR party_gstin = '')
                AND gstr1_status != 'filed'
                ORDER BY invoice_date ASC");
            $b2cStmt->execute([$startDate, $endDate]);
            $b2cRecords = $b2cStmt->fetchAll(PDO::FETCH_ASSOC);

            // Group B2B by GSTIN
            $b2bGrouped = [];
            foreach ($b2bRecords as $r) {
                $gstinKey = $r['party_gstin'];
                if (!isset($b2bGrouped[$gstinKey])) {
                    $b2bGrouped[$gstinKey] = [
                        'gstin' => $gstinKey,
                        'name' => $r['party_name'],
                        'invoices' => [],
                    ];
                }
                $b2bGrouped[$gstinKey]['invoices'][] = [
                    'invoice_number' => $r['invoice_number'],
                    'invoice_date' => $r['invoice_date'],
                    'document_type' => $r['document_type'] ?? 'invoice',
                    'taxable_value' => (float)$r['taxable_value'],
                    'place_of_supply' => $r['place_of_supply'] ?? '09-Uttar Pradesh',
                    'reverse_charge' => (int)($r['reverse_charge'] ?? 0),
                    'rate' => (float)$r['gst_rate'],
                    'cgst' => (float)$r['cgst_amount'],
                    'sgst' => (float)$r['sgst_amount'],
                    'igst' => (float)$r['igst_amount'],
                    'cess' => (float)$r['cess_amount'],
                ];
            }

            // B2C summary (place_of_supply + rate)
            $b2cSummary = [];
            foreach ($b2cRecords as $r) {
                $key = ($r['place_of_supply'] ?? '09') . '_' . $r['gst_rate'];
                if (!isset($b2cSummary[$key])) {
                    $b2cSummary[$key] = [
                        'place_of_supply' => $r['place_of_supply'] ?? '09-Uttar Pradesh',
                        'rate' => (float)$r['gst_rate'],
                        'taxable_value' => 0,
                        'cgst' => 0, 'sgst' => 0, 'igst' => 0,
                        'count' => 0,
                    ];
                }
                $b2cSummary[$key]['taxable_value'] += (float)$r['taxable_value'];
                $b2cSummary[$key]['cgst'] += (float)$r['cgst_amount'];
                $b2cSummary[$key]['sgst'] += (float)$r['sgst_amount'];
                $b2cSummary[$key]['igst'] += (float)$r['igst_amount'];
                $b2cSummary[$key]['count']++;
            }

            // HSN Summary
            $hsnStmt = $pdo->prepare("SELECT hsn_sac_code, gst_rate,
                SUM(taxable_value) as total_taxable, SUM(cgst_amount) as total_cgst,
                SUM(sgst_amount) as total_sgst, SUM(igst_amount) as total_igst,
                COUNT(*) as qty
                FROM gst_transactions
                WHERE transaction_date BETWEEN ? AND ? AND transaction_type = 'output'
                AND gstr1_status != 'filed'
                GROUP BY hsn_sac_code, gst_rate ORDER BY total_taxable DESC");
            $hsnStmt->execute([$startDate, $endDate]);
            $hsnSummary = $hsnStmt->fetchAll(PDO::FETCH_ASSOC);

            // Totals
            $allRecords = array_merge($b2bRecords, $b2cRecords);
            $totalTaxable = array_sum(array_column($allRecords, 'taxable_value'));
            $totalCGST = array_sum(array_column($allRecords, 'cgst_amount'));
            $totalSGST = array_sum(array_column($allRecords, 'sgst_amount'));
            $totalIGST = array_sum(array_column($allRecords, 'igst_amount'));
            $totalCess = array_sum(array_column($allRecords, 'cess_amount'));

            // Build GSTN-compatible JSON structure
            $gstr1 = [
                'gstin' => $gstin,
                'fp' => $this->getReturnPeriod($month, $year),
                'gt' => $totalTaxable,
                'ctin' => '',
                'b2b' => array_values($b2bGrouped),
                'b2c' => ['details' => array_values($b2cSummary)],
                'hsn' => ['data' => $hsnSummary],
                'docdata' => [
                    'inv' => ['total' => count($allRecords), 'issued' => count($allRecords)],
                ],
                'summary' => [
                    'total_taxable' => $totalTaxable,
                    'total_cgst' => $totalCGST,
                    'total_sgst' => $totalSGST,
                    'total_igst' => $totalIGST,
                    'total_cess' => $totalCess,
                    'total_tax' => $totalCGST + $totalSGST + $totalIGST + $totalCess,
                ],
            ];

            // Save to file
            $dir = 'C:/xampp/htdocs/apsdreamhome/storage/efiling/gst';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $period = $this->getReturnPeriod($month, $year);
            $filename = "gstr1_{$gstin}_{$period}_" . date('Ymd_His') . '.json';
            $filepath = $dir . '/' . $filename;
            file_put_contents($filepath, json_encode($gstr1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Create submission
            $submissionId = $this->efiling->createSubmission([
                'submission_type' => 'gstr1',
                'reference_table' => 'gst_transactions',
                'reference_id' => $b2bRecords[0]['id'] ?? ($b2cRecords[0]['id'] ?? null),
                'financial_year' => $fy,
                'period_month' => $month,
                'period_year' => $year,
                'gstin' => $gstin,
                'filing_mode' => 'offline',
                'total_records' => count($allRecords),
                'total_amount' => $totalTaxable,
                'notes' => "GSTR-1 {$period}: " . count($allRecords) . " invoices, Tax ₹" .
                    number_format($totalCGST + $totalSGST + $totalIGST, 2),
            ]);
            $this->efiling->updateSubmissionStatus($submissionId, 'prepared', ['json_file_path' => $filepath]);

            return [
                'success' => true,
                'submission_id' => $submissionId,
                'file_path' => $filepath,
                'filename' => $filename,
                'gstr1' => $gstr1,
                'summary' => [
                    'b2b_count' => count($b2bGrouped),
                    'b2b_invoices' => count($b2bRecords),
                    'b2c_count' => count($b2cRecords),
                    'total_taxable' => $totalTaxable,
                    'total_tax' => $totalCGST + $totalSGST + $totalIGST,
                ],
            ];
        } catch (\Exception $e) {
            error_log("[GSTFilingService] generateGSTR1() exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== GSTR-3B ==========

    public function generateGSTR3B(int $month, int $year, string $fy = null): array
    {
        $pdo = $this->getPdo();
        $gstin = $this->getCompanyGSTIN();
        $fy = $fy ?: $this->efiling->getCurrentFinancialYear();

        try {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));

            // Output tax (sales)
            $outStmt = $pdo->prepare("SELECT
                SUM(taxable_value) as taxable,
                SUM(cgst_amount) as cgst,
                SUM(sgst_amount) as sgst,
                SUM(igst_amount) as igst,
                SUM(cess_amount) as cess,
                COUNT(*) as count
                FROM gst_transactions
                WHERE transaction_date BETWEEN ? AND ? AND transaction_type = 'output'
                AND gstr1_status != 'filed'");
            $outStmt->execute([$startDate, $endDate]);
            $output = $outStmt->fetch(PDO::FETCH_ASSOC);

            // Input tax credit (purchases)
            $inStmt = $pdo->prepare("SELECT
                SUM(taxable_value) as taxable,
                SUM(cgst_amount) as cgst,
                SUM(sgst_amount) as sgst,
                SUM(igst_amount) as igst,
                SUM(cess_amount) as cess,
                COUNT(*) as count
                FROM gst_transactions
                WHERE transaction_date BETWEEN ? AND ? AND transaction_type = 'input'
                AND itc_eligible = 1 AND itc_claimed = 0");
            $inStmt->execute([$startDate, $endDate]);
            $input = $inStmt->fetch(PDO::FETCH_ASSOC);

            $outTax = (float)($output['cgst'] ?? 0) + (float)($output['sgst'] ?? 0) + (float)($output['igst'] ?? 0);
            $inTax = (float)($input['cgst'] ?? 0) + (float)($input['sgst'] ?? 0) + (float)($input['igst'] ?? 0);
            $netPayable = max(0, $outTax - $inTax);

            $gstr3b = [
                'gstin' => $gstin,
                'fp' => $this->getReturnPeriod($month, $year),
                'period' => $this->getReturnPeriod($month, $year),
                'output' => [
                    'taxable_value' => (float)($output['taxable'] ?? 0),
                    'cgst' => (float)($output['cgst'] ?? 0),
                    'sgst' => (float)($output['sgst'] ?? 0),
                    'igst' => (float)($output['igst'] ?? 0),
                    'cess' => (float)($output['cess'] ?? 0),
                    'total' => $outTax,
                    'count' => (int)($output['count'] ?? 0),
                ],
                'input' => [
                    'taxable_value' => (float)($input['taxable'] ?? 0),
                    'cgst' => (float)($input['cgst'] ?? 0),
                    'sgst' => (float)($input['sgst'] ?? 0),
                    'igst' => (float)($input['igst'] ?? 0),
                    'cess' => (float)($input['cess'] ?? 0),
                    'total' => $inTax,
                    'count' => (int)($input['count'] ?? 0),
                ],
                'net_payable' => $netPayable,
                'interest' => 0,
                'late_fee' => 0,
                'total_payable' => $netPayable,
            ];

            // Save JSON
            $dir = 'C:/xampp/htdocs/apsdreamhome/storage/efiling/gst';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $period = $this->getReturnPeriod($month, $year);
            $filename = "gstr3b_{$gstin}_{$period}_" . date('Ymd_His') . '.json';
            $filepath = $dir . '/' . $filename;
            file_put_contents($filepath, json_encode($gstr3b, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Create submission
            $submissionId = $this->efiling->createSubmission([
                'submission_type' => 'gstr3b',
                'reference_table' => 'gst_transactions',
                'financial_year' => $fy,
                'period_month' => $month,
                'period_year' => $year,
                'gstin' => $gstin,
                'filing_mode' => 'offline',
                'total_records' => (int)($output['count'] ?? 0) + (int)($input['count'] ?? 0),
                'total_amount' => $netPayable,
                'notes' => "GSTR-3B {$period}: Output ₹" . number_format($outTax, 2) .
                    " - ITC ₹" . number_format($inTax, 2) .
                    " = Net ₹" . number_format($netPayable, 2),
            ]);
            $this->efiling->updateSubmissionStatus($submissionId, 'prepared', ['json_file_path' => $filepath]);

            return [
                'success' => true,
                'submission_id' => $submissionId,
                'file_path' => $filepath,
                'filename' => $filename,
                'gstr3b' => $gstr3b,
                'summary' => [
                    'output_tax' => $outTax,
                    'input_tax' => $inTax,
                    'net_payable' => $netPayable,
                ],
            ];
        } catch (\Exception $e) {
            error_log("[GSTFilingService] generateGSTR3B() exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== GSTR-9 Annual ==========

    public function generateGSTR9(string $fy): array
    {
        try {
            $startYear = (int)substr($fy, 0, 4);
            $months = [];
            for ($i = 0; $i < 12; $i++) {
                $m = (($i + 3) % 12) + 1;
                $y = ($i < 9) ? $startYear : $startYear + 1;
                $months[] = ['month' => $m, 'year' => $y];
            }

            $annual = [
                'fy' => $fy,
                'gstin' => $this->getCompanyGSTIN(),
                'monthly_summary' => [],
                'total' => ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'tax' => 0],
            ];

            $pdo = $this->getPdo();
            foreach ($months as $period) {
                $startDate = sprintf('%04d-%02d-01', $period['year'], $period['month']);
                $endDate = date('Y-m-t', strtotime($startDate));
                $stmt = $pdo->prepare("SELECT
                    SUM(taxable_value) as taxable, SUM(cgst_amount) as cgst,
                    SUM(sgst_amount) as sgst, SUM(igst_amount) as igst,
                    COUNT(*) as count
                    FROM gst_transactions WHERE transaction_date BETWEEN ? AND ?");
                $stmt->execute([$startDate, $endDate]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $annual['monthly_summary'][] = ['period' => $period['month'] . '-' . $period['year']] + ($row ?: []);
                $annual['total']['taxable'] += (float)($row['taxable'] ?? 0);
                $annual['total']['cgst'] += (float)($row['cgst'] ?? 0);
                $annual['total']['sgst'] += (float)($row['sgst'] ?? 0);
                $annual['total']['igst'] += (float)($row['igst'] ?? 0);
            }
            $annual['total']['tax'] = $annual['total']['cgst'] + $annual['total']['sgst'] + $annual['total']['igst'];

            return ['success' => true, 'gstr9' => $annual];
        } catch (\Exception $e) {
            error_log("[GSTFilingService] generateGSTR9() exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== GST Summary ==========

    public function getGSTSummary(string $fy, string $month = null): array
    {
        try {
            $startYear = (int)substr($fy, 0, 4);
            $periods = $this->efiling->getFinancialYearPeriods($fy);

            $summary = [];
            foreach ($periods as $p) {
                if ($month && $p['month'] != (int)$month) continue;

                $startDate = sprintf('%04d-%02d-01', $p['year'], $p['month']);
                $endDate = date('Y-m-t', strtotime($startDate));
                $stmt = $this->getPdo()->prepare("SELECT
                    transaction_type,
                    SUM(taxable_value) as taxable,
                    SUM(cgst_amount) as cgst,
                    SUM(sgst_amount) as sgst,
                    SUM(igst_amount) as igst,
                    SUM(total_tax) as tax,
                    COUNT(*) as count
                    FROM gst_transactions WHERE transaction_date BETWEEN ? AND ?
                    GROUP BY transaction_type");
                $stmt->execute([$startDate, $endDate]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $output = $input = ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'tax' => 0, 'count' => 0];
                foreach ($rows as $r) {
                    $type = $r['transaction_type'];
                    $target = ($type === 'output') ? $output : $input;
                    $target['taxable'] = (float)$r['taxable'];
                    $target['cgst'] = (float)$r['cgst'];
                    $target['sgst'] = (float)$r['sgst'];
                    $target['igst'] = (float)$r['igst'];
                    $target['tax'] = (float)$r['tax'];
                    $target['count'] = (int)$r['count'];
                    if ($type === 'output') $output = $target; else $input = $target;
                }

                $outTax = $output['cgst'] + $output['sgst'] + $output['igst'];
                $inTax = $input['cgst'] + $input['sgst'] + $input['igst'];

                $summary[] = [
                    'period' => $p['label'],
                    'month' => $p['month'],
                    'quarter' => $p['quarter'],
                    'output' => $output,
                    'input' => $input,
                    'net_itc' => $inTax,
                    'net_payable' => max(0, $outTax - $inTax),
                    'out_tax' => $outTax,
                    'in_tax' => $inTax,
                ];
            }

            return ['success' => true, 'summary' => $summary];
        } catch (\Exception $e) {
            error_log("[GSTFilingService] getGSTSummary() exception: " . $e->getMessage());
            return ['success' => false, 'summary' => []];
        }
    }

    // ========== Helpers ==========

    public function getReturnPeriod(int $month, int $year): string
    {
        // GSTN format: 042025 = Apr 2025
        return sprintf('%02d%04d', $month, $year);
    }

    public function getHSNCodes(): array
    {
        return [
            '996412' => ['desc' => 'Construction of residential complex', 'gst' => 12, 'hsn_type' => 'service'],
            '997211' => ['desc' => 'Real estate services', 'gst' => 18, 'hsn_type' => 'service'],
            '999111' => ['desc' => 'General construction services', 'gst' => 18, 'hsn_type' => 'service'],
            '4403'   => ['desc' => 'Timber wood', 'gst' => 18, 'hsn_type' => 'goods'],
            '4407'   => ['desc' => 'Sawn wood', 'gst' => 18, 'hsn_type' => 'goods'],
            '7016'   => ['desc' => 'Glass blocks/tiles', 'gst' => 28, 'hsn_type' => 'goods'],
            '6810'   => ['desc' => 'Building blocks/tiles', 'gst' => 28, 'hsn_type' => 'goods'],
            '2523'   => ['desc' => 'Cement', 'gst' => 28, 'hsn_type' => 'goods'],
            '7214'   => ['desc' => 'Steel bars/rods', 'gst' => 18, 'hsn_type' => 'goods'],
            '3917'   => ['desc' => 'PVC pipes', 'gst' => 28, 'hsn_type' => 'goods'],
        ];
    }

    public function markTransactionsFiled(int $month, int $year): int
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        try {
            $stmt = $this->getPdo()->prepare("UPDATE gst_transactions
                SET gstr1_status = 'filed'
                WHERE transaction_date BETWEEN ? AND ? AND gstr1_status != 'filed'");
            $stmt->execute([$startDate, $endDate]);
            return $stmt->rowCount();
        } catch (\Exception $e) {
            error_log("[GSTFilingService] markTransactionsFiled() exception: " . $e->getMessage());
            return 0;
        }
    }
}
