<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class UserPropertyController extends AdminController
{
    public function index()
    {
        $page = (int)($_GET['page'] ?? 1);
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];

        if ($status) {
            $where .= " AND up.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $where .= " AND up.property_type = ?";
            $params[] = $type;
        }

        if ($search) {
            $where .= " AND (up.name LIKE ? OR up.phone LIKE ? OR up.email LIKE ? OR up.address LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $countSql = "SELECT COUNT(*) as total FROM user_properties up $where";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT up.*, 
                s.name as state_name,
                d.name as district_name,
                c.name as city_name
                FROM user_properties up
                LEFT JOIN states s ON up.state_id = s.id
                LEFT JOIN districts d ON up.district_id = d.id
                LEFT JOIN cities c ON up.city_id = c.id
                $where
                ORDER BY up.created_at DESC
                LIMIT $perPage OFFSET $offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $statusCounts = $this->getPropertyStatusCounts();

        $data = [
            'properties' => $properties,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'status' => $status,
            'type' => $type,
            'search' => $search,
            'statusCounts' => $statusCounts,
            'page_title' => 'User Properties'
        ];

        $this->render('admin/user-properties/index', $data);
    }

    public function verify($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE id = ?");
        $stmt->execute([$id]);
        $property = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$property) {
            header('Location: /admin/user-properties?error=not_found');
            exit;
        }

        $data = [
            'property' => $property,
            'page_title' => 'Verify Property'
        ];

        $this->render('admin/user-properties/verify', $data);
    }

    public function action()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/user-properties');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        if (!$id || !in_array($action, ['approve', 'reject', 'verify'])) {
            header('Location: /admin/user-properties?error=invalid');
            exit;
        }

        $adminId = $_SESSION['admin_id'] ?? 1;

        if ($action === 'approve') {
            $status = 'approved';
        } elseif ($action === 'reject') {
            $status = 'rejected';
        } else {
            $status = 'verified';
        }

        $stmt = $this->db->prepare("
            UPDATE user_properties 
            SET status = ?, verified_by = ?, verified_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $adminId, $id]);

        header('Location: /admin/user-properties?success=updated');
        exit;
    }

    private function getPropertyStatusCounts()
    {
        $sql = "SELECT status, COUNT(*) as count FROM user_properties GROUP BY status";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $counts = [
            'all' => 0,
            'pending' => 0,
            'verified' => 0,
            'approved' => 0,
            'rejected' => 0,
            'sold' => 0
        ];

        foreach ($results as $row) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['all'] += (int)$row['count'];
        }

        return $counts;
    }
}
?>
