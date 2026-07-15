<?php
namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;

class ResellPropertiesAdminController extends AdminController
{
    public function index() 
    {
        $this->requireAdmin();
        $db = Database::getInstance();
        $base = BASE_URL;

        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $where = "WHERE listing_type = 'sell'";
        $params = [];

        if ($search !== '') {
            $where .= " AND (name LIKE ? OR location LIKE ? OR address LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($status !== '' && in_array($status, ['pending','verified','approved','rejected','sold'])) {
            $where .= " AND status = ?";
            $params[] = $status;
        }

        $total = $db->fetchOne("SELECT COUNT(*) as c FROM user_properties {$where}", $params)['c'] ?? 0;
        $totalPages = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $properties = $db->fetchAll(
            "SELECT up.*, u.name as seller_name, u.phone as seller_phone
             FROM user_properties up
             LEFT JOIN users u ON up.posted_by = u.id
             {$where}
             ORDER BY up.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $stats = [
            'total' => $db->fetchOne("SELECT COUNT(*) as c FROM user_properties WHERE listing_type = 'sell'")['c'] ?? 0,
            'active' => $db->fetchOne("SELECT COUNT(*) as c FROM user_properties WHERE listing_type = 'sell' AND status IN ('verified','approved')")['c'] ?? 0,
            'pending' => $db->fetchOne("SELECT COUNT(*) as c FROM user_properties WHERE listing_type = 'sell' AND status = 'pending'")['c'] ?? 0,
            'sold' => $db->fetchOne("SELECT COUNT(*) as c FROM user_properties WHERE listing_type = 'sell' AND status = 'sold'")['c'] ?? 0,
        ];

        return $this->render('admin/resell_properties/index', [
            'page_title' => 'Resell Properties',
            'properties' => $properties,
            'stats' => $stats,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'status' => $status,
            'base' => $base,
        ]);
    }
    
    public function create() 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/create', ['page_title' => 'Add Resell Property']);
    }
    
    public function edit($id) 
    {
        $this->requireAdmin();
        $db = Database::getInstance();
        $property = $db->fetchOne("SELECT * FROM user_properties WHERE id = ?", [(int)$id]);
        if (!$property) {
            $_SESSION['error'] = 'Property not found';
            header('Location: ' . BASE_URL . '/admin/resell-properties');
            exit;
        }
        return $this->render('admin/resell_properties/edit', [
            'page_title' => 'Edit Resell Property',
            'id' => $id,
            'property' => $property,
        ]);
    }
    
    public function details($id) 
    {
        $this->requireAdmin();
        $db = Database::getInstance();
        $property = $db->fetchOne(
            "SELECT up.*, u.name as seller_name, u.phone as seller_phone, u.email as seller_email
             FROM user_properties up
             LEFT JOIN users u ON up.posted_by = u.id
             WHERE up.id = ?",
            [(int)$id]
        );
        if (!$property) {
            $_SESSION['error'] = 'Property not found';
            header('Location: ' . BASE_URL . '/admin/resell-properties');
            exit;
        }
        return $this->render('admin/resell_properties/view', [
            'page_title' => 'Resell Property Details',
            'id' => $id,
            'property' => $property,
            'base' => BASE_URL,
        ]);
    }
    
    public function images($id) 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/images', ['page_title' => 'Property Images', 'id' => $id]);
    }
    
    public function status($id) 
    {
        $this->requireAdmin();
        $db = Database::getInstance();
        $property = $db->fetchOne("SELECT * FROM user_properties WHERE id = ?", [(int)$id]);
        return $this->render('admin/resell_properties/status', [
            'page_title' => 'Update Status',
            'id' => $id,
            'property' => $property,
        ]);
    }
    
    public function commission($id) 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/commission', ['page_title' => 'Manage Commission', 'id' => $id]);
    }
}
