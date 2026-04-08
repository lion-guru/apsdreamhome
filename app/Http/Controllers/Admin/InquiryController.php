<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;

class InquiryController extends AdminController
{
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
        } catch (Exception $e) {
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

        // Mark as contacted if new
        if ($inquiry['status'] === 'new') {
            try {
                $updateStmt = $this->db->prepare("UPDATE inquiries SET status = 'contacted', updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$id]);
                $inquiry['status'] = 'contacted';
            } catch (Exception $e) {}
        }

        $data = ['inquiry' => $inquiry];
        $this->render('admin/inquiries/view', $data);
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? 'new';

            try {
                $stmt = $this->db->prepare("UPDATE inquiries SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $id]);
                $this->setFlash('success', 'Status updated successfully');
            } catch (Exception $e) {
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
            $stmt = $this->db->prepare("DELETE FROM inquiries WHERE id = ?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Inquiry deleted successfully');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to delete inquiry');
        }
        $this->redirect('/admin/inquiries');
    }
}
