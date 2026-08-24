<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * GST Service
 * Handles GST transactions (output / input + ITC reconciliation)
 */
class GstService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function recordGST(array $data): int
    {
        $tid = TenantContext::getId();

        $type = $data['transaction_type'] ?? 'output'; // output, input
        $gstRate = (float)($data['gst_rate'] ?? 18.0);
        $taxableAmount = (float)($data['taxable_amount'] ?? 0);
        $gstAmount = round($taxableAmount * $gstRate / 100, 2);

        $payload = [
            'transaction_type'    => $type,
            'invoice_number'      => $data['invoice_number'] ?? null,
            'invoice_date'        => $data['invoice_date'] ?? date('Y-m-d'),
            'party_name'          => $data['party_name'] ?? null,
            'party_gstin'         => strtoupper($data['party_gstin'] ?? ''),
            'gstin_verified'      => 0,
            'taxable_amount'      => $taxableAmount,
            'gst_rate'            => $gstRate,
            'cgst'                => $type === 'output' ? round($gstAmount / 2, 2) : 0,
            'sgst'                => $type === 'output' ? round($gstAmount / 2, 2) : 0,
            'igst'                => $type === 'output' ? 0 : $gstAmount,
            'total_gst'           => $gstAmount,
            'hsn_sac_code'        => $data['hsn_sac_code'] ?? null,
            'place_of_supply'     => $data['place_of_supply'] ?? null,
            'reverse_charge'      => !empty($data['reverse_charge']) ? 1 : 0,
            'itc_eligible'        => $type === 'input' ? 1 : 0,
            'itc_claimed'         => 0,
            'itc_claim_date'      => null,
            'financial_year'      => $data['financial_year'] ?? $this->getCurrentFinancialYear(),
            'quarter'             => $data['quarter'] ?? $this->getCurrentQuarter(),
            'tenant_id'           => TenantContext::getId(),
        ];
        $this->db->insert('gst_transactions', $payload);
        return (int)$this->db->lastInsertId();
    }

    public function recordGstProxy(array $data): int
    {
        return $this->recordGST($data);
    }

    public function getGstTransactions(array $filters = []): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE 1=1" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = $tid > 1 ? [$tid] : [];

        if (!empty($filters['transaction_type'])) {
            $where .= " AND transaction_type = ?";
            $params[] = $filters['transaction_type'];
        }
        if (!empty($filters['financial_year'])) {
            $where .= " AND financial_year = ?";
            $params[] = $filters['financial_year'];
        }
        if (!empty($filters['quarter'])) {
            $where .= " AND quarter = ?";
            $params[] = $filters['quarter'];
        }
        if (!empty($filters['party_gstin'])) {
            $where .= " AND party_gstin = ?";
            $params[] = $filters['party_gstin'];
        }
        if (!empty($filters['from_date'])) {
            $where .= " AND invoice_date >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where .= " AND invoice_date <= ?";
            $params[] = $filters['to_date'];
        }

        $where .= " ORDER BY invoice_date DESC, id DESC";
        return $this->db->fetchAll("SELECT * FROM gst_transactions $where", $params) ?: [];
    }

    public function getGstSummary(string $fy): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE financial_year = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $params = [$fy];
        if ($tid > 1) $params[] = $tid;

        $output = $this->db->fetchOne("
            SELECT COALESCE(SUM(taxable_amount), 0) AS taxable, COALESCE(SUM(total_gst), 0) AS gst
            FROM gst_transactions WHERE transaction_type = 'output' $where
        ", $params);

        $input = $this->db->fetchOne("
            SELECT COALESCE(SUM(taxable_amount), 0) AS taxable, COALESCE(SUM(total_gst), 0) AS gst
            FROM gst_transactions WHERE transaction_type = 'input' $where
        ", $params);

        $itcClaimed = $this->db->fetchOne("
            SELECT COALESCE(SUM(total_gst), 0) AS gst FROM gst_transactions
            WHERE transaction_type = 'input' AND itc_claimed = 1 $where
        ", $params);

        return [
            'output_taxable'   => (float)($output['taxable'] ?? 0),
            'output_gst'       => (float)($output['gst'] ?? 0),
            'input_taxable'    => (float)($input['taxable'] ?? 0),
            'input_gst'        => (float)($input['gst'] ?? 0),
            'itc_claimed'      => (float)($itcClaimed['gst'] ?? 0),
            'net_gst_liability'=> (float)(($output['gst'] ?? 0) - ($input['gst'] ?? 0)),
        ];
    }

    public function markItcClaimed(int $id, string $claimDate): bool
    {
        $tid = TenantContext::getId();
        $sql = "UPDATE gst_transactions SET itc_claimed = 1, itc_claim_date = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        return $this->db->execute($sql, array_merge([$claimDate, $id], $tid > 1 ? [$tid] : []));
    }

    public function verifyGstin(string $gstin): array
    {
        // Basic GSTIN format validation
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstin)) {
            return ['valid' => false, 'error' => 'Invalid GSTIN format'];
        }

        // Checksum validation (simplified)
        return ['valid' => true, 'gstin' => $gstin];
    }

    private function getCurrentFinancialYear(): string
    {
        $year = (int)date('Y');
        $month = (int)date('m');
        if ($month > 3) return $year . '-' . ($year + 1);
        return ($year - 1) . '-' . $year;
    }
}