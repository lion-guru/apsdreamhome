<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;

class PropertyTaxService
{
    use TenantAwareTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function calculateTax(array $params): array
    {
        $taxType = $params['tax_type'] ?? 'property_tax';
        $propertyCategory = $params['property_category'] ?? 'residential';
        $builtUpArea = (float)($params['built_up_area'] ?? 0);
        $landArea = (float)($params['land_area'] ?? 0);
        $annualRentalValue = (float)($params['annual_rental_value'] ?? 0);
        $capitalValue = (float)($params['capital_value'] ?? 0);
        $rate = (float)($params['tax_rate'] ?? 0);
        $rateUnit = $params['rate_unit'] ?? 'per_sqft';
        $rebatePercent = (float)($params['rebate_percent'] ?? 0);
        $surchargePercent = (float)($params['surcharge_percent'] ?? 0);

        $baseTax = 0;
        switch ($rateUnit) {
            case 'per_sqft':
                $baseTax = ($builtUpArea + $landArea) * $rate;
                break;
            case 'per_sqm':
                $baseTax = (($builtUpArea + $landArea) / 10.764) * $rate;
                break;
            case 'percent_arv':
                $baseTax = $annualRentalValue * ($rate / 100);
                break;
            case 'percent_cv':
                $baseTax = $capitalValue * ($rate / 100);
                break;
            case 'flat':
                $baseTax = $rate;
                break;
        }

        $rebateAmount = $baseTax * ($rebatePercent / 100);
        $surchargeAmount = $baseTax * ($surchargePercent / 100);
        $netTax = $baseTax - $rebateAmount + $surchargeAmount;

        return [
            'base_tax' => round($baseTax, 2),
            'rebate_amount' => round($rebateAmount, 2),
            'surcharge_amount' => round($surchargeAmount, 2),
            'net_tax' => round($netTax, 2),
        ];
    }

    public function calculatePenalty(float $netTax, string $dueDate, string $paymentDate, float $penaltyRatePerMonth = 1.0, float $maxPenaltyPercent = 25.0): float
    {
        $due = new \DateTime($dueDate);
        $paid = new \DateTime($paymentDate);
        if ($paid <= $due) {
            return 0.0;
        }
        $monthsLate = ceil($due->diff($paid)->days / 30);
        $penalty = $netTax * ($penaltyRatePerMonth / 100) * $monthsLate;
        $maxPenalty = $netTax * ($maxPenaltyPercent / 100);
        return round(min($penalty, $maxPenalty), 2);
    }

    public function getApplicableRebates(array $conditions): array
    {
        $rules = $this->db->fetchAll("
            SELECT * FROM property_tax_rebate_rules
            WHERE is_active = 1
            AND effective_from <= CURDATE()
            AND (effective_to IS NULL OR effective_to >= CURDATE())
            AND tenant_id = ?
        ", [$this->tenantId()]);

        $applicable = [];
        foreach ($rules as $rule) {
            $cond = json_decode($rule['conditions_json'], true) ?? [];
            $match = true;
            foreach ($cond as $key => $val) {
                if (!isset($conditions[$key]) || $conditions[$key] != $val) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $applicable[] = $rule;
            }
        }
        return $applicable;
    }

    public function createTaxMaster(array $data): int
    {
        $calc = $this->calculateTax($data);
        $data['base_tax_amount'] = $calc['base_tax'];
        $data['net_tax_amount'] = $calc['net_tax'];

        $columns = [
            'tenant_id', 'colony_id', 'plot_id', 'tax_type', 'assessment_year',
            'tax_authority', 'ward_zone', 'property_category', 'built_up_area',
            'land_area', 'annual_rental_value', 'capital_value', 'tax_rate',
            'rate_unit', 'base_tax_amount', 'rebate_percent', 'surcharge_percent',
            'net_tax_amount', 'due_date', 'penalty_rate_per_month', 'max_penalty_percent',
            'status', 'notes', 'created_by'
        ];

        $values = [];
        $placeholders = [];
        foreach ($columns as $col) {
            $values[] = $data[$col] ?? null;
            $placeholders[] = '?';
        }

        $this->db->execute(
            "INSERT INTO property_tax_master (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")",
            $values
        );
        return (int)$this->db->lastInsertId();
    }

