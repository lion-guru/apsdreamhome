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

    public function store()
    {
        $this->requireAdmin();
        $db = Database::getInstance();

        $data = [
            'name' => trim($_POST['property_title'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'area_sqft' => (float)($_POST['area_sqft'] ?? 0),
            'bedrooms' => (int)($_POST['bedrooms'] ?? 0),
            'bathrooms' => (int)($_POST['bathrooms'] ?? 0),
            'listing_type' => 'sell',
            'status' => 'pending',
            'posted_by' => (int)($_SESSION['admin_id'] ?? 0),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $db->query(
            "INSERT INTO user_properties (name, location, address, price, area_sqft, bedrooms, bathrooms, listing_type, status, posted_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($data)
        );

        $_SESSION['success'] = 'Resell property created successfully';
        header('Location: ' . BASE_URL . '/admin/resell-properties');
        exit;
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
        $db = Database::getInstance();
        $id = (int)$id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            try {
                $property = $db->fetchOne("SELECT * FROM user_properties WHERE id = ?", [$id]);
                if (!$property) {
                    return $this->redirect('/admin/resell-properties');
                }

                $existingImages = [];
                $image = $property['image'] ?? '';
                if (!empty($image)) {
                    $existingImages = (strpos($image, '[') === 0) ? (json_decode($image, true) ?: []) : array_values(array_filter(explode(',', $image)));
                }

                if (!empty($_FILES['property_image']['tmp_name'])) {
                    $file = $_FILES['property_image'];
                    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!in_array($file['type'], $allowed)) {
                        $this->setFlash('error', 'Invalid file type. Use JPG, PNG, or WebP.');
                        return $this->redirect("/admin/resell-properties/images/$id");
                    }
                    if ($file['size'] > 5 * 1024 * 1024) {
                        $this->setFlash('error', 'File too large. Max 5MB allowed.');
                        return $this->redirect("/admin/resell-properties/images/$id");
                    }

                    $uploadDir = __DIR__ . '/../../../public/uploads/properties/resell/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'resell_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $filepath = $uploadDir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $relativePath = '/uploads/properties/resell/' . $filename;
                        $existingImages[] = $relativePath;
                    } else {
                        $this->setFlash('error', 'Failed to save uploaded file.');
                        return $this->redirect("/admin/resell-properties/images/$id");
                    }
                }

                $db->update('user_properties', ['image' => json_encode($existingImages)], 'id = ?', [$id]);
                $this->setFlash('success', 'Image uploaded successfully.');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Upload failed: ' . $e->getMessage());
            }
            return $this->redirect("/admin/resell-properties/images/$id");
        }

        $property = $db->fetchOne("SELECT * FROM user_properties WHERE id = ?", [$id]);
        return $this->render('admin/resell_properties/images', [
            'page_title' => 'Property Images',
            'id' => $id,
            'property' => $property,
        ]);
    }

    public function deleteImage($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $db = Database::getInstance();
        $id = (int)$id;
        $index = (int)($_POST['image_index'] ?? -1);

        try {
            $property = $db->fetchOne("SELECT * FROM user_properties WHERE id = ?", [$id]);
            if (!$property) {
                return $this->redirect('/admin/resell-properties');
            }

            $image = $property['image'] ?? '';
            $images = (strpos($image, '[') === 0) ? (json_decode($image, true) ?: []) : array_values(array_filter(explode(',', $image)));

            if ($index >= 0 && $index < count($images)) {
                $removed = array_splice($images, $index, 1);
                // Delete physical file if local
                if (!empty($removed[0]) && strpos($removed[0], '/uploads/') === 0) {
                    $physPath = __DIR__ . '/../../../public' . $removed[0];
                    if (file_exists($physPath)) {
                        @unlink($physPath);
                    }
                }
                $db->update('user_properties', ['image' => json_encode($images)], 'id = ?', [$id]);
                $this->setFlash('success', 'Image removed.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Delete failed: ' . $e->getMessage());
        }
        return $this->redirect("/admin/resell-properties/images/$id");
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
        $db = Database::getInstance();
        $property = $db->fetchOne("SELECT * FROM user_properties WHERE id = ?", [(int)$id]);
        return $this->render('admin/resell_properties/commission', [
            'page_title' => 'Manage Commission',
            'id' => $id,
            'property' => $property,
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $db = Database::getInstance();
        $id = (int)$id;

        $data = [
            'name' => trim($_POST['property_title'] ?? ''),
            'property_type' => $_POST['property_type'] ?? 'plot',
            'address' => trim($_POST['address'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'area_sqft' => (int)($_POST['area_sqft'] ?? 0),
            'bedrooms' => (int)($_POST['bedrooms'] ?? 0),
            'bathrooms' => (int)($_POST['bathrooms'] ?? 0),
            'furnished' => $_POST['furnished'] ?? 'unfurnished',
            'price' => (float)($_POST['price'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        ];

        $sets = [];
        $params = [];
        foreach ($data as $k => $v) {
            $sets[] = "{$k} = ?";
            $params[] = $v;
        }
        $sets[] = "updated_at = NOW()";
        $params[] = $id;

        $db->query("UPDATE user_properties SET " . implode(', ', $sets) . " WHERE id = ?", $params);

        $_SESSION['success'] = 'Property updated successfully';
        header('Location: ' . BASE_URL . '/admin/resell-properties/edit/' . $id);
        exit;
    }

    public function updateStatus($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $db = Database::getInstance();
        $id = (int)$id;
        $newStatus = $_POST['status'] ?? '';

        if (!in_array($newStatus, ['pending','verified','approved','rejected','sold'])) {
            $_SESSION['error'] = 'Invalid status';
            header('Location: ' . BASE_URL . '/admin/resell-properties/status/' . $id);
            exit;
        }

        $now = date('Y-m-d H:i:s');
        $updates = ['status' => $newStatus, 'updated_at' => $now];
        if ($newStatus === 'verified') {
            $updates['verified_by'] = $_SESSION['admin_id'] ?? null;
            $updates['verified_at'] = $now;
        }
        if ($newStatus === 'sold') {
            $updates['sold_at'] = $now;
        }

        $sets = [];
        $params = [];
        foreach ($updates as $k => $v) {
            $sets[] = "{$k} = ?";
            $params[] = $v;
        }
        $params[] = $id;

        $db->query("UPDATE user_properties SET " . implode(', ', $sets) . " WHERE id = ?", $params);

        $_SESSION['success'] = 'Status updated to ' . ucfirst($newStatus);
        header('Location: ' . BASE_URL . '/admin/resell-properties/status/' . $id);
        exit;
    }

    public function deleteProperty($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $db = Database::getInstance();
        $db->query("DELETE FROM user_properties WHERE id = ?", [(int)$id]);
        $_SESSION['success'] = 'Property deleted';
        header('Location: ' . BASE_URL . '/admin/resell-properties');
        exit;
    }
}
