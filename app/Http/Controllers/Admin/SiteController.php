<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Site Controller - Custom MVC Implementation
 * Handles site management operations in the Admin panel
 */
class SiteController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * List all sites
     */
    public function index()
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT s.*, 
                           COUNT(p.id) as property_count,
                           COUNT(pr.id) as project_count,
                           COUNT(pl.id) as plot_count,
                           COALESCE(SUM(p.price), 0) as total_property_value
                    FROM sites s
                    LEFT JOIN properties p ON s.id = p.site_id
                    LEFT JOIN projects pr ON s.id = pr.site_id
                    LEFT JOIN plots pl ON s.id = pl.site_id
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (s.site_name LIKE ? OR s.location LIKE ? OR s.description LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $sql .= " AND s.status = ?";
                $params[] = $status;
            }

            if (!empty($type)) {
                $sql .= " AND s.site_type = ?";
                $params[] = $type;
            }

            $sql .= " GROUP BY s.id ORDER BY s.created_at DESC";

            // Count total
            $countSql = str_replace("SELECT s.*, COUNT(p.id) as property_count, COUNT(pr.id) as project_count, COUNT(pl.id) as plot_count, COALESCE(SUM(p.price), 0) as total_property_value", "SELECT COUNT(DISTINCT s.id) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $sites = $stmt->fetchAll();

            $data = [
                'page_title' => 'Site Management - APS Dream Home',
                'active_page' => 'sites',
                'sites' => $sites,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type
                ]
            ];

            return $this->render('admin/sites/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Site Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sites');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show the form for creating a new site
     */
    public function create()
    {
        try {
            $data = [
                'page_title' => 'Create Site - APS Dream Home',
                'active_page' => 'sites'
            ];

            return $this->render('admin/sites/create', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Site Create error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load site form');
            return $this->redirect('admin/sites');
        }
    }

    /**
     * Store a newly created site
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['site_name', 'location', 'site_type'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate numeric fields
            $totalArea = (float)($data['total_area'] ?? 0);

            if ($totalArea <= 0) {
                return $this->jsonError('Total area must be greater than 0', 400);
            }

            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if (!$imageValidation['valid']) {
                    return $this->jsonError($imageValidation['error'], 400);
                }
                $imagePath = $this->uploadImage($_FILES['image']);
                if (!$imagePath) {
                    return $this->jsonError('Failed to upload image', 500);
                }
            }

            // Insert site
            $sql = "INSERT INTO sites 
                    (site_name, location, site_type, total_area, description, 
                     address, city, state, pincode, contact_person, contact_email, contact_phone,
                     latitude, longitude, image, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['site_name'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['location'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['site_type'], 'string'),
                $totalArea,
                CoreFunctionsServiceCustom::validateInput($data['description'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['address'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['city'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['state'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['pincode'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['contact_person'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['contact_email'] ?? '', 'string'),
                CoreFunctionsServiceCustom::validateInput($data['contact_phone'] ?? '', 'string'),
                !empty($data['latitude']) ? (float)$data['latitude'] : null,
                !empty($data['longitude']) ? (float)$data['longitude'] : null,
                $imagePath
            ]);

            if ($result) {
                $siteId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'site_created', [
                    'site_id' => $siteId,
                    'site_name' => $data['site_name']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site created successfully',
                    'site_id' => $siteId
                ]);
            }

            // Clean up uploaded image if database insert failed
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }

            return $this->jsonError('Failed to create site', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Site Store error: " . $e->getMessage());
            return $this->jsonError('Failed to create site', 500);
        }
    }

    /**
     * Display the specified site
     */
    public function show($id)
    {
        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                $this->setFlash('error', 'Invalid site ID');
                return $this->redirect('admin/sites');
            }

            // Get site details
            $sql = "SELECT s.*, 
                           COUNT(p.id) as property_count,
                           COUNT(pr.id) as project_count,
                           COUNT(pl.id) as plot_count,
                           COALESCE(SUM(p.price), 0) as total_property_value
                    FROM sites s
                    LEFT JOIN properties p ON s.id = p.site_id
                    LEFT JOIN projects pr ON s.id = pr.site_id
                    LEFT JOIN plots pl ON s.id = pl.site_id
                    WHERE s.id = ?
                    GROUP BY s.id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $site = $stmt->fetch();

            if (!$site) {
                $this->setFlash('error', 'Site not found');
                return $this->redirect('admin/sites');
            }

            // Get properties in this site
            $sql = "SELECT p.*, 
                           b.booking_number,
                           c.name as customer_name
                    FROM properties p
                    LEFT JOIN bookings b ON p.id = b.property_id
                    LEFT JOIN users c ON b.customer_id = c.id
                    WHERE p.site_id = ?
                    ORDER BY p.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $properties = $stmt->fetchAll();

            // Get projects in this site
            $sql = "SELECT * FROM projects WHERE site_id = ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $projects = $stmt->fetchAll();

            // Get plots in this site
            $sql = "SELECT pl.*, l.land_title FROM plots pl LEFT JOIN land_records l ON pl.land_id = l.id WHERE pl.site_id = ? ORDER BY pl.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $plots = $stmt->fetchAll();

            $data = [
                'page_title' => 'Site Details - APS Dream Home',
                'active_page' => 'sites',
                'site' => $site,
                'properties' => $properties,
                'projects' => $projects,
                'plots' => $plots
            ];

            return $this->render('admin/sites/show', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Site Show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load site details');
            return $this->redirect('admin/sites');
        }
    }

    /**
     * Show the form for editing the specified site
     */
    public function edit($id)
    {
        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                $this->setFlash('error', 'Invalid site ID');
                return $this->redirect('admin/sites');
            }

            // Get site details
            $sql = "SELECT * FROM sites WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $site = $stmt->fetch();

            if (!$site) {
                $this->setFlash('error', 'Site not found');
                return $this->redirect('admin/sites');
            }

            $data = [
                'page_title' => 'Edit Site - APS Dream Home',
                'active_page' => 'sites',
                'site' => $site
            ];

            return $this->render('admin/sites/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Site Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load site form');
            return $this->redirect('admin/sites');
        }
    }

    /**
     * Update the specified site
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                return $this->jsonError('Invalid site ID', 400);
            }

            $data = $_POST;

            // Check if site exists
            $sql = "SELECT * FROM sites WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $site = $stmt->fetch();

            if (!$site) {
                return $this->jsonError('Site not found', 404);
            }

            // Handle image upload
            $imagePath = $site['image']; // Keep existing image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageValidation = $this->validateImage($_FILES['image']);
                if (!$imageValidation['valid']) {
                    return $this->jsonError($imageValidation['error'], 400);
                }

                $newImagePath = $this->uploadImage($_FILES['image']);
                if (!$newImagePath) {
                    return $this->jsonError('Failed to upload image', 500);
                }

                // Delete old image if exists
                if ($site['image'] && file_exists($site['image'])) {
                    unlink($site['image']);
                }

                $imagePath = $newImagePath;
            }

            // Build update query
            $updateFields = [];
            $updateValues = [];

            if (!empty($data['site_name'])) {
                $updateFields[] = "site_name = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['site_name'], 'string');
            }

            if (!empty($data['location'])) {
                $updateFields[] = "location = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['location'], 'string');
            }

            if (!empty($data['site_type'])) {
                $updateFields[] = "site_type = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['site_type'], 'string');
            }

            if (isset($data['total_area'])) {
                $totalArea = (float)$data['total_area'];
                if ($totalArea <= 0) {
                    return $this->jsonError('Total area must be greater than 0', 400);
                }
                $updateFields[] = "total_area = ?";
                $updateValues[] = $totalArea;
            }

            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['description'], 'string');
            }

            if (isset($data['address'])) {
                $updateFields[] = "address = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['address'], 'string');
            }

            if (isset($data['city'])) {
                $updateFields[] = "city = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['city'], 'string');
            }

            if (isset($data['state'])) {
                $updateFields[] = "state = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['state'], 'string');
            }

            if (isset($data['pincode'])) {
                $updateFields[] = "pincode = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['pincode'], 'string');
            }

            if (isset($data['contact_person'])) {
                $updateFields[] = "contact_person = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['contact_person'], 'string');
            }

            if (isset($data['contact_email'])) {
                $updateFields[] = "contact_email = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['contact_email'], 'string');
            }

            if (isset($data['contact_phone'])) {
                $updateFields[] = "contact_phone = ?";
                $updateValues[] = CoreFunctionsServiceCustom::validateInput($data['contact_phone'], 'string');
            }

            if (isset($data['latitude'])) {
                $updateFields[] = "latitude = ?";
                $updateValues[] = !empty($data['latitude']) ? (float)$data['latitude'] : null;
            }

            if (isset($data['longitude'])) {
                $updateFields[] = "longitude = ?";
                $updateValues[] = !empty($data['longitude']) ? (float)$data['longitude'] : null;
            }

            if (isset($data['status'])) {
                $validStatuses = ['active', 'inactive', 'under_construction', 'completed'];
                if (in_array($data['status'], $validStatuses)) {
                    $updateFields[] = "status = ?";
                    $updateValues[] = $data['status'];
                }
            }

            $updateFields[] = "image = ?";
            $updateValues[] = $imagePath;
            $updateFields[] = "updated_at = NOW()";
            $updateValues[] = $siteId;

            $sql = "UPDATE sites SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($updateValues);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'site_updated', [
                    'site_id' => $siteId,
                    'changes' => $data
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update site', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Site Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update site', 500);
        }
    }

    /**
     * Remove the specified site
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $siteId = intval($id);
            if ($siteId <= 0) {
                return $this->jsonError('Invalid site ID', 400);
            }

            // Check if site exists
            $sql = "SELECT * FROM sites WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $site = $stmt->fetch();

            if (!$site) {
                return $this->jsonError('Site not found', 404);
            }

            // Check if site has properties
            $sql = "SELECT COUNT(*) as property_count FROM properties WHERE site_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $propertyCount = $stmt->fetch()['property_count'];

            if ($propertyCount > 0) {
                return $this->jsonError('Cannot delete site with existing properties', 400);
            }

            // Check if site has projects
            $sql = "SELECT COUNT(*) as project_count FROM projects WHERE site_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$siteId]);
            $projectCount = $stmt->fetch()['project_count'];

            if ($projectCount > 0) {
                return $this->jsonError('Cannot delete site with existing projects', 400);
            }

            // Delete image if exists
            if ($site['image'] && file_exists($site['image'])) {
                unlink($site['image']);
            }

            // Delete site
            $sql = "DELETE FROM sites WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$siteId]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'site_deleted', [
                    'site_id' => $siteId,
                    'site_name' => $site['site_name']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site deleted successfully'
                ]);
            }

            return $this->jsonError('Failed to delete site', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Site Destroy error: " . $e->getMessage());
            return $this->jsonError('Failed to delete site', 500);
        }
    }

    /**
     * Validate uploaded image
     */
    private function validateImage(array $file): array
    {
        // Check file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Image size too large. Maximum 5MB allowed.'];
        }

        // Check image type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid image type. Allowed types: JPG, PNG, GIF, WebP'];
        }

        return ['valid' => true];
    }

    /**
     * Upload image
     */
    private function uploadImage(array $file): ?string
    {
        try {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('site_') . '.' . $extension;

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/sites/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filePath = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                return $filePath;
            }

            return null;
        } catch (Exception $e) {
            $this->loggingService->error("Upload Image error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get site statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total sites
            $sql = "SELECT COUNT(*) as total FROM sites";
            $result = $this->db->fetchOne($sql);
            $stats['total_sites'] = (int)($result['total'] ?? 0);

            // Active sites
            $sql = "SELECT COUNT(*) as total FROM sites WHERE status = 'active'";
            $result = $this->db->fetchOne($sql);
            $stats['active_sites'] = (int)($result['total'] ?? 0);

            // Total area
            $sql = "SELECT COALESCE(SUM(total_area), 0) as total FROM sites";
            $result = $this->db->fetchOne($sql);
            $stats['total_area'] = (float)($result['total'] ?? 0);

            // Sites by type
            $sql = "SELECT site_type, COUNT(*) as count FROM sites GROUP BY site_type";
            $stats['by_type'] = $this->db->fetchAll($sql) ?: [];

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Site Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch site stats'
            ], 500);
        }
    }
}