    public function updateTaxMaster(int $id, array $data): bool
    {
        if (isset($data['tax_rate']) || isset($data['rate_unit']) || isset($data['built_up_area']) || isset($data['land_area']) || isset($data['annual_rental_value']) || isset($data['capital_value']) || isset($data['rebate_percent']) || isset($data['surcharge_percent'])) {
            $current = $this->db->fetchOne("SELECT * FROM property_tax_master WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
            if ($current) {
                $merged = array_merge($current, $data);
                $calc = $this->calculateTax($merged);
                $data['base_tax_amount'] = $calc['base_tax'];
                $data['net_tax_amount'] = $calc['net_tax'];
            }
        }

        $setParts = [];
        $values = [];
        foreach ($data as $key => $val) {
            if (in_array($key, ['id', 'tenant_id', 'created_at', 'created_by'])) continue;
            $setParts[] = "$key = ?";
            $values[] = $val;
        }
        if (empty($setParts)) return false;

        $values[] = $id;
        $values[] = $this->tenantId();

        $this->db->execute(
            "UPDATE property_tax_master SET " . implode(',', $setParts) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?",
            $values
        );
        return true;
    }

    public function generateDemandNotice(int $taxMasterId): ?string
    {
        $master = $this->db->fetchOne("SELECT * FROM property_tax_master WHERE id = ? AND tenant_id = ?", [$taxMasterId, $this->tenantId()]);
        if (!$master) return null;

        $noticeNumber = 'TAX-' . date('Y') . '-' . str_pad($taxMasterId, 6, '0', STR_PAD_LEFT);
        $this->db->execute(
            "UPDATE property_tax_master SET status = 'demand_generated', demand_notice_number = ?, demand_notice_date = CURDATE() WHERE id = ?",
            [$noticeNumber, $taxMasterId]
        );
        return $noticeNumber;
    }

    public function recordPayment(array $data): int
    {
        $master = $this->db->fetchOne("SELECT * FROM property_tax_master WHERE id = ? AND tenant_id = ?", [$data['tax_master_id'], $this->tenantId()]);
        if (!$master) throw new \Exception('Tax master not found');

        $penalty = $this->calculatePenalty(
            (float)$master['net_tax_amount'],
            $master['due_date'],
            $data['payment_date'],
            (float)$master['penalty_rate_per_month'],
            (float)$master['max_penalty_percent']
        );

        $rebates = $this->getApplicableRebates($data['rebate_conditions'] ?? []);
        $rebateTotal = 0;
        foreach ($rebates as $rule) {
            if ($rule['rebate_type'] === 'percent') {
                $rebateTotal += (float)$master['net_tax_amount'] * ($rule['rebate_value'] / 100);
            } else {
                $rebateTotal += $rule['rebate_value'];
            }
            if ($rule['max_rebate_amount'] && $rebateTotal > $rule['max_rebate_amount']) {
                $rebateTotal = $rule['max_rebate_amount'];
            }
        }

        $baseTaxPaid = (float)$data['base_tax_paid'] ?? (float)$master['net_tax_amount'];
        $totalPaid = $baseTaxPaid + $penalty - $rebateTotal;

        $receiptNumber = 'RCT-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        while ($this->db->fetchOne("SELECT 1 FROM property_tax_payments WHERE receipt_number = ? AND tenant_id = ?", [$receiptNumber, $this->tenantId()])) {
            $receiptNumber = 'RCT-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }

        $columns = [
            'tenant_id', 'tax_master_id', 'plot_id', 'customer_id', 'booking_id',
            'payment_date', 'payment_mode', 'cheque_dd_number', 'cheque_dd_date',
            'bank_name', 'bank_branch', 'transaction_ref', 'upi_ref',
            'base_tax_paid', 'penalty_paid', 'interest_paid', 'rebate_availed',
            'total_paid', 'receipt_number', 'receipt_date', 'status', 'notes', 'created_by'
        ];

        $values = [
            $this->tenantId(),
            $data['tax_master_id'],
            $data['plot_id'] ?? null,
            $data['customer_id'] ?? null,
            $data['booking_id'] ?? null,
            $data['payment_date'],
            $data['payment_mode'],
            $data['cheque_dd_number'] ?? null,
            $data['cheque_dd_date'] ?? null,
            $data['bank_name'] ?? null,
            $data['bank_branch'] ?? null,
            $data['transaction_ref'] ?? null,
            $data['upi_ref'] ?? null,
            $baseTaxPaid,
            $penalty,
            $data['interest_paid'] ?? 0,
            $rebateTotal,
            $totalPaid,
            $receiptNumber,
            $data['payment_date'],
            'pending',
            $data['notes'] ?? null,
            $data['created_by'] ?? null,
        ];

        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $this->db->execute(
            "INSERT INTO property_tax_payments (" . implode(',', $columns) . ") VALUES ($placeholders)",
            $values
        );
        return (int)$this->db->lastInsertId();
    }

    public function verifyPayment(int $paymentId, int $verifiedBy): bool
    {
        $this->db->execute(
            "UPDATE property_tax_payments SET status = 'verified', verified_by = ?, verified_at = NOW(), receipt_generated_at = NOW() WHERE id = ? AND tenant_id = ?",
            [$verifiedBy, $paymentId, $this->tenantId()]
        );
        return true;
    }

    public function getTaxMasterList(array $filters = []): array
    {
        $where = ["tm.tenant_id = ?"];
        $params = [$this->tenantId()];

        if (!empty($filters['colony_id'])) {
            $where[] = "tm.colony_id = ?";
            $params[] = $filters['colony_id'];
        }
        if (!empty($filters['plot_id'])) {
            $where[] = "tm.plot_id = ?";
            $params[] = $filters['plot_id'];
        }
        if (!empty($filters['assessment_year'])) {
            $where[] = "tm.assessment_year = ?";
            $params[] = $filters['assessment_year'];
        }
        if (!empty($filters['tax_type'])) {
            $where[] = "tm.tax_type = ?";
            $params[] = $filters['tax_type'];
        }
        if (!empty($filters['status'])) {
            $where[] = "tm.status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT tm.*, c.name as colony_name, p.plot_number
                FROM property_tax_master tm
                LEFT JOIN colonies c ON c.id = tm.colony_id
                LEFT JOIN plots p ON p.id = tm.plot_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY tm.assessment_year DESC, tm.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getPaymentHistory(int $taxMasterId): array
    {
        return $this->db->fetchAll("
            SELECT pp.*, u.name as customer_name, u.phone as customer_phone
            FROM property_tax_payments pp
            LEFT JOIN users u ON u.id = pp.customer_id
            WHERE pp.tax_master_id = ? AND pp.tenant_id = ?
            ORDER BY pp.payment_date DESC
        ", [$taxMasterId, $this->tenantId()]);
    }

    public function getColonyDemandRegister(int $colonyId, int $year): ?array
    {
        $row = $this->db->fetchOne("
            SELECT * FROM property_tax_demand_register
            WHERE colony_id = ? AND assessment_year = ? AND tenant_id = ?
        ", [$colonyId, $year, $this->tenantId()]);
        return $row ?: null;
    }

    public function generateDemandRegister(int $colonyId, int $year): array
    {
        $masters = $this->db->fetchAll("
            SELECT * FROM property_tax_master
            WHERE colony_id = ? AND assessment_year = ? AND tenant_id = ?
        ", [$colonyId, $year, $this->tenantId()]);

        $totalProperties = count($masters);
        $totalDemand = 0;
        foreach ($masters as $m) {
            $totalDemand += (float)$m['net_tax_amount'];
        }

        $payments = $this->db->fetchAll("
            SELECT SUM(total_paid) as collected, SUM(penalty_paid) as penalty
            FROM property_tax_payments pp
            JOIN property_tax_master tm ON tm.id = pp.tax_master_id
            WHERE tm.colony_id = ? AND tm.assessment_year = ? AND pp.tenant_id = ? AND pp.status = 'verified'
        ", [$colonyId, $year, $this->tenantId()]);

        $collected = (float)($payments[0]['collected'] ?? 0);
        $penaltyCollected = (float)($payments[0]['penalty'] ?? 0);
        $pending = $totalDemand - $collected;

        $register = $this->getColonyDemandRegister($colonyId, $year);
        if ($register) {
            $this->db->execute("
                UPDATE property_tax_demand_register SET
                    total_properties = ?, total_demand = ?, total_collected = ?,
                    total_pending = ?, total_penalty_collected = ?,
                    demand_generated_at = NOW(), generated_by = ?, status = 'generated'
                WHERE id = ?
            ", [$totalProperties, $totalDemand, $collected, $pending, $penaltyCollected, $_SESSION['admin_id'] ?? 1, $register['id']]);
        } else {
            $this->db->execute("
                INSERT INTO property_tax_demand_register
                (tenant_id, colony_id, assessment_year, total_properties, total_demand, total_collected, total_pending, total_penalty_collected, demand_generated_at, generated_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'generated')
            ", [$this->tenantId(), $colonyId, $year, $totalProperties, $totalDemand, $collected, $pending, $penaltyCollected, $_SESSION['admin_id'] ?? 1]);
        }

        return [
            'total_properties' => $totalProperties,
            'total_demand' => $totalDemand,
            'total_collected' => $collected,
            'total_pending' => $pending,
            'total_penalty_collected' => $penaltyCollected,
        ];
    }

    public function getTaxMaster(int $id): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM property_tax_master WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        return $row ?: null;
    }

    public function getPayment(int $id): ?array
    {
        $row = $this->db->fetchOne("SELECT * FROM property_tax_payments WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        return $row ?: null;
    }

    public function getRebateRules(): array
    {
        return $this->db->fetchAll("SELECT * FROM property_tax_rebate_rules WHERE tenant_id = ? ORDER BY rule_name", [$this->tenantId()]);
    }

    public function createRebateRule(array $data): int
    {
        $columns = ['tenant_id', 'rule_name', 'rule_code', 'applicable_tax_types', 'rebate_type', 'rebate_value', 'max_rebate_amount', 'conditions_json', 'is_active', 'effective_from', 'effective_to'];
        $values = [
            $this->tenantId(),
            $data['rule_name'],
            $data['rule_code'],
            json_encode($data['applicable_tax_types'] ?? []),
            $data['rebate_type'] ?? 'percent',
            $data['rebate_value'] ?? 0,
            $data['max_rebate_amount'] ?? null,
            json_encode($data['conditions_json'] ?? []),
            $data['is_active'] ?? 1,
            $data['effective_from'],
            $data['effective_to'] ?? null,
        ];
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $this->db->execute("INSERT INTO property_tax_rebate_rules (" . implode(',', $columns) . ") VALUES ($placeholders)", $values);
        return (int)$this->db->lastInsertId();
    }

    public function updateRebateRule(int $id, array $data): bool
    {
        $setParts = [];
        $values = [];
        foreach ($data as $key => $val) {
            if (in_array($key, ['id', 'tenant_id', 'created_at'])) continue;
            if ($key === 'applicable_tax_types' || $key === 'conditions_json') {
                $val = json_encode($val);
            }
            $setParts[] = "$key = ?";
            $values[] = $val;
        }
        if (empty($setParts)) return false;
        $values[] = $id;
        $values[] = $this->tenantId();
        $this->db->execute("UPDATE property_tax_rebate_rules SET " . implode(',', $setParts) . ", updated_at = NOW() WHERE id = ? AND tenant_id = ?", $values);
        return true;
    }

    public function deleteRebateRule(int $id): bool
    {
        $this->db->execute("DELETE FROM property_tax_rebate_rules WHERE id = ? AND tenant_id = ?", [$id, $this->tenantId()]);
        return true;
    }
}