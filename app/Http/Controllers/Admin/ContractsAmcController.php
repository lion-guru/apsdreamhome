<?php
/**
 * Contracts AMC Controller — AMC/Contract renewal tracking
 * Routes: /admin/contracts-amc (index), /admin/contracts-amc/create, /admin/contracts-amc/store,
 *         /admin/contracts-amc/{id}, /admin/contracts-amc/{id}/edit, /admin/contracts-amc/{id}/update,
 *         /admin/contracts-amc/{id}/renew, /admin/contracts-amc/{id}/delete
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class ContractsAmcController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all contracts/AMCs with filters
     */
    public function index()
    {
        $this->requireAdmin();
        [$tSql, $tParams] = $this->tenantWhere();

        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $where = "WHERE 1=1{$tSql}";
        $params = array_values($tParams);

        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        if ($type) {
            $where .= " AND type = ?";
            $params[] = $type;
        }
        if ($search) {
            $where .= " AND (contract_number LIKE ? OR party_name LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Stats cards
        $stats = [
            'total' => (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM contracts_amc {$where}", $params)['c'],
            'active' => (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM contracts_amc WHERE status='active'{$tSql}", $tParams)['c'],
            'expiring_soon' => (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM contracts_amc WHERE status='expiring_soon'{$tSql}", $tParams)['c'],
            'expired' => (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM contracts_amc WHERE status='expired'{$tSql}", $tParams)['c'],
            'renewal_value' => (float)$this->db->fetchOne("SELECT COALESCE(SUM(amount),0) as v FROM contracts_amc WHERE status IN ('active','expiring_soon'){$tSql}", $tParams)['v'],
        ];

        // Pagination
        $total = (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM contracts_amc {$where}", $params)['c'];
        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $contracts = $this->db->fetchAll("
            SELECT c.*, u.name as creator_name
            FROM contracts_amc c
            LEFT JOIN users u ON u.id = c.created_by
            {$where}
            ORDER BY c.end_date ASC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params) ?: [];

        // Compute days to expiry and status badge
        foreach ($contracts as &$c) {
            $end = new DateTime($c['end_date']);
            $now = new DateTime();
            $c['days_to_expiry'] = $now->diff($end)->days;
            $c['is_expired'] = $end < $now;
            $c['is_expiring'] = !$c['is_expired'] && $c['days_to_expiry'] <= (int)($c['renewal_notice_days'] ?? 30);
        }
        unset($c);

        return $this->render('admin/contracts_amc/index', [
            'page_title' => 'Contracts & AMC Tracker',
            'page_description' => 'Track contract renewals, AMC expiries, and upcoming payments',
            'contracts' => $contracts,
            'stats' => $stats,
            'filters' => ['status' => $status, 'type' => $type, 'search' => $search],
            'pagination' => ['page' => $page, 'total_pages' => $totalPages, 'total' => $total],
            'statuses' => ['draft','active','expiring_soon','expired','renewed','cancelled'],
            'types' => ['amc','rental','service','maintenance','warranty','other'],
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/contracts_amc/form', [
            'page_title' => 'Create Contract / AMC',
            'contract' => null,
            'users' => $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('customer','associate','agent') ORDER BY name") ?: [],
        ]);
    }

    /**
     * Store new contract
     */
    public function store()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();

        $contractNumber = 'CTR-' . strtoupper(substr(uniqid(), -8));
        $start = $_POST['start_date'] ?? date('Y-m-d');
        $end = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 year'));
        $noticeDays = (int)($_POST['renewal_notice_days'] ?? 30);
        $renewalDate = date('Y-m-d', strtotime($end . " -{$noticeDays} days"));

        try {
            $this->db->execute("
                INSERT INTO contracts_amc (
                    contract_number, type, party_name, party_type, party_id,
                    description, start_date, end_date, renewal_date,
                    amount, currency, billing_cycle, status,
                    auto_renew, renewal_notice_days, notes,
                    created_by, tenant_id, created_at, updated_at
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
            ", [
                $contractNumber,
                $_POST['type'] ?? 'amc',
                $_POST['party_name'] ?? '',
                $_POST['party_type'] ?? 'customer',
                (int)($_POST['party_id'] ?? 0) ?: null,
                $_POST['description'] ?? '',
                $start,
                $end,
                $renewalDate,
                (float)($_POST['amount'] ?? 0),
                $_POST['currency'] ?? 'INR',
                $_POST['billing_cycle'] ?? 'yearly',
                'active',
                (int)($_POST['auto_renew'] ?? 0),
                $noticeDays,
                $_POST['notes'] ?? '',
                $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
                $tid,
            ]);

            $this->setFlash('success', 'Contract/AMC created successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/contracts-amc');
    }

    /**
     * Show contract detail
     */
    public function show($id)
    {
        $this->requireAdmin();
        [$tSql, $tParams] = $this->tenantWhere();
        $contract = $this->db->fetch("
            SELECT c.*, u.name as creator_name
            FROM contracts_amc c
            LEFT JOIN users u ON u.id = c.created_by
            WHERE c.id = ?{$tSql}
        ", array_merge([$id], $tParams));

        if (!$contract) {
            $this->setFlash('error', 'Contract not found');
            $this->redirect('/admin/contracts-amc');
        }

        // Compute status
        $end = new DateTime($contract['end_date']);
        $now = new DateTime();
        $contract['days_to_expiry'] = $now->diff($end)->days;
        $contract['is_expired'] = $end < $now;
        $contract['is_expiring'] = !$contract['is_expired'] && $contract['days_to_expiry'] <= (int)($contract['renewal_notice_days'] ?? 30);

        // Recent activities
        $activities = $this->db->fetchAll("
            SELECT a.*, u.name as user_name
            FROM lead_activities a
            LEFT JOIN users u ON u.id = a.created_by
            WHERE a.lead_id = ? AND a.activity_type LIKE 'contract_%'
            ORDER BY a.created_at DESC
            LIMIT 20
        ", [$id]) ?: [];

        return $this->render('admin/contracts_amc/detail', [
            'page_title' => 'Contract Details',
            'contract' => $contract,
            'activities' => $activities,
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $this->requireAdmin();
        [$tSql, $tParams] = $this->tenantWhere();
        $contract = $this->db->fetch("SELECT * FROM contracts_amc WHERE id = ?{$tSql}", array_merge([$id], $tParams));

        if (!$contract) {
            $this->setFlash('error', 'Contract not found');
            $this->redirect('/admin/contracts-amc');
        }

        return $this->render('admin/contracts_amc/form', [
            'page_title' => 'Edit Contract / AMC',
            'contract' => $contract,
            'users' => $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('customer','associate','agent') ORDER BY name") ?: [],
        ]);
    }

    /**
     * Update contract
     */
    public function update($id)
    {
        $this->requireAdmin();
        [$tSql, $tParams] = $this->tenantWhere();

        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';
        $noticeDays = (int)($_POST['renewal_notice_days'] ?? 30);
        $renewalDate = $end ? date('Y-m-d', strtotime($end . " -{$noticeDays} days")) : null;

        try {
            $sql = "UPDATE contracts_amc SET
                        type = ?, party_name = ?, party_type = ?, party_id = ?,
                        description = ?, start_date = ?, end_date = ?,
                        renewal_date = ?, amount = ?, currency = ?, billing_cycle = ?,
                        status = ?, auto_renew = ?, renewal_notice_days = ?, notes = ?,
                        updated_by = ?, updated_at = NOW()
                    WHERE id = ?{$tSql}";
            $params = [
                $_POST['type'] ?? 'amc',
                $_POST['party_name'] ?? '',
                $_POST['party_type'] ?? 'customer',
                (int)($_POST['party_id'] ?? 0) ?: null,
                $_POST['description'] ?? '',
                $start,
                $end,
                $renewalDate,
                (float)($_POST['amount'] ?? 0),
                $_POST['currency'] ?? 'INR',
                $_POST['billing_cycle'] ?? 'yearly',
                $_POST['status'] ?? 'active',
                (int)($_POST['auto_renew'] ?? 0),
                $noticeDays,
                $_POST['notes'] ?? '',
                $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
                $id,
            ];
            $params = array_merge($params, $tParams);
            $this->db->execute($sql, $params);

            $this->setFlash('success', 'Contract updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/contracts-amc/' . $id);
    }

    /**
     * Manual renew action
     */
    public function renew($id)
    {
        $this->requireAdmin();
        [$tSql, $tParams] = $this->tenantWhere();

        try {
            $contract = $this->db->fetch("SELECT end_date, billing_cycle FROM contracts_amc WHERE id = ?{$tSql}", array_merge([$id], $tParams));
            if (!$contract) { throw new \Exception('Not found'); }

            $oldEnd = new DateTime($contract['end_date']);
            $cycle = $contract['billing_cycle'] ?? 'yearly';
            $map = ['monthly' => '+1 month', 'quarterly' => '+3 months', 'half_yearly' => '+6 months', 'yearly' => '+1 year', 'one_time' => '+1 year'];
            $newEnd = clone $oldEnd;
            $newEnd->modify($map[$cycle] ?? '+1 year');
            $newEndStr = $newEnd->format('Y-m-d');

            $noticeDays = (int)$this->db->fetchOne("SELECT renewal_notice_days FROM contracts_amc WHERE id = ?{$tSql}", array_merge([$id], $tParams))['renewal_notice_days'] ?? 30;
            $newRenewal = date('Y-m-d', strtotime($newEndStr . " -{$noticeDays} days"));

            $this->db->execute("
                UPDATE contracts_amc SET
                    status = 'active', end_date = ?, renewal_date = ?, reminder_count = 0, last_reminder_at = NULL,
                    updated_by = ?, updated_at = NOW()
                WHERE id = ?{$tSql}
            ", array_merge([$newEndStr, $newRenewal, $_SESSION['admin_id'] ?? 0, $id], $tParams));

            // Log activity
            $this->db->execute("
                INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                VALUES (?, 'contract_renewed', ?, ?, NOW())
            ", [$id, "Contract renewed to {$newEndStr} (billing: {$cycle})", $_SESSION['admin_id'] ?? 0]);

            $this->setFlash('success', 'Contract renewed to ' . $newEndStr);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Renew failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/contracts-amc/' . $id);
    }

    /**
     * Soft delete
     */
    public function delete($id)
    {
        $this->requireAdmin();
        [$tSql, $tParams] = $this->tenantWhere();

        try {
            $this->db->execute("UPDATE contracts_amc SET status = 'cancelled', updated_at = NOW() WHERE id = ?{$tSql}", array_merge([$id], $tParams));
            $this->setFlash('success', 'Contract cancelled');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/contracts-amc');
    }
}