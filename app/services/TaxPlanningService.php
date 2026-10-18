<?php

namespace App\Services;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

class TaxPlanningService
{
    use ServiceTenantTrait;

    private Database $db;

    private const CORPORATE_RATE = 0.2517;

    private const ADVANCE_QUARTERS = [
        1 => ['due' => '09-30', 'pct' => 15.0],
        2 => ['due' => '12-30', 'pct' => 45.0],
        3 => ['due' => '03-15', 'pct' => 75.0],
        4 => ['due' => '06-15', 'pct' => 100.0],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function currentFinancialYear(): string
    {
        $y = (int)date('Y');
        return (date('m') >= '04') ? $y . '-' . ($y + 1) : ($y - 1) . '-' . $y;
    }

    public function getFinancialYearRange(string $fy): array
    {
        $start = (int)substr($fy, 0, 4);
        return [$start . '-04-01', ($start + 1) . '-03-31'];
    }

    public function getProfitSummary(string $fy): array
    {
        [$from, $to] = $this->getFinancialYearRange($fy);

        $incSql = "SELECT COUNT(*) c, COALESCE(SUM(amount),0) s, COALESCE(SUM(tax_amount),0) tds
                   FROM income_records
                   WHERE income_date >= ? AND income_date <= ? AND status != 'cancelled'" . $this->tenantWhere();
        $inc = $this->db->fetchOne($incSql, $this->tenantParams([$from, $to]));

        $expSql = "SELECT COUNT(*) c, COALESCE(SUM(amount),0) s
                   FROM expense_records
                   WHERE created_at >= ? AND created_at <= ? AND status != 'rejected'" . $this->tenantWhere();
        $exp = $this->db->fetchOne($expSql, $this->tenantParams([$from, $to]));

        $income = (float)($inc['s'] ?? 0);
        $expense = (float)($exp['s'] ?? 0);
        $profit = $income - $expense;
        $tax = $profit > 0 ? round($profit * self::CORPORATE_RATE, 2) : 0.0;

        return [
            'fy' => $fy,
            'income' => $income,
            'expense' => $expense,
            'profit' => $profit,
            'tax' => $tax,
            'income_count' => (int)($inc['c'] ?? 0),
            'expense_count' => (int)($exp['c'] ?? 0),
            'tds_collected' => (float)($inc['tds'] ?? 0),
            'rate' => self::CORPORATE_RATE,
        ];
    }

    public function getSchedule(string $fy): array
    {
        $summary = $this->getProfitSummary($fy);
        $sql = "SELECT * FROM advance_tax_schedule
                WHERE financial_year = ?" . $this->tenantWhere() . " ORDER BY quarter";
        $rows = $this->db->fetchAll($sql, $this->tenantParams([$fy]));
        $existing = [];
        foreach ($rows as $r) { $existing[(int)$r['quarter']] = $r; }

        $schedule = [];
        $cumulativePaid = 0.0;
        $start = (int)substr($fy, 0, 4);
        foreach (self::ADVANCE_QUARTERS as $q => $cfg) {
            $dueYear = $start;
            if ($cfg['due'] === '03-15' || $cfg['due'] === '06-15') { $dueYear = $start + 1; }
            $dueDate = $dueYear . '-' . $cfg['due'];
            $cumulative = round($summary['tax'] * $cfg['pct'] / 100, 2);
            $due = round($cumulative - $cumulativePaid, 2);
            if ($due < 0) { $due = 0.0; }

            $dbRow = $existing[$q] ?? null;
            $paid = $dbRow ? (float)$dbRow['paid_amount'] : 0.0;
            $cumulativePaid += $paid;

            if ($dbRow && $dbRow['status'] === 'paid') {
                $status = 'paid';
            } elseif ($dueDate < date('Y-m-d') && $due > 0) {
                $status = 'overdue';
            } else {
                $status = 'pending';
            }

            $schedule[] = [
                'quarter' => $q,
                'due_date' => $dueDate,
                'cumulative_pct' => $cfg['pct'],
                'projected_profit' => $summary['profit'],
                'projected_tax' => $summary['tax'],
                'cumulative_tax' => $cumulative,
                'due_amount' => $dbRow ? (float)$dbRow['due_amount'] : $due,
                'paid_amount' => $paid,
                'challan_ref' => $dbRow['challan_ref'] ?? null,
                'status' => $status,
                'db_id' => $dbRow['id'] ?? null,
            ];
        }
        return $schedule;
    }

    public function generateSchedule(string $fy): array
    {
        $summary = $this->getProfitSummary($fy);
        $schedule = $this->getSchedule($fy);
        $created = 0;

        foreach ($schedule as $q) {
            if ($q['db_id'] !== null) { continue; }
            $data = [
                'financial_year' => $fy,
                'quarter' => $q['quarter'],
                'due_date' => $q['due_date'],
                'cumulative_pct' => $q['cumulative_pct'],
                'projected_profit' => $summary['profit'],
                'projected_tax' => $summary['tax'],
                'due_amount' => $q['due_amount'],
                'paid_amount' => 0,
                'status' => $q['status'] === 'overdue' ? 'overdue' : 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $data = array_merge($data, $this->tenantInsertData());
            $this->db->insert('advance_tax_schedule', $data);
            $created++;
        }
        return ['created' => $created, 'summary' => $summary];
    }

    public function recordPayment(int $quarterId, float $amount, string $challanRef, string $notes = ''): bool
    {
        $sql = "SELECT * FROM advance_tax_schedule WHERE id = ?" . $this->tenantWhere();
        $row = $this->db->fetchOne($sql, $this->tenantParams([$quarterId]));
        if (!$row || $amount <= 0) { return false; }

        $newPaid = (float)$row['paid_amount'] + $amount;
        $this->db->update('advance_tax_schedule', [
            'paid_amount' => $newPaid,
            'challan_ref' => $challanRef,
            'status' => ($newPaid >= (float)$row['due_amount'] && (float)$row['due_amount'] > 0) ? 'paid' : 'pending',
            'paid_date' => date('Y-m-d'),
            'notes' => $notes ?: $row['notes'],
        ], 'id = :id', ['id' => $quarterId]);
        return true;
    }

    public function getTdsSummary(string $fy): array
    {
        $exists = $this->db->fetchAll("SHOW TABLES LIKE 'tds_challans'");
        if (empty($exists)) {
            return ['challans' => [], 'total_paid' => 0.0, 'count' => 0];
        }

        $sql = "SELECT * FROM tds_challans
                WHERE financial_year = ?" . $this->tenantWhere() . "
                ORDER BY id DESC LIMIT 20";
        $challans = $this->db->fetchAll($sql, $this->tenantParams([$fy]));
        $total = 0.0;
        foreach ($challans as $c) { $total += (float)($c['total_amount'] ?? 0); }

        return ['challans' => $challans, 'total_paid' => $total, 'count' => count($challans)];
    }

    public function getStats(string $fy): array
    {
        $summary = $this->getProfitSummary($fy);
        $schedule = $this->getSchedule($fy);
        $paid = 0.0;
        $outstanding = 0.0;
        $overdue = 0;
        foreach ($schedule as $q) {
            $paid += $q['paid_amount'];
            $outstanding += max(0, $q['due_amount'] - $q['paid_amount']);
            if ($q['status'] === 'overdue') { $overdue++; }
        }
        return [
            'fy' => $fy,
            'projected_tax' => $summary['tax'],
            'paid' => round($paid, 2),
            'balance' => round(max(0, $summary['tax'] - $paid), 2),
            'outstanding' => round($outstanding, 2),
            'overdue_count' => $overdue,
            'tds_collected' => $summary['tds_collected'],
        ];
    }
}