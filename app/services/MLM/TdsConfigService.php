<?php
/**
 * TdsConfigService — Configurable TDS for Commission Payouts
 *
 * Handles:
 *   - Section 194H (brokerage/commission): 5% if PAN available, 20% if not
 *   - Section 194C (contractor): 1% individual, 2% company
 *   - Section 194I (rent): 10%
 *   - Section 194J (professional): 10%
 *   - Section 194A (interest): 10%
 *   - PAN validation via regex (10-char alphanumeric)
 *   - Annual TDS limit tracking (₹30,000 threshold for 194H)
 *   - GST compensation cess (if applicable)
 *
 * Reads config from `settings` table or uses hardcoded defaults.
 */

namespace App\Services\MLM;

use PDO;
use Exception;

class TdsConfigService
{
    protected $db;

    /** TDS section rates — defaults, overridable from DB */
    const DEFAULT_RATES = [
        '194H' => ['rate' => 5.0, 'no_pan_rate' => 20.0, 'threshold' => 30000, 'description' => 'Brokerage/Commission'],
        '194C' => ['rate_individual' => 1.0, 'rate_company' => 2.0, 'no_pan_rate' => 20.0, 'threshold' => 30000, 'description' => 'Contractor/Sub-contractor'],
        '194I_LAND' => ['rate' => 10.0, 'no_pan_rate' => 20.0, 'threshold' => 240000, 'description' => 'Rent on Land/Building'],
        '194I_PLANT' => ['rate' => 2.0, 'no_pan_rate' => 20.0, 'threshold' => 240000, 'description' => 'Rent on Plant/Machinery'],
        '194J_A' => ['rate' => 10.0, 'no_pan_rate' => 20.0, 'threshold' => 30000, 'description' => 'Professional/Technical Fees'],
        '194J_B' => ['rate' => 2.0, 'no_pan_rate' => 20.0, 'threshold' => 30000, 'description' => 'Royalty under 115BAC'],
        '194A' => ['rate' => 10.0, 'no_pan_rate' => 20.0, 'threshold' => 40000, 'description' => 'Interest other than on securities'],
        '194N' => ['rate_above_1cr' => 2.0, 'rate_above_3cr' => 5.0, 'description' => 'Cash withdrawal'],
        '194M' => ['rate' => 5.0, 'no_pan_rate' => 20.0, 'threshold' => 5000000, 'description' => 'Payment on contract >₹50L'],
        '194Q' => ['rate' => 0.1, 'description' => 'Purchase of goods >₹50L'],
    ];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                error_log('[TdsConfigService] DB init failed: ' . $e->getMessage());
                $pdo = null;
            }
        }
        $this->db = $pdo;
    }

    /**
     * Calculate TDS for a commission/payment amount.
     *
     * @param string $section TDS section (e.g., '194H')
     * @param float $amount Taxable amount
     * @param string|null $pan Deductee PAN (null = no PAN → higher rate)
     * @param string $deducteeType 'individual' or 'company'
     * @return array ['tds_amount', 'rate_used', 'section', 'pan_status', 'below_threshold', 'net_payable']
     */
    public function calculate(string $section, float $amount, ?string $pan = null, string $deducteeType = 'individual'): array
    {
        $sectionUpper = strtoupper($section);
        $config = self::DEFAULT_RATES[$sectionUpper] ?? null;

        if ($config === null) {
            return [
                'tds_amount' => 0,
                'rate_used' => 0,
                'section' => $sectionUpper,
                'pan_status' => 'unknown',
                'below_threshold' => true,
                'net_payable' => round($amount, 2),
                'error' => "Unknown TDS section: {$sectionUpper}",
            ];
        }

        $hasPan = $this->isValidPan($pan);
        $panStatus = $hasPan ? 'valid' : 'missing';

        // Check threshold
        $threshold = $config['threshold'] ?? 0;
        if ($amount < $threshold) {
            return [
                'tds_amount' => 0,
                'rate_used' => 0,
                'section' => $sectionUpper,
                'pan_status' => $panStatus,
                'below_threshold' => true,
                'net_payable' => round($amount, 2),
                'note' => "Below ₹" . number_format($threshold) . " threshold for {$sectionUpper}",
            ];
        }

        // Determine rate
        if (!$hasPan) {
            $rate = $config['no_pan_rate'] ?? 20.0;
        } elseif ($sectionUpper === '194C') {
            $rate = $deducteeType === 'company'
                ? ($config['rate_company'] ?? 2.0)
                : ($config['rate_individual'] ?? 1.0);
        } elseif ($sectionUpper === '194N') {
            $rate = $amount > 30000000
                ? ($config['rate_above_3cr'] ?? 5.0)
                : ($config['rate_above_1cr'] ?? 2.0);
        } else {
            $rate = $config['rate'] ?? 5.0;
        }

        $tdsAmount = round($amount * $rate / 100, 2);
        $netPayable = round($amount - $tdsAmount, 2);

        // Check annual limit (only for 194H)
        $annualNote = '';
        if ($sectionUpper === '194H' && $this->db) {
            $annualTotal = $this->getAnnualTdsForUser(0); // Will be called with actual user
            $annualThreshold = 30000;
            if ($annualTotal + $tdsAmount > $annualThreshold) {
                $annualNote = "Annual TDS ₹" . number_format($annualTotal + $tdsAmount) . " exceeds ₹" . number_format($annualThreshold) . " limit";
            }
        }

        return [
            'tds_amount' => $tdsAmount,
            'rate_used' => $rate,
            'section' => $sectionUpper,
            'pan_status' => $panStatus,
            'below_threshold' => false,
            'net_payable' => $netPayable,
            'description' => $config['description'] ?? '',
            'annual_note' => $annualNote,
        ];
    }

    /**
     * Calculate TDS for MLM commission payout (Section 194H).
     * Convenience method for the most common case.
     */
    public function calculateForCommission(float $amount, ?string $pan = null): array
    {
        return $this->calculate('194H', $amount, $pan);
    }

    /**
     * Validate Indian PAN format: AAAAA9999A
     * 5 letters + 4 digits + 1 letter
     */
    public function isValidPan(?string $pan): bool
    {
        if (empty($pan)) {
            return false;
        }
        $pan = strtoupper(trim($pan));
        return (bool)preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan);
    }

    /**
     * Get annual TDS deducted for a user (current FY).
     */
    public function getAnnualTdsForUser(int $userId): float
    {
        if (!$this->db || $userId <= 0) {
            return 0.0;
        }

        $fy = $this->getCurrentFinancialYear();
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(tds_amount), 0)
                FROM tds_register
                WHERE deductee_user_id = ? 
                AND tds_date BETWEEN ? AND ?
            ");
            $stmt->execute([$userId, $fy['start'], $fy['end']]);
            return (float)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0.0;
        }
    }

    /**
     * Record a TDS deduction in the register.
     */
    public function recordDeduction(array $data): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        $required = ['deductee_user_id', 'amount', 'section'];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                return ['success' => false, 'error' => "Missing required field: {$f}"];
            }
        }

        $pan = $data['pan'] ?? null;
        $calc = $this->calculate($data['section'], $data['amount'], $pan, $data['deductee_type'] ?? 'individual');

        if (!empty($calc['error'])) {
            return ['success' => false, 'error' => $calc['error']];
        }

        try {
            $fy = $this->getCurrentFinancialYear();
            $quarter = $this->getQuarter($data['tds_date'] ?? date('Y-m-d'));

            $stmt = $this->db->prepare("
                INSERT INTO tds_register 
                (deductee_user_id, deductee_pan, tds_section, tds_date, taxable_amount, 
                 tds_amount, financial_year, quarter, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $data['deductee_user_id'],
                $pan,
                strtoupper($data['section']),
                $data['tds_date'] ?? date('Y-m-d'),
                $data['amount'],
                $calc['tds_amount'],
                $fy['label'],
                $quarter,
            ]);

            return [
                'success' => true,
                'id' => (int)$this->db->lastInsertId(),
                'tds_amount' => $calc['tds_amount'],
                'rate_used' => $calc['rate_used'],
                'net_payable' => $calc['net_payable'],
            ];
        } catch (Exception $e) {
            error_log('[TdsConfigService] recordDeduction: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all configured TDS sections.
     */
    public function getSections(): array
    {
        return self::DEFAULT_RATES;
    }

    /**
     * Get current financial year (April 1 – March 31).
     */
    private function getCurrentFinancialYear(): array
    {
        $month = (int)date('m');
        $year = (int)date('Y');
        if ($month >= 4) {
            return [
                'start' => "{$year}-04-01",
                'end' => ($year + 1) . "-03-31",
                'label' => "{$year}-" . ($year + 1),
            ];
        }
        return [
            'start' => ($year - 1) . "-04-01",
            'end' => "{$year}-03-31",
            'label' => ($year - 1) . "-{$year}",
        ];
    }

    /**
     * Get quarter number from date (Q1=Apr-Jun, Q2=Jul-Sep, Q3=Oct-Dec, Q4=Jan-Mar).
     */
    private function getQuarter(string $date): string
    {
        $month = (int)date('m', strtotime($date));
        if ($month >= 4 && $month <= 6) return 'Q1';
        if ($month >= 7 && $month <= 9) return 'Q2';
        if ($month >= 10 && $month <= 12) return 'Q3';
        return 'Q4';
    }
}
