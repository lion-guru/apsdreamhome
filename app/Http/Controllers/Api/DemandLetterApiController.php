<?php
namespace App\Http\Controllers\Api;

use App\Traits\TenantAwareTrait;

class DemandLetterApiController extends BaseApiController
{
    use TenantAwareTrait;

    public function list()
    {
        $uid = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$uid) return $this->jsonError('Authentication required', 401);
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            // Determine if customer vs staff (staff sees all, customer sees own)
            $isStaff = false;
            try {
                $st = $db->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
                $st->execute([$uid]);
                $role = $st->fetchColumn();
                $isStaff = in_array($role, ['admin','super_admin','manager','ceo','cfo','coo','finance_manager','finance_director','chartered_accountant','senior_accountant','franchise_owner'], true);
            } catch (\Exception $e) { $isStaff = false; }

            $filters = [
                'status' => trim((string)($_GET['status'] ?? '')),
                'colony_id' => (int)($_GET['colony_id'] ?? 0),
                'overdue_days' => trim((string)($_GET['overdue_days'] ?? '')),
                'search' => trim((string)($_GET['search'] ?? '')),
            ];
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
            $offset = ($page - 1) * $perPage;

            $where = ['dl.tenant_id = ?'];
            $params = [$tid];
            if (!$isStaff) {
                $where[] = '(b.customer_id = ? OR b.user_id = ?)';
                $params[] = $uid; $params[] = $uid;
            }
            if ($filters['status'] !== '') { $where[] = 'dl.status = ?'; $params[] = $filters['status']; }
            if ($filters['colony_id'] > 0) { $where[] = '(b.colony_id = ? OR p.colony_id = ?)'; $params[] = $filters['colony_id']; $params[] = $filters['colony_id']; }
            if ($filters['overdue_days'] !== '') {
                $days = (int)$filters['overdue_days'];
                if ($days > 0) { $where[] = "dl.status IN ('drafted','sent','overdue') AND DATEDIFF(CURDATE(), dl.due_date) >= ?"; $params[] = $days; }
                elseif ($filters['overdue_days'] === '0') { $where[] = "dl.due_date < CURDATE() AND dl.status IN ('drafted','sent','overdue')"; }
            }
            if ($filters['search'] !== '') {
                $where[] = '(dl.letter_number LIKE ? OR b.booking_number LIKE ?)';
                $like = '%' . $filters['search'] . '%';
                $params[] = $like; $params[] = $like;
            }
            $whereSql = implode(' AND ', $where);
            $needsPlotJoin = true;
            $cntSql = "SELECT COUNT(*) FROM booking_demand_letters dl JOIN bookings b ON b.id=dl.booking_id LEFT JOIN plots p ON p.id=b.plot_id WHERE {$whereSql}";
            $st = $db->prepare($cntSql); $st->execute($params); $total = (int)$st->fetchColumn();

            $sql = "SELECT dl.*, b.booking_number, COALESCE(cu.name, bu.name) AS customer_name,
                           COALESCE(col.name, pcol.name) AS colony_name,
                           DATEDIFF(CURDATE(), dl.due_date) AS overdue_days_calc
                    FROM booking_demand_letters dl
                    JOIN bookings b ON b.id=dl.booking_id
                    LEFT JOIN plots p ON p.id=b.plot_id
                    LEFT JOIN colonies col ON col.id=b.colony_id
                    LEFT JOIN colonies pcol ON pcol.id=p.colony_id
                    LEFT JOIN users cu ON cu.id=b.customer_id
                    LEFT JOIN users bu ON bu.id=b.user_id
                    WHERE {$whereSql}
                    ORDER BY dl.due_date ASC, dl.id DESC LIMIT {$perPage} OFFSET {$offset}";
            $st = $db->prepare($sql); $st->execute($params);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            foreach ($rows as &$r) {
                $r['pdf_url'] = $base . '/admin/finance/demand-letters/' . $r['id'] . '/pdf';
                $r['pay_url'] = $base . '/user/installments/' . (int)($r['installment_id'] ?? 0) . '/pay';
            }
            return $this->jsonSuccess(['letters' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'filters' => $filters]);
        } catch (\Exception $e) {
            error_log('DemandLetterApiController::list error: ' . $e->getMessage());
            return $this->jsonError('Failed to load demand letters', 500);
        }
    }

    public function detail($id = null)
    {
        $uid = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$uid) return $this->jsonError('Authentication required', 401);
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if (!$id) return $this->jsonError('Invalid ID', 400);
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            $st = $db->prepare("SELECT dl.*, b.booking_number, b.customer_id, b.user_id FROM booking_demand_letters dl JOIN bookings b ON b.id=dl.booking_id WHERE dl.id=? AND dl.tenant_id=? LIMIT 1");
            $st->execute([$id, $tid]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$row) return $this->jsonError('Not found', 404);
            // Customers can only see own
            $st = $db->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
            $st->execute([$uid]);
            $role = $st->fetchColumn();
            $isStaff = in_array($role, ['admin','super_admin','manager','ceo','cfo'], true);
            if (!$isStaff && (int)$row['customer_id'] !== $uid && (int)$row['user_id'] !== $uid) return $this->jsonError('Forbidden', 403);
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            $row['pdf_url'] = $base . '/admin/finance/demand-letters/' . $id . '/pdf';
            return $this->jsonSuccess($row);
        } catch (\Exception $e) {
            return $this->jsonError('Failed', 500);
        }
    }
}
