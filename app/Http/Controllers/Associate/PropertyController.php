<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociatePropertyController
 * Handles associate property management
 */
class PropertyController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Add property page
     */
    public function addProperty()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        // Get colonies for dropdown
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];
        $colonies = $db->fetchAll("SELECT * FROM colonies WHERE is_active = 1{$tidSql} ORDER BY name", $params);

        $this->render('associate/add_property', [
            'page_title' => 'Add Property - Associate Portal',
            'page_description' => 'List a new property',
            'colonies' => $colonies,
        ], 'layouts/associate');
    }

    /**
     * Store add property
     */
    public function storeAddProperty()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/add-property');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $colonyId = (int)($_POST['colony_id'] ?? 0);
            $facing = $_POST['facing'] ?? '';
            $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
            $data = [
                'property_type' => $_POST['property_type'] ?? 'plot',
                'name' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'price' => (float)($_POST['price'] ?? 0),
                'area_sqft' => (float)($_POST['area_sqft'] ?? 0),
                'bedrooms' => (int)($_POST['bedrooms'] ?? 0),
                'bathrooms' => (int)($_POST['bathrooms'] ?? 0),
                'address' => trim($_POST['address'] ?? ''),
                'location' => trim($_POST['city'] ?? ''),
                'city_name' => $_POST['city'] ?? '',
                'pincode' => $_POST['pincode'] ?? '',
                'status' => 'pending',
                'is_featured' => 0,
            ];

            // Validate
            if (empty($colonyId)) throw new Exception('Colony is required');
            if (empty($data['name'])) throw new Exception('Title is required');
            if ($data['price'] <= 0) throw new Exception('Price must be greater than 0');
            if ($data['area_sqft'] <= 0) throw new Exception('Area must be greater than 0');

            // Handle image uploads
            $images = [];
            if (!empty($_FILES['property_images']['name'][0])) {
                $uploadDir = __DIR__ . '/../../../public/uploads/associate_properties/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                foreach ($_FILES['property_images']['tmp_name'] as $key => $tmpName) {
                    if ($tmpName && $_FILES['property_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['property_images']['name'][$key], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($ext, $allowed)) {
                            $fileName = 'prop_' . uniqid() . '.' . $ext;
                            $dest = $uploadDir . $fileName;
                            if (move_uploaded_file($tmpName, $dest)) {
                                $images[] = '/uploads/associate_properties/' . $fileName;
                            }
                        }
                    }
                }
            }
            $data['image'] = $images[0] ?? null;
            $data['metadata'] = json_encode(['images' => $images, 'facing' => $facing, 'amenities' => $amenities, 'colony_id' => $colonyId]);

            // Insert property
            $tid = TenantContext::getId();
            $cols = array_keys($data);
            $vals = array_fill(0, count($cols), '?');
            $insertExtra = $this->tenantInsertData();
            if (!empty($insertExtra)) {
                $cols = array_merge($cols, array_keys($insertExtra));
                $vals = array_merge($vals, array_fill(0, count($insertExtra), '?'));
            }
            $cols = array_merge($cols, ['user_id']);
            $vals = array_merge($vals, ['?']);
            $params = array_merge(array_values($data), array_values($insertExtra ?? []), [$userId]);

            $stmt = $db->prepare("INSERT INTO user_properties (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")");
            $stmt->execute($params);
            $propertyId = (int)$db->lastInsertId();

            // Log activity
            $this->logActivity($userId, 'property_added', ['property_id' => $propertyId]);

            $_SESSION['success'] = 'Property submitted for review!';
            $this->redirect('/associate/properties');
        } catch (\Throwable $e) {
            error_log('AssociatePropertyController::storeAddProperty error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to add property: ' . $e->getMessage();
            $this->redirect('/associate/add-property');
        }
    }

    /**
     * List associate's properties
     */
    public function properties()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND up.tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $search = trim($_GET['search'] ?? '');
        if ($search) {
            $sql = "SELECT up.* FROM user_properties up WHERE up.user_id = ? AND (up.name LIKE ? OR up.address LIKE ?) {$tidSql} ORDER BY up.created_at DESC";
            $params = array_merge([$userId, "%{$search}%", "%{$search}%"], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
        } else {
            $sql = "SELECT up.* FROM user_properties up WHERE up.user_id = ? {$tidSql} ORDER BY up.created_at DESC";
        }

        $properties = $db->fetchAll($sql, $params) ?: [];

        $this->render('associate/properties', [
            'page_title' => 'My Properties - Associate Portal',
            'page_description' => 'Manage your listed properties',
            'properties' => $properties,
            'search' => $search,
        ], 'layouts/associate');
    }

    /**
     * Edit property page
     */
    public function editProperty($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND up.tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $property = $db->fetchOne("SELECT up.* FROM user_properties up WHERE up.id = ? AND up.user_id = ?{$tidSql} LIMIT 1", $params);

        if (!$property) {
            $_SESSION['error'] = 'Property not found';
            $this->redirect('/associate/properties');
            return;
        }

        // Get colonies for dropdown
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];
        $colonies = $db->fetchAll("SELECT * FROM colonies WHERE is_active = 1{$tidSql} ORDER BY name", $params);

        $this->render('associate/edit_property', [
            'page_title' => 'Edit Property - Associate Portal',
            'page_description' => 'Update property details',
            'property' => $property,
            'colonies' => $colonies,
        ], 'layouts/associate');
    }

    /**
     * Update property
     */
    public function updateProperty($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/associate/properties/edit/{$id}");
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $colonyId = (int)($_POST['colony_id'] ?? 0);
            $facingUp = $_POST['facing'] ?? '';
            $amenitiesUp = isset($_POST['amenities']) ? $_POST['amenities'] : [];
            $data = [
                'property_type' => $_POST['property_type'] ?? 'plot',
                'name' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'price' => (float)($_POST['price'] ?? 0),
                'area_sqft' => (float)($_POST['area_sqft'] ?? 0),
                'bedrooms' => (int)($_POST['bedrooms'] ?? 0),
                'bathrooms' => (int)($_POST['bathrooms'] ?? 0),
                'address' => trim($_POST['address'] ?? ''),
                'location' => trim($_POST['city'] ?? ''),
                'city_name' => $_POST['city'] ?? '',
                'pincode' => $_POST['pincode'] ?? '',
                'status' => $_POST['status'] ?? 'pending',
            ];

            // Handle new image uploads
            $existingImages = json_decode($_POST['existing_images'] ?? '[]', true);
            if (!empty($_FILES['property_images']['name'][0])) {
                $uploadDir = __DIR__ . '/../../../public/uploads/associate_properties/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                foreach ($_FILES['property_images']['tmp_name'] as $key => $tmpName) {
                    if ($tmpName && $_FILES['property_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['property_images']['name'][$key], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($ext, $allowed)) {
                            $fileName = 'prop_' . uniqid() . '.' . $ext;
                            $dest = $uploadDir . $fileName;
                            if (move_uploaded_file($tmpName, $dest)) {
                                $existingImages[] = '/uploads/associate_properties/' . $fileName;
                            }
                        }
                    }
                }
            }
            $data['image'] = $existingImages[0] ?? null;
            $data['metadata'] = json_encode(['images' => $existingImages, 'facing' => $facingUp, 'amenities' => $amenitiesUp, 'colony_id' => $colonyId]);

            // Validate
            if (empty($colonyId)) throw new Exception('Colony is required');
            if (empty($data['name'])) throw new Exception('Title is required');
            if ($data['price'] <= 0) throw new Exception('Price must be greater than 0');
            if ($data['area_sqft'] <= 0) throw new Exception('Area must be greater than 0');

            // Update property
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            // Check ownership
            $stmt = $db->prepare("SELECT id FROM user_properties WHERE id = ? AND user_id = ?{$tidSql} LIMIT 1");
            $stmt->execute([$id, $userId]);
            if (!$stmt->fetch()) {
                $_SESSION['error'] = 'Property not found or access denied';
                $this->redirect('/associate/properties');
                return;
            }

            // Update
            $cols = [];
            $params = [];
            foreach ($data as $key => $value) {
                $cols[] = "$key = ?";
                $params[] = $value;
            }
            $params[] = $id;
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $sql = "UPDATE user_properties SET " . implode(', ', $cols) . " WHERE id = ?{$tidSql}";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            $this->logActivity($userId, 'property_updated', ['property_id' => $id]);

            $_SESSION['success'] = 'Property updated successfully!';
            $this->redirect('/associate/properties');
        } catch (\Throwable $e) {
            error_log('AssociatePropertyController::updateProperty error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update property: ' . $e->getMessage();
            $this->redirect("/associate/properties/edit/{$id}");
        }
    }

    /**
     * Delete property
     */
    public function deleteProperty($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$id, $userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $stmt = $db->prepare("DELETE FROM user_properties WHERE id = ? AND user_id = ?{$tidSql}");
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $this->logActivity($userId, 'property_deleted', ['property_id' => $id]);
            $_SESSION['success'] = 'Property deleted successfully!';
        } else {
            $_SESSION['error'] = 'Property not found or access denied';
        }

        $this->redirect('/associate/properties');
    }

    /**
     * Browse all properties (associate can see all for reference)
     */
    public function browse()
    {
        $this->requireAuth();
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND up.tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];

        $search = trim($_GET['search'] ?? '');
        $colonyFilter = (int)($_GET['colony_id'] ?? 0);
        $typeFilter = $_GET['property_type'] ?? '';

        $where = "WHERE 1=1";
        if ($search) {
            $where .= " AND (up.name LIKE ? OR up.address LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($typeFilter) {
            $where .= " AND up.property_type = ?";
            $params[] = $typeFilter;
        }

        if (TenantContext::getId() > 1) {
            $where .= " AND up.tenant_id = ?";
            $params[] = TenantContext::getId();
        }

        $sql = "SELECT up.* FROM user_properties up {$where} ORDER BY up.created_at DESC";
        $properties = $db->fetchAll($sql, $params) ?: [];

        // Get colonies for filter
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];
        $colonies = $db->fetchAll("SELECT * FROM colonies WHERE is_active = 1{$tidSql} ORDER BY name", $params);

        $this->render('associate/browse', [
            'page_title' => 'Browse Properties - Associate Portal',
            'page_description' => 'Browse all listed properties',
            'properties' => $properties,
            'colonies' => $colonies,
            'search' => $search,
            'colony_filter' => $colonyFilter,
            'type_filter' => $typeFilter,
        ], 'layouts/associate');
    }

    /**
     * Sold properties
     */
    public function sold()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $sql = "SELECT up.* FROM user_properties up WHERE up.user_id = ? AND up.listing_type = 'sell' AND up.status = 'sold'{$tidSql} ORDER BY up.updated_at DESC";
        $params = array_merge([$userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
        $properties = $db->fetchAll($sql, $params) ?: [];

        $this->render('associate/sold', [
            'page_title' => 'Sold Properties - Associate Portal',
            'page_description' => 'View your sold properties',
            'properties' => $properties,
        ], 'layouts/associate');
    }

    /**
     * Pending properties
     */
    public function pending()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $sql = "SELECT up.* FROM user_properties up WHERE up.user_id = ? AND up.status = 'pending'{$tidSql} ORDER BY up.created_at DESC";
        $params = array_merge([$userId], TenantContext::getId() > 1 ? [TenantContext::getId()] : []);
        $properties = $db->fetchAll($sql, $params) ?: [];

        $this->render('associate/pending', [
            'page_title' => 'Pending Properties - Associate Portal',
            'page_description' => 'View your pending properties',
            'properties' => $properties,
        ], 'layouts/associate');
    }
}