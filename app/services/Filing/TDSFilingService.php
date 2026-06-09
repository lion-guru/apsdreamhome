<?php
namespace App\Services\Filing;

use PDO;

/**
 * TDSFilingService — TDS-specific e-filing operations
 * Form 26Q generation, Challan 281, Form 16A, TRACES API client
 */
class TDSFilingService
{
    private $db;
    private $efiling;
    private $credentials;

    public function __construct($pdo = null)
    {
        $this->db = $pdo;
        $this->efiling = new EFilingService($pdo);
    }

    private function getPdo(): PDO
    {
        if ($this->db instanceof PDO) return $this->db;
        $config = require 'C:/xampp/htdocs/apsdreamhome/config/database.php';
        $this->db = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
            $config['username'], $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $this->db;
    }

    private function getCompanyTAN(): ?string
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT credential_value FROM company_credentials
                WHERE credential_type = 'tan' AND status = 'active' AND is_primary = 1 LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['credential_value'] ?? null;
        } catch (\Exception $e) { error_log("[TDSFilingService] getCompanyTAN: " . $e->getMessage()); return null; }
    }

    private function getCompanyPAN(): ?string
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT credential_value FROM company_credentials
                WHERE credential_type = 'pan' AND status = 'active' AND is_primary = 1 LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['credential_value'] ?? null;
        } catch (\Exception $e) { error_log("[TDSFilingService] getCompanyPAN: " . $e->getMessage()); return null; }
    }

    // ========== Form 26Q Generation ==========

    public function generateForm26Q(string $fy, string $quarter): array
    {
        $pdo = $this->getPdo();
        $tan = $this->getCompanyTAN();

        try {
            // Get all TDS deductions for the quarter
            $stmt = $pdo->prepare("SELECT * FROM tds_register
                WHERE financial_year = ? AND quarter = ?
                ORDER BY transaction_date ASC, tds_section ASC");
            $stmt->execute([$fy, $quarter]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($records)) {
                return ['success' => false, 'error' => 'No TDS records found for ' . $fy . ' ' . $quarter];
            }

            // Group by section
            $bySection = [];
            foreach ($records as $r) {
                $section = $r['tds_section'];
                if (!isset($bySection[$section])) {
                    $bySection[$section] = [
                        'section' => $section,
                        'section_name' => $this->getSectionName($section),
                        'records' => [],
                        'total_gross' => 0,
                        'total_tds' => 0,
                        'total_surcharge' => 0,
                        'total_cess' => 0,
                        'count' => 0,
                    ];
                }
                $bySection[$section]['records'][] = $r;
                $bySection[$section]['total_gross'] += (float)$r['gross_amount'];
                $bySection[$section]['total_tds'] += (float)$r['tds_amount'];
                $bySection[$section]['total_surcharge'] += (float)($r['surcharge'] ?? 0);
                $bySection[$section]['total_cess'] += (float)($r['cess'] ?? 0);
                $bySection[$section]['count']++;
            }

            // Build Form 26Q structure (TRACES format)
            $form26q = [
                'file_version' => '1.0',
                'tan' => $tan,
                'assessment_year' => $this->getAssessmentYear($fy),
                'financial_year' => $fy,
                'quarter' => $quarter,
                'form_number' => '26Q',
                'filing_type' => 'original',
                'deductor_name' => $records[0]['deductor_name'] ?? '',
                'deductor_pan' => $records[0]['deductor_pan'] ?? '',
                'deductor_state_code' => $records[0]['deductor_state_code'] ?? '09',
                'deductor_email' => '',
                'deductor_phone' => '',
                'total_deductees' => count($records),
                'total_amount' => array_sum(array_column($records, 'gross_amount')),
                'total_tds' => array_sum(array_column($records, 'tds_amount')),
                'total_surcharge' => array_sum(array_column($records, 'surcharge')),
                'total_cess' => array_sum(array_column($records, 'cess')),
                'sections' => array_values($bySection),
            ];

            // Save to file
            $dir = 'C:/xampp/htdocs/apsdreamhome/storage/efiling/tds';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = "form_26q_{$fy}_{$quarter}_" . date('Ymd_His') . '.json';
            $filepath = $dir . '/' . $filename;
            file_put_contents($filepath, json_encode($form26q, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Create submission record
            $submissionId = $this->efiling->createSubmission([
                'submission_type' => 'tds_return',
                'reference_table' => 'tds_register',
                'reference_id' => $records[0]['id'] ?? null,
                'financial_year' => $fy,
                'quarter' => $quarter,
                'tan' => $tan,
                'pan' => $this->getCompanyPAN(),
                'filing_mode' => 'offline',
                'total_records' => count($records),
                'total_amount' => array_sum(array_column($records, 'gross_amount')),
                'notes' => "Form 26Q {$quarter} {$fy}: " . count($records) . " records, TDS total ₹" .
                    number_format(array_sum(array_column($records, 'tds_amount')), 2),
            ]);
            $this->efiling->updateSubmissionStatus($submissionId, 'prepared', ['json_file_path' => $filepath]);

            return [
                'success' => true,
                'submission_id' => $submissionId,
                'file_path' => $filepath,
                'filename' => $filename,
                'form_26q' => $form26q,
                'summary' => [
                    'total_records' => count($records),
                    'total_gross' => array_sum(array_column($records, 'gross_amount')),
                    'total_tds' => array_sum(array_column($records, 'tds_amount')),
                    'sections' => count($bySection),
                ],
            ];
        } catch (\Exception $e) {
            error_log("[TDSFilingService] generateForm26Q() exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== Challan 281 ==========

    public function generateChallan281(array $data): int
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("INSERT INTO tds_challans
            (challan_number, bsr_code, tan, assessment_year, financial_year, quarter,
             deposit_date, major_head, minor_head, tds_section, total_amount,
             interest_amount, penalty_amount, surcharge_amount, cess_amount,
             total_with_charges, challan_status, deposited_via, bank_name, remarks)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $totalWithCharges = (float)$data['total_amount']
            + (float)($data['interest_amount'] ?? 0)
            + (float)($data['penalty_amount'] ?? 0)
            + (float)($data['surcharge_amount'] ?? 0)
            + (float)($data['cess_amount'] ?? 0);

        $stmt->execute([
            $data['challan_number'] ?? null,
            $data['bsr_code'] ?? null,
            $data['tan'],
            $data['assessment_year'],
            $data['financial_year'],
            $data['quarter'],
            $data['deposit_date'] ?? date('Y-m-d'),
            $data['major_head'] ?? '9482',
            $data['minor_head'] ?? '800',
            $data['tds_section'] ?? null,
            $data['total_amount'],
            $data['interest_amount'] ?? 0,
            $data['penalty_amount'] ?? 0,
            $data['surcharge_amount'] ?? 0,
            $data['cess_amount'] ?? 0,
            $totalWithCharges,
            'prepared',
            $data['deposited_via'] ?? 'net_banking',
            $data['bank_name'] ?? null,
            $data['remarks'] ?? null,
        ]);

        $challanId = (int)$pdo->lastInsertId();

        // Also create submission record
        $this->efiling->createSubmission([
            'submission_type' => 'tds_challan',
            'reference_table' => 'tds_challans',
            'reference_id' => $challanId,
            'financial_year' => $data['financial_year'],
            'quarter' => $data['quarter'],
            'tan' => $data['tan'],
            'filing_mode' => 'offline',
            'total_records' => 1,
            'total_amount' => $totalWithCharges,
            'notes' => "Challan 281: {$data['tds_section']} ₹" . number_format($totalWithCharges, 2),
        ]);

        return $challanId;
    }

    public function listChallans(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['financial_year'])) { $where[] = "financial_year = ?"; $params[] = $filters['financial_year']; }
        if (!empty($filters['quarter'])) { $where[] = "quarter = ?"; $params[] = $filters['quarter']; }
        if (!empty($filters['challan_status'])) { $where[] = "challan_status = ?"; $params[] = $filters['challan_status']; }

        try {
            $sql = "SELECT * FROM tds_challans WHERE " . implode(' AND ', $where) . " ORDER BY deposit_date DESC";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[TDSFilingService] listChallans() exception: " . $e->getMessage());
            return [];
        }
    }

    public function updateChallanStatus(int $challanId, string $status, array $extra = []): bool
    {
        try {
            $sets = ['challan_status = ?'];
            $params = [$status];
            if (!empty($extra['govt_challan_id'])) { $sets[] = "govt_challan_id = ?"; $params[] = $extra['govt_challan_id']; }
            if (!empty($extra['receipt_number'])) { $sets[] = "receipt_number = ?"; $params[] = $extra['receipt_number']; }
            if (!empty($extra['bsr_code'])) { $sets[] = "bsr_code = ?"; $params[] = $extra['bsr_code']; }
            $params[] = $challanId;
            $stmt = $this->getPdo()->prepare("UPDATE tds_challans SET " . implode(', ', $sets) . " WHERE id = ?");
            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log("[TDSFilingService] updateChallanStatus() exception: " . $e->getMessage());
            return false;
        }
    }

    // ========== Form 16A ==========

    public function generateForm16A(int $certificateId): array
    {
        $pdo = $this->getPdo();
        try {
            $stmt = $pdo->prepare("SELECT * FROM tds_certificates_issued WHERE id = ?");
            $stmt->execute([$certificateId]);
            $cert = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$cert) return ['success' => false, 'error' => 'Certificate not found'];

            // Get TDS records for this deductee + quarter
            $tdsStmt = $pdo->prepare("SELECT * FROM tds_register
                WHERE deductee_pan = ? AND financial_year = ? AND quarter = ?
                ORDER BY transaction_date ASC");
            $tdsStmt->execute([$cert['deductee_pan'], $cert['financial_year'], $cert['quarter']]);
            $tdsRecords = $tdsStmt->fetchAll(PDO::FETCH_ASSOC);

            $form16a = [
                'form_number' => '16A',
                'certificate_number' => $cert['certificate_number'],
                'financial_year' => $cert['financial_year'],
                'quarter' => $cert['quarter'],
                'deductor' => [
                    'name' => $tdsRecords[0]['deductor_name'] ?? '',
                    'pan' => $tdsRecords[0]['deductor_pan'] ?? '',
                    'tan' => $this->getCompanyTAN(),
                ],
                'deductee' => [
                    'name' => $cert['deductee_name'],
                    'pan' => $cert['deductee_pan'],
                ],
                'total_tds' => (float)$cert['total_tds'],
                'total_gross' => array_sum(array_column($tdsRecords, 'gross_amount')),
                'records' => array_map(function($r) {
                    return [
                        'date' => $r['transaction_date'],
                        'section' => $r['tds_section'],
                        'gross' => (float)$r['gross_amount'],
                        'rate' => (float)$r['tds_rate'],
                        'tds' => (float)$r['tds_amount'],
                    ];
                }, $tdsRecords),
            ];

            // Save HTML for PDF generation
            $dir = 'C:/xampp/htdocs/apsdreamhome/storage/efiling/form16a';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = "form_16a_{$cert['deductee_pan']}_{$cert['financial_year']}_{$cert['quarter']}.json";
            $filepath = $dir . '/' . $filename;
            file_put_contents($filepath, json_encode($form16a, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Update certificate record
            $pdo->prepare("UPDATE tds_certificates_issued SET form_16a_pdf_path = ? WHERE id = ?")
                ->execute([$filepath, $certificateId]);

            return ['success' => true, 'form_16a' => $form16a, 'file_path' => $filepath];
        } catch (\Exception $e) {
            error_log("[TDSFilingService] generateForm16A() exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== TDS Summary ==========

    public function getTDSSummary(string $fy, string $quarter = null): array
    {
        try {
            $where = "financial_year = ?";
            $params = [$fy];
            if ($quarter) { $where .= " AND quarter = ?"; $params[] = $quarter; }

            $stmt = $this->getPdo()->prepare("SELECT
                tds_section,
                COUNT(*) as count,
                SUM(gross_amount) as total_gross,
                SUM(tds_amount) as total_tds,
                SUM(surcharge) as total_surcharge,
                SUM(cess) as total_cess,
                SUM(total_tds) as total_with_charges,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status='pending' THEN tds_amount ELSE 0 END) as pending_tds,
                SUM(CASE WHEN status='deposited' THEN 1 ELSE 0 END) as deposited_count,
                SUM(CASE WHEN status='filed' THEN 1 ELSE 0 END) as filed_count,
                SUM(CASE WHEN status='verified' THEN 1 ELSE 0 END) as verified_count
                FROM tds_register WHERE $where GROUP BY tds_section ORDER BY tds_section");
            $stmt->execute($params);
            $bySection = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Overall totals
            $totals = [
                'total_records' => array_sum(array_column($bySection, 'count')),
                'total_gross' => array_sum(array_column($bySection, 'total_gross')),
                'total_tds' => array_sum(array_column($bySection, 'total_tds')),
                'total_surcharge' => array_sum(array_column($bySection, 'total_surcharge')),
                'total_cess' => array_sum(array_column($bySection, 'total_cess')),
                'pending_count' => array_sum(array_column($bySection, 'pending_count')),
                'pending_tds' => array_sum(array_column($bySection, 'pending_tds')),
            ];

            return ['by_section' => $bySection, 'totals' => $totals];
        } catch (\Exception $e) {
            error_log("[TDSFilingService] getTDSSummary() exception: " . $e->getMessage());
            return ['by_section' => [], 'totals' => []];
        }
    }

    // ========== Helpers ==========

    public function getAssessmentYear(string $fy): string
    {
        // FY 2025-26 → AY 2026-27
        $startYear = (int)substr($fy, 0, 4);
        return ($startYear + 1) . '-' . substr($startYear + 2, -2);
    }

    public function getSectionName(string $section): string
    {
        $names = [
            '192' => 'Salary',
            '194C' => 'Contractor',
            '194H' => 'Commission/Brokerage',
            '194I' => 'Rent - Plant/Machinery',
            '194IA' => 'Immovable Property (50L+)',
            '194IB' => 'Rent - Individual/HUF',
            '194J' => 'Professional/Technical Fees',
            '194LA' => 'Compulsory Acquisition',
            '194M' => 'Contract/Commission >20L',
            '194N' => 'Cash Withdrawal >1Cr',
            '194O' => 'E-commerce Participant',
            '194P' => 'Senior Citizen Exemption',
            '194Q' => 'Purchase of Goods >50L',
            '194R' => 'Benefits/Perquisites >20L',
            '194S' => 'Virtual Digital Assets',
            '195' => 'Payment to NRI',
            '196' => 'Income from Units',
            '197' => 'Lower Deduction Certificate',
            '194BA' => 'Online Gaming',
        ];
        return $names[$section] ?? "Section {$section}";
    }

    public function getTDSRates(): array
    {
        return [
            '192'  => ['rate' => 'slab', 'desc' => 'Income Tax Slabs (no fixed %)'], // slab-based
            '194C' => ['rate_indiv' => 1, 'rate_co' => 2, 'desc' => 'Contractor'],
            '194H' => ['rate' => 5, 'desc' => 'Commission/Brokerage'],
            '194I' => ['rate' => 2, 'desc' => 'Rent - Plant/Machinery'],
            '194IA'=> ['rate' => 1, 'desc' => 'Immovable Property (≥50L)'],
            '194IB'=> ['rate' => 5, 'desc' => 'Rent by Individual (50K+/month)'],
            '194J' => ['rate' => 10, 'desc' => 'Professional/Technical Fees'],
            '194LA'=> ['rate' => 10, 'desc' => 'Compulsory Acquisition'],
            '194M' => ['rate' => 5, 'desc' => 'Contract/Commission >20L'],
            '194N' => ['rate_1cr' => 2, 'rate_3cr' => 5, 'desc' => 'Cash Withdrawal'],
            '194O' => ['rate' => 1, 'desc' => 'E-commerce (>5L/yr)'],
            '194Q' => ['rate' => 0.1, 'desc' => 'Purchase of Goods >50L'],
            '194R' => ['rate' => 10, 'desc' => 'Benefits/Perquisites >20L'],
            '194S' => ['rate' => 1, 'desc' => 'Virtual Digital Assets'],
            '195'  => ['rate' => 'varies', 'desc' => 'Payment to NRI (DTAA rates)'],
        ];
    }
}
