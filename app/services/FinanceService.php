<?php
namespace App\Services;

use PDO;
use \App\Traits\ServiceTenantTrait;

/**
 * FinanceService - budgets, GST, cash flow, tax management
 */
class FinanceService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function createBudget(int $departmentId, string $category, float $allocated, string $period, int $fiscalYear, int $createdBy = 0): array
    {
        $insertData = $this->tenantInsertData();
        $cols = 'department_id, category, allocated_amount, period, fiscal_year, created_by, status, created_at' . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = ':d, :c, :a, :p, :y, :u, \'active\', NOW()' . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $st = $this->db->prepare("INSERT INTO budgets ($cols) VALUES ($ph)");
        $params = [':d' => $departmentId, ':c' => $category, ':a' => $allocated, ':p' => $period, ':y' => $fiscalYear, ':u' => $createdBy];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function updateBudgetSpent(int $id, float $amount): array
    {
        $st = $this->db->prepare("UPDATE budgets SET spent_amount = spent_amount + :a WHERE id = :id" . $this->tenantSql());
        $st->execute([':a' => $amount, ':id' => $id]);
        return ['ok' => true];
    }

    public function listBudgets(int $departmentId = 0, int $fiscalYear = 0): array
    {
        try {
            $sql = "SELECT b.*, d.name as department_name, b.budget_name AS period FROM budgets b LEFT JOIN departments d ON b.department_id = d.id WHERE 1=1" . $this->tenantSql('b');
            $params = [];
            if ($departmentId) { $sql .= " AND b.department_id = :d"; $params[':d'] = $departmentId; }
            if ($fiscalYear) { $sql .= " AND b.fiscal_year = :y"; $params[':y'] = $fiscalYear; }
            $sql .= " ORDER BY b.fiscal_year DESC, b.category";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getBudgetUtilization(int $id): ?array
    {
        $st = $this->db->prepare("SELECT *, (spent_amount / allocated_amount * 100) as utilization_pct FROM budgets WHERE id = :id" . $this->tenantSql());
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function createBudgetExpense(int $budgetId, string $description, float $amount, string $date, int $createdBy = 0, ?string $receiptUrl = null): array
    {
        $insertData = $this->tenantInsertData();
        $cols = 'budget_id, description, amount, expense_date, receipt_url, status, created_by, created_at' . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = ':b, :d, :a, :dt, :r, \'pending\', :u, NOW()' . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $st = $this->db->prepare("INSERT INTO budget_expenses ($cols) VALUES ($ph)");
        $params = [':b' => $budgetId, ':d' => $description, ':a' => $amount, ':dt' => $date, ':r' => $receiptUrl, ':u' => $createdBy];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st->execute($params);

        $st2 = $this->db->prepare("UPDATE budgets SET spent_amount = spent_amount + :a WHERE id = :id");
        $st2->execute([':a' => $amount, ':id' => $budgetId]);

        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function listExpenses(int $budgetId = 0, string $status = ''): array
    {
        try {
            $sql = "SELECT e.*, b.category, d.name as department_name FROM budget_expenses e LEFT JOIN budgets b ON e.budget_id = b.id LEFT JOIN departments d ON b.department_id = d.id WHERE 1=1" . $this->tenantSql('e');
            $params = [];
            if ($budgetId) { $sql .= " AND e.budget_id = :b"; $params[':b'] = $budgetId; }
            if ($status) { $sql .= " AND e.status = :s"; $params[':s'] = $status; }
            $sql .= " ORDER BY e.expense_date DESC LIMIT 200";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function approveExpense(int $id, int $approverId): array
    {
        $st = $this->db->prepare("UPDATE budget_expenses SET status = 'approved', approved_by = :a, approved_at = NOW() WHERE id = :id" . $this->tenantSql());
        $st->execute([':a' => $approverId, ':id' => $id]);
        return ['ok' => true];
    }

    public function createBudgetPlan(string $name, int $fiscalYear, float $totalRevenue, float $totalExpense, string $strategy, int $createdBy = 0): array
    {
        $insertData = $this->tenantInsertData();
        $cols = 'plan_name, fiscal_year, projected_revenue, projected_expense, projected_profit, strategy, status, created_by, created_at' . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = ':n, :y, :r, :e, :p, :s, \'draft\', :u, NOW()' . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $st = $this->db->prepare("INSERT INTO budget_planning ($cols) VALUES ($ph)");
        $params = [':n' => $name, ':y' => $fiscalYear, ':r' => $totalRevenue, ':e' => $totalExpense, ':p' => $totalRevenue - $totalExpense, ':s' => $strategy, ':u' => $createdBy];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function listBudgetPlans(int $fiscalYear = 0): array
    {
$sql = "SELECT * FROM budget_planning WHERE 1=1" . $this->tenantSql();
            $params = [];
            if ($fiscalYear) { $sql .= " AND fiscal_year = :y"; $params[':y'] = $fiscalYear; }
            $sql .= " ORDER BY fiscal_year DESC, created_at DESC";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCashFlow(int $planId, string $month, float $inflow, float $outflow, string $category = 'operations'): array
    {
        $insertData = $this->tenantInsertData();
        $cols = 'plan_id, month, inflow, outflow, net_flow, category, tenant_id' . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = ':p, :m, :i, :o, :n, :c, :tid' . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $st = $this->db->prepare("INSERT INTO cash_flow_projections ($cols) VALUES ($ph)
                                  ON DUPLICATE KEY UPDATE inflow = VALUES(inflow), outflow = VALUES(outflow), net_flow = VALUES(net_flow), category = VALUES(category)");
        $params = [':p' => $planId, ':m' => $month, ':i' => $inflow, ':o' => $outflow, ':n' => $inflow - $outflow, ':c' => $category, ':tid' => $this->tenantId()];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st->execute($params);
        return ['ok' => true];
    }

    public function getCashFlow(int $planId): array
    {
        $st = $this->db->prepare("SELECT * FROM cash_flow_projections WHERE plan_id = :p" . $this->tenantSql() . " ORDER BY month");
        $st->execute([':p' => $planId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTaxSlabs(string $taxType = '', string $stateCode = ''): array
    {
        try {
            $sql = "SELECT id, tax_type, 'ALL' AS state_code, min_amount, max_amount, rate_pct AS tax_rate, fiscal_year, is_active FROM tax_slabs WHERE is_active = 1";
            $params = [];
            if ($taxType) { $sql .= " AND tax_type = :t"; $params[':t'] = $taxType; }
            $sql .= " ORDER BY tax_type, min_amount";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addTaxSlab(string $taxType, string $stateCode, float $min, float $max, float $rate, ?string $effectiveTo = null): array
    {
        $st = $this->db->prepare("INSERT INTO tax_slabs (tax_type, state_code, min_amount, max_amount, tax_rate, effective_to, created_at) VALUES (:t, :sc, :mi, :ma, :r, :et, NOW())");
        $st->execute([':t' => $taxType, ':sc' => $stateCode, ':mi' => $min, ':ma' => $max, ':r' => $rate, ':et' => $effectiveTo]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function calculateTax(string $taxType, float $amount, string $stateCode = ''): float
    {
        $slabs = $this->getTaxSlabs($taxType, $stateCode);
        $tax = 0;
        foreach ($slabs as $s) {
            if ($amount >= (float)$s['min_amount'] && ($s['max_amount'] === null || $amount <= (float)$s['max_amount'])) {
                $tax = $amount * (float)$s['tax_rate'] / 100;
                break;
            }
        }
        return round($tax, 2);
    }

    public function getTaxTypes(): array
    {
        try {
            $st = $this->db->query("SELECT id, type_code AS code, type_name AS name, description, default_rate, is_active AS active FROM tax_types WHERE is_active = 1 ORDER BY type_name");
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addTaxType(string $code, string $name, string $description = '', float $defaultRate = 0): array
    {
        $st = $this->db->prepare("INSERT INTO tax_types (code, name, description, default_rate, active, created_at) VALUES (:c, :n, :d, :r, 1, NOW())
                                  ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), default_rate = VALUES(default_rate), active = 1");
        $st->execute([':c' => $code, ':n' => $name, ':d' => $description, ':r' => $defaultRate]);
        return ['ok' => true];
    }

    public function getGstSettings(string $stateCode = ''): ?array
    {
        $sql = "SELECT * FROM gst_settings WHERE 1=1";
        $params = [];
        if ($stateCode) { $sql .= " AND state_code = :sc"; $params[':sc'] = $stateCode; }
        $sql .= " ORDER BY effective_from DESC LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function saveGstSetting(string $stateCode, float $cgst, float $sgst, float $igst, ?string $effectiveFrom = null): array
    {
        $eff = $effectiveFrom ?: date('Y-m-d');
        $st = $this->db->prepare("INSERT INTO gst_settings (state_code, cgst_rate, sgst_rate, igst_rate, effective_from, active, created_at) VALUES (:sc, :c, :s, :i, :e, 1, NOW())
                                  ON DUPLICATE KEY UPDATE cgst_rate = VALUES(cgst_rate), sgst_rate = VALUES(sgst_rate), igst_rate = VALUES(igst_rate), effective_from = VALUES(effective_from), active = 1");
        $st->execute([':sc' => $stateCode, ':c' => $cgst, ':s' => $sgst, ':i' => $igst, ':e' => $eff]);
        return ['ok' => true];
    }

    public function calculateGst(float $amount, bool $interstate = false, string $stateCode = ''): array
    {
        $s = $this->getGstSettings($stateCode);
        if (!$s) $s = ['cgst_rate' => 9, 'sgst_rate' => 9, 'igst_rate' => 18];
        if ($interstate) {
            $tax = $amount * (float)$s['igst_rate'] / 100;
            return ['total_tax' => round($tax, 2), 'igst' => round($tax, 2), 'cgst' => 0, 'sgst' => 0];
        }
        $cgst = $amount * (float)$s['cgst_rate'] / 100;
        $sgst = $amount * (float)$s['sgst_rate'] / 100;
        return ['total_tax' => round($cgst + $sgst, 2), 'igst' => 0, 'cgst' => round($cgst, 2), 'sgst' => round($sgst, 2)];
    }

    public function fileGstReturn(string $period, string $gstin, float $totalSales, float $totalPurchase, float $taxLiability, int $filedBy = 0): array
    {
        $insertData = $this->tenantInsertData();
        $cols = 'return_period, gstin, total_sales, total_purchase, tax_liability, status, filed_by, filed_at, created_at, tenant_id' . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = ':p, :g, :s, :pu, :t, \'filed\', :u, NOW(), NOW(), :tid' . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $st = $this->db->prepare("INSERT INTO gst_returns ($cols) VALUES ($ph)");
        $params = [':p' => $period, ':g' => $gstin, ':s' => $totalSales, ':pu' => $totalPurchase, ':t' => $taxLiability, ':u' => $filedBy, ':tid' => $this->tenantId()];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function listGstReturns(int $limit = 24): array
    {
        try {
            $st = $this->db->prepare("SELECT id, return_period, gstin, return_type, total_taxable_value AS total_sales, total_tax_amount AS tax_liability, total_itc_claimed, net_payable, filing_status AS status, filed_at, acknowledgment_number FROM gst_returns" . $this->tenantSql() . " ORDER BY filed_at DESC LIMIT :lim");
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function financialSummary(int $fiscalYear): array
    {
        $out = ['budgets' => 0, 'spent' => 0, 'remaining' => 0, 'utilization' => 0, 'expense_count' => 0];
        try {
            $st = $this->db->prepare("SELECT SUM(allocated_amount) as total_alloc, SUM(spent_amount) as total_spent, COUNT(*) as count FROM budgets WHERE fiscal_year = :y" . $this->tenantSql());
            $st->execute([':y' => (string)$fiscalYear]);
            $b = $st->fetch(PDO::FETCH_ASSOC);
            $out['budgets'] = (float)($b['total_alloc'] ?? 0);
            $out['spent'] = (float)($b['total_spent'] ?? 0);
            $out['remaining'] = $out['budgets'] - $out['spent'];
            $out['utilization'] = $out['budgets'] > 0 ? round(($out['spent'] / $out['budgets']) * 100, 1) : 0;
        } catch (\Throwable $e) { error_log($e->getMessage()); }
        try {
            $st2 = $this->db->prepare("SELECT COUNT(*) as c FROM budget_expenses e LEFT JOIN budgets b ON e.budget_id = b.id WHERE b.fiscal_year = :y" . $this->tenantSql('e'));
            $st2->execute([':y' => (string)$fiscalYear]);
            $out['expense_count'] = (int)$st2->fetchColumn();
        } catch (\Throwable $e) { error_log($e->getMessage()); }
        return $out;
    }
}
