<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Enterprise Township Civil Infrastructure & Contractor Work Order Service
 * Manages civil construction contracts, milestone Running Account (RA) bills,
 * statutory TDS deductions (Sec 194C), and defects liability retention holdbacks.
 */
class WorkOrderService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Issue a new Construction Work Order for a township colony.
     */
    public function createWorkOrder(array $data): array
    {
        $colonyId = (int)($data['colony_id'] ?? 0);
        $title = trim((string)($data['work_title'] ?? ''));
        $contractor = trim((string)($data['contractor_name'] ?? ''));
        $phone = trim((string)($data['contractor_phone'] ?? ''));
        $amount = (float)($data['contract_amount'] ?? 0);

        if ($colonyId <= 0 || $title === '' || $contractor === '' || $amount <= 0) {
            return ['success' => false, 'message' => 'Colony, work title, contractor name, and valid contract sum are required.'];
        }

        // Generate Work Order reference number: WO-APS-YEAR-XXXX
        $year = date('Y');
        $random = strtoupper(substr(uniqid(), -4));
        $orderNumber = "WO-APS-{$year}-{$random}";

        $category = in_array($data['work_category'] ?? '', ['boundary_wall', 'road_tarring', 'drainage', 'electrification', 'leveling', 'gate_arch', 'landscaping', 'general'], true)
            ? $data['work_category'] : 'general';
        $retentionPct = isset($data['retention_pct']) ? max(0, min(20, (float)$data['retention_pct'])) : 5.00;
        $tdsPct = isset($data['tds_pct']) ? max(0, min(10, (float)$data['tds_pct'])) : 1.00;

        $st = $this->pdo->prepare(
            "INSERT INTO construction_work_orders (
                tenant_id, colony_id, order_number, contractor_name, contractor_phone, contractor_pan, contractor_gstin,
                work_title, work_category, contract_amount, retention_pct, tds_pct, start_date, completion_deadline,
                status, scope_description, created_by, created_at
            ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())"
        );
        $st->execute([
            $colonyId,
            $orderNumber,
            $contractor,
            $phone,
            trim((string)($data['contractor_pan'] ?? '')),
            trim((string)($data['contractor_gstin'] ?? '')),
            $title,
            $category,
            $amount,
            $retentionPct,
            $tdsPct,
            !empty($data['start_date']) ? $data['start_date'] : null,
            !empty($data['completion_deadline']) ? $data['completion_deadline'] : null,
            trim((string)($data['scope_description'] ?? '')),
            (int)($data['created_by'] ?? 1)
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return [
            'success' => true,
            'work_order_id' => $id,
            'order_number' => $orderNumber,
            'message' => "Work Order #{$orderNumber} created successfully."
        ];
    }

    /**
     * Submit an RA (Running Account) Milestone Bill against a Work Order.
     */
    public function submitBill(int $workOrderId, array $data): array
    {
        $wo = $this->getWorkOrderBasic($workOrderId);
        if (!$wo) {
            return ['success' => false, 'message' => 'Work Order not found.'];
        }

        $gross = (float)($data['gross_amount'] ?? 0);
        if ($gross <= 0) {
            return ['success' => false, 'message' => 'Gross bill amount must be greater than zero.'];
        }

        // Calculate statutory TDS and contractual Retention holdback
        $retentionPct = (float)($wo['retention_pct'] ?? 5.00);
        $tdsPct = (float)($wo['tds_pct'] ?? 1.00);

        $retentionAmount = round(($gross * $retentionPct) / 100, 2);
        $tdsAmount = round(($gross * $tdsPct) / 100, 2);
        $netPayable = round($gross - $retentionAmount - $tdsAmount, 2);

        $billNumber = trim((string)($data['bill_number'] ?? ''));
        if ($billNumber === '') {
            $count = (int)$this->pdo->query("SELECT COUNT(*) FROM contractor_bills WHERE work_order_id = {$workOrderId}")->fetchColumn();
            $billNumber = 'RA-' . str_pad((string)($count + 1), 2, '0', STR_PAD_LEFT);
        }

        $billDate = !empty($data['bill_date']) ? $data['bill_date'] : date('Y-m-d');

        $st = $this->pdo->prepare(
            "INSERT INTO contractor_bills (
                tenant_id, work_order_id, bill_number, measurement_ref, measurement_notes,
                gross_amount, retention_amount, tds_amount, net_payable_amount, paid_amount,
                status, bill_date, created_at
            ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, 'submitted', ?, NOW())"
        );
        $st->execute([
            $workOrderId,
            $billNumber,
            trim((string)($data['measurement_ref'] ?? '')),
            trim((string)($data['measurement_notes'] ?? '')),
            $gross,
            $retentionAmount,
            $tdsAmount,
            $netPayable,
            $billDate
        ]);

        $billId = (int)$this->pdo->lastInsertId();

        return [
            'success' => true,
            'bill_id' => $billId,
            'bill_number' => $billNumber,
            'net_payable' => $netPayable,
            'message' => "Bill #{$billNumber} recorded with Net Payable ₹" . number_format($netPayable, 2)
        ];
    }

    /**
     * Admin approves a submitted RA bill.
     */
    public function approveBill(int $billId): array
    {
        $st = $this->pdo->prepare(
            "UPDATE contractor_bills SET status = 'approved', updated_at = NOW() WHERE id = ? AND status = 'submitted'"
        );
        $st->execute([$billId]);
        if ($st->rowCount() === 0) {
            return ['success' => false, 'message' => 'Bill not found or not in submitted status.'];
        }

        return ['success' => true, 'message' => "Bill #{$billId} approved for disbursement."];
    }

    /**
     * Record bank disbursement against an approved contractor bill.
     */
    public function disburseBillPayment(int $billId, float $paidAmount, string $paymentRef): array
    {
        if ($paidAmount <= 0) {
            return ['success' => false, 'message' => 'Valid payment amount required.'];
        }

        $st = $this->pdo->prepare(
            "UPDATE contractor_bills
             SET paid_amount = ?, payment_ref = ?, paid_at = NOW(), status = 'paid', updated_at = NOW()
             WHERE id = ? AND status IN ('approved', 'submitted')"
        );
        $st->execute([$paidAmount, trim($paymentRef), $billId]);

        return ['success' => true, 'message' => "Payment of ₹" . number_format($paidAmount, 2) . " recorded."];
    }

    /**
     * Get detailed work order with colony details and list of bills.
     */
    public function getWorkOrderDetail(int $id): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT cwo.*, c.name AS colony_name, c.location AS colony_location
             FROM construction_work_orders cwo
             LEFT JOIN colonies c ON c.id = cwo.colony_id
             WHERE cwo.id = ?"
        );
        $st->execute([$id]);
        $wo = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$wo) {
            return null;
        }

        $billsSt = $this->pdo->prepare(
            "SELECT * FROM contractor_bills WHERE work_order_id = ? ORDER BY bill_date ASC, id ASC"
        );
        $billsSt->execute([$id]);
        $wo['bills'] = $billsSt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totalGross = 0.0;
        $totalPaid = 0.0;
        $totalRetention = 0.0;
        $totalTds = 0.0;
        foreach ($wo['bills'] as $b) {
            $totalGross += (float)$b['gross_amount'];
            $totalPaid += (float)$b['paid_amount'];
            $totalRetention += (float)$b['retention_amount'];
            $totalTds += (float)$b['tds_amount'];
        }

        $wo['total_gross_billed'] = $totalGross;
        $wo['total_paid'] = $totalPaid;
        $wo['total_retention_held'] = $totalRetention;
        $wo['total_tds_deducted'] = $totalTds;
        $wo['completion_pct'] = (float)$wo['contract_amount'] > 0
            ? min(100, round(($totalGross / (float)$wo['contract_amount']) * 100, 1))
            : 0;

        return $wo;
    }

    /**
     * List work orders with filter and progress aggregations.
     */
    public function getWorkOrders(?int $colonyId = null, ?string $status = null): array
    {
        $sql = "SELECT cwo.*, c.name AS colony_name,
                       COALESCE(SUM(cb.gross_amount), 0) AS total_billed,
                       COALESCE(SUM(cb.paid_amount), 0) AS total_paid,
                       COALESCE(SUM(cb.retention_amount), 0) AS total_retention
                FROM construction_work_orders cwo
                LEFT JOIN colonies c ON c.id = cwo.colony_id
                LEFT JOIN contractor_bills cb ON cb.work_order_id = cwo.id
                WHERE 1=1";
        $params = [];

        if ($colonyId && $colonyId > 0) {
            $sql .= " AND cwo.colony_id = ?";
            $params[] = $colonyId;
        }

        if ($status && $status !== 'all') {
            $sql .= " AND cwo.status = ?";
            $params[] = $status;
        }

        $sql .= " GROUP BY cwo.id ORDER BY cwo.created_at DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Aggregate portfolio statistics.
     */
    public function getSummaryStats(): array
    {
        $woSt = $this->pdo->query(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(contract_amount), 0) AS total_contract_value
             FROM construction_work_orders WHERE status = 'active'"
        )->fetch(\PDO::FETCH_ASSOC);

        $billSt = $this->pdo->query(
            "SELECT COALESCE(SUM(gross_amount), 0) AS total_billed,
                    COALESCE(SUM(paid_amount), 0) AS total_paid,
                    COALESCE(SUM(retention_amount), 0) AS total_retention
             FROM contractor_bills"
        )->fetch(\PDO::FETCH_ASSOC);

        return [
            'active_orders' => (int)($woSt['total_orders'] ?? 0),
            'total_contract_value' => (float)($woSt['total_contract_value'] ?? 0),
            'total_billed' => (float)($billSt['total_billed'] ?? 0),
            'total_paid' => (float)($billSt['total_paid'] ?? 0),
            'total_retention_held' => (float)($billSt['total_retention'] ?? 0),
        ];
    }

    private function getWorkOrderBasic(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM construction_work_orders WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
