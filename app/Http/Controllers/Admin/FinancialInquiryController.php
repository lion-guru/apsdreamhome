<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class FinancialInquiryController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';

            $where = [];
            $params = [];

            if (!empty($status) && in_array($status, ['new', 'contacted', 'converted', 'closed'])) {
                $where[] = "fi.status = ?";
                $params[] = $status;
            }
            if (!empty($search)) {
                $where[] = "(fi.name LIKE ? OR fi.email LIKE ? OR fi.phone LIKE ? OR fi.message LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $tid = $this->tenantId();
            if ($tid > 1) {
                $where[] = "fi.tenant_id = ?";
                $params[] = $tid;
            }

            $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM financial_inquiries fi $whereSql");
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 25;
            $offset = ($page - 1) * $perPage;
            $totalPages = max(1, ceil($total / $perPage));

            $stmt = $this->db->prepare("
                SELECT fi.*
                FROM financial_inquiries fi
                $whereSql
                ORDER BY fi.created_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute($params);
            $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $stats = $this->db->query("
                SELECT
                    COUNT(*) as total,
                    SUM(IF(status='new',1,0)) as new_count,
                    SUM(IF(status='contacted',1,0)) as contacted,
                    SUM(IF(status='converted',1,0)) as converted,
                    SUM(IF(status='closed',1,0)) as closed
                FROM financial_inquiries
                " . ($tid > 1 ? "WHERE tenant_id = $tid" : "")
            )->fetch(\PDO::FETCH_ASSOC);

            $this->render('admin/financial-inquiries/index', [
                'page_title' => 'Financial Inquiries',
                'inquiries' => $inquiries,
                'stats' => $stats,
                'total' => $total,
                'page' => $page,
                'totalPages' => $totalPages,
                'status' => $status,
                'search' => $search,
            ]);
        } catch (\Exception $e) {
            error_log("FinancialInquiryController::index error: " . $e->getMessage());
            $this->render('admin/financial-inquiries/index', [
                'page_title' => 'Financial Inquiries',
                'inquiries' => [],
                'stats' => ['total' => 0, 'new_count' => 0, 'contacted' => 0, 'converted' => 0, 'closed' => 0],
                'total' => 0,
                'page' => 1,
                'totalPages' => 1,
                'status' => '',
                'search' => '',
            ]);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT fi.* FROM financial_inquiries fi WHERE fi.id = ?");
            $stmt->execute([$id]);
            $inquiry = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$inquiry) {
                $_SESSION['error'] = 'Inquiry not found.';
                $this->redirect('/admin/financial-inquiries');
                return;
            }

            $this->render('admin/financial-inquiries/show', [
                'page_title' => 'Financial Inquiry #' . $id,
                'inquiry' => $inquiry,
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            $this->redirect('/admin/financial-inquiries');
        }
    }

    public function updateStatus($id)
    {
        $this->requireAdmin();
        try {
            $status = $_POST['status'] ?? '';
            $notes = trim($_POST['notes'] ?? '');

            if (!in_array($status, ['new', 'contacted', 'converted', 'closed'])) {
                $_SESSION['error'] = 'Invalid status.';
                $this->redirect('/admin/financial-inquiries/' . $id);
                return;
            }

            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE financial_inquiries SET status = ?, notes = CONCAT(IFNULL(notes, ''), ?) WHERE id = ? $tenantSql");
            $params = array_merge([$status, "\n[" . date('Y-m-d H:i') . "] " . $notes, $id], $tenantParams);
            $stmt->execute($params);

            $_SESSION['success'] = 'Status updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/financial-inquiries/' . $id);
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("DELETE FROM financial_inquiries WHERE id = ? $tenantSql");
            $stmt->execute(array_merge([$id], $tenantParams));
            $_SESSION['success'] = 'Inquiry deleted.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/financial-inquiries');
    }
}
