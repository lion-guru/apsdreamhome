<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;

class InquiryController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $page = (int)($_GET['page'] ?? 1);
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];

        if ($search) {
            $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM inquiries $where";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        $totalPages = ceil($total / $perPage);

        // Get inquiries
        $sql = "SELECT * FROM inquiries $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get counts
        $newCount = $this->getCountByStatus('new');
        $pendingCount = $this->getCountByStatus('pending');
        $contactedCount = $this->getCountByStatus('contacted');

        $data = [
            'inquiries' => $inquiries,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'newCount' => $newCount,
            'pendingCount' => $pendingCount,
            'contactedCount' => $contactedCount,
            'status' => $status,
            'search' => $search
        ];

        $this->render('admin/inquiries/index', $data);
    }

    private function getCountByStatus($status)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM inquiries WHERE status = ?");
            $stmt->execute([$status]);
            return $stmt->fetch()['cnt'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function show($id = null)
    {
        if (!$id) {
            $this->redirect('/admin/inquiries');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE id = ?");
        $stmt->execute([$id]);
        $inquiry = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$inquiry) {
            $this->setFlash('error', 'Inquiry not found');
            $this->redirect('/admin/inquiries');
            return;
        }

        // Mark as read
        try {
            $this->db->prepare("UPDATE inquiries SET is_read = 1 WHERE id = ?")->execute([$id]);
            \App\Core\Cache::delete('admin_notify_inquiries_pending');
            \App\Core\Cache::delete('admin_notify_inquiries_today');
        } catch (\Throwable $e) {}

        // Mark as contacted if new
        if ($inquiry['status'] === 'new') {
            try {
                list($tSql, $tParams) = $this->tenantWhere();
                $updateStmt = $this->db->prepare("UPDATE inquiries SET status = 'contacted', updated_at = NOW() WHERE id = ? $tSql");
                $updateStmt->execute(array_merge([$id], $tParams));
                $inquiry['status'] = 'contacted';
            } catch (\Exception $e) { error_log('InquiryController view status update: ' . $e->getMessage()); }
        }

        // Fetch assigned staff member info
        $assignedUser = null;
        if (!empty($inquiry['assigned_to'])) {
            try {
                $uStmt = $this->db->prepare("SELECT id, name, email, phone, role FROM users WHERE id = ?");
                $uStmt->execute([$inquiry['assigned_to']]);
                $assignedUser = $uStmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {}
        }

        // Fetch active staff list for assignment dropdown
        $staffList = [];
        try {
            $staffList = $this->db->query("SELECT id, name, role FROM users WHERE role IN ('employee', 'agent', 'telecaller', 'manager', 'admin') ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}

        $data = [
            'inquiry'      => $inquiry,
            'assignedUser' => $assignedUser,
            'staffList'    => $staffList
        ];
        $this->render('admin/inquiries/view', $data);
    }

    public function assign()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $id = (int)($_POST['id'] ?? 0);
            $assignedTo = (int)($_POST['assigned_to'] ?? 0);

            if ($id <= 0 || $assignedTo <= 0) {
                $this->setFlash('error', 'Please select a valid staff member to assign.');
                $this->redirect('/admin/inquiries/view/' . $id);
                return;
            }

            try {
                $stmt = $this->db->prepare("SELECT name, phone, message FROM inquiries WHERE id = ?");
                $stmt->execute([$id]);
                $inquiry = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($inquiry) {
                    $updateStmt = $this->db->prepare("UPDATE inquiries SET assigned_to = ?, status = 'in_progress', updated_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$assignedTo, $id]);

                    // Determine appropriate portal destination URL based on staff role
                    $uStmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
                    $uStmt->execute([$assignedTo]);
                    $targetUser = $uStmt->fetch(\PDO::FETCH_ASSOC);
                    $targetRole = $targetUser['role'] ?? 'employee';
                    $actionUrl = in_array($targetRole, ['employee', 'telecaller'], true)
                        ? "/employee/inquiries/{$id}"
                        : "/admin/inquiries/view/{$id}";

                    // Send in-app notification to the assigned staff member
                    $notifService = new \App\Services\NotificationService($this->db);
                    $notifService->notify(
                        'inquiry',
                        "You have been assigned inquiry #{$id} from {$inquiry['name']} ({$inquiry['phone']}).",
                        $assignedTo,
                        $actionUrl,
                        "New Inquiry Assigned: {$inquiry['name']}"
                    );

                    \App\Core\Cache::delete('admin_notify_inquiries_pending');
                    \App\Core\Cache::delete('admin_notify_inquiries_today');
                    $this->setFlash('success', 'Inquiry assigned successfully and notification sent to staff member.');
                }
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to assign inquiry: ' . $e->getMessage());
            }
            $this->redirect('/admin/inquiries/view/' . $id);
            return;
        }
        $this->redirect('/admin/inquiries');
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? 'new';

            try {
                list($tSql, $tParams) = $this->tenantWhere();
                $stmt = $this->db->prepare("UPDATE inquiries SET status = ?, updated_at = NOW() WHERE id = ? $tSql");
                $stmt->execute(array_merge([$status, $id], $tParams));
                \App\Core\Cache::delete('admin_notify_inquiries_pending');
                \App\Core\Cache::delete('admin_notify_inquiries_today');
                $this->setFlash('success', 'Status updated successfully');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Failed to update status');
            }
        }
        $this->redirect('/admin/inquiries');
    }

    public function delete($id = null)
    {
        if (!$id) {
            $this->redirect('/admin/inquiries');
            return;
        }

        try {
            list($tSql, $tParams) = $this->tenantWhere();
            $stmt = $this->db->prepare("DELETE FROM inquiries WHERE id = ? $tSql");
            $stmt->execute(array_merge([$id], $tParams));
            $this->setFlash('success', 'Inquiry deleted successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to delete inquiry');
        }
        $this->redirect('/admin/inquiries');
    }
}
