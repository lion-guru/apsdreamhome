<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use Exception;


/**
 * Admin PropertyController
 * Handles admin CRUD operations for properties
 */
class PropertyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    /**
     * List all properties for admin
     */
    public function index()
    {
        try {
            // Get query parameters
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 10);
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            $sort = $_GET['sort'] ?? 'newest';

            // Calculate offset
            $offset = ($page - 1) * $perPage;

            // Build query
            $query = $this->db->table('properties');
            $countQuery = $this->db->table('properties');

            // Apply filters
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%')
                        ->orWhere('location', 'LIKE', '%' . $search . '%');
                });
                $countQuery->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%')
                        ->orWhere('location', 'LIKE', '%' . $search . '%');
                });
            }

            if (!empty($status)) {
                $query->where('status', $status);
                $countQuery->where('status', $status);
            }

            if (!empty($type)) {
                $query->where('type', $type);
                $countQuery->where('type', $type);
            }

            // Apply sorting
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'featured':
                    $query->orderBy('featured', 'desc')->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            // Get total count
            $totalProperties = $countQuery->count();

            // Get paginated results
            $properties = $query->limit($perPage)->offset($offset)->get();

            // Calculate pagination info
            $totalPages = ceil($totalProperties / $perPage);

            $this->render('admin/properties/index', [
                'page_title' => 'Property Management - APS Dream Home Admin',
                'page_description' => 'Manage all properties in the system',
                'properties' => $properties,
                'total_properties' => $totalProperties,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type,
                    'sort' => $sort
                ]
            ], 'layouts/base');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load properties: ' . $e->getMessage());
            $this->render('admin/properties/index', [
                'page_title' => 'Property Management - APS Dream Home Admin',
                'properties' => [],
                'total_properties' => 0,
                'current_page' => 1,
                'total_pages' => 1,
                'filters' => []
            ], 'layouts/base');
        }
    }

    /**
     * Show create property form
     */
    public function create()
    {
        $this->render('admin/properties/create', [
            'page_title' => 'Add New Property - APS Dream Home Admin',
            'page_description' => 'Create a new property listing'
        ], 'layouts/base');
    }

    /**
     * Store new property
     */
    public function store()
    {
        try {
            $this->validateCsrfToken($_POST['csrf_token'] ?? '');

            // Get form data
            $title = trim(Security::sanitize($_POST['title']) ?? '');
            $description = trim(Security::sanitize($_POST['description']) ?? '');
            $price = (float)(Security::sanitize($_POST['price']) ?? 0);
            $area = (float)(Security::sanitize($_POST['area']) ?? 0);
            $bedrooms = (int)(Security::sanitize($_POST['bedrooms']) ?? 0);
            $bathrooms = (int)(Security::sanitize($_POST['bathrooms']) ?? 0);
            $location = trim(Security::sanitize($_POST['location']) ?? '');
            $type = Security::sanitize($_POST['type']) ?? 'residential';
            $status = Security::sanitize($_POST['status']) ?? 'active';
            $featured = isset($_POST['featured']) ? 1 : 0;

            // Amenities as array
            $amenities = [];
            if (!empty(Security::sanitize($_POST['amenities']))) {
                $amenities = is_array(Security::sanitize($_POST['amenities'])) ? Security::sanitize($_POST['amenities']) : [Security::sanitize($_POST['amenities'])];
            }

            // Add custom amenities
            if (!empty(Security::sanitize($_POST['custom_amenities']))) {
                $customAmenities = array_map('trim', explode(',', Security::sanitize($_POST['custom_amenities'])));
                $amenities = array_merge($amenities, $customAmenities);
            }

            // Validate required fields
            if (empty($title) || empty($location) || $price <= 0) {
                throw new Exception('Title, location, and price are required. Price must be greater than 0.');
            }

            // Validate property type
            $validTypes = ['residential', 'commercial', 'land', 'luxury'];
            if (!in_array($type, $validTypes)) {
                throw new Exception('Invalid property type selected.');
            }

            // Validate status
            $validStatuses = ['active', 'sold', 'inactive'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid status selected.');
            }

            // Handle image uploads
            $uploadedImages = [];
            if (!empty($_FILES['images']['name'][0])) {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Utils/ImageUpload.php';
                $imageUpload = new \App\Utils\ImageUpload();

                $uploadResults = $imageUpload->uploadMultipleImages($_FILES['images']);
                $uploadedImages = array_filter($uploadResults, function ($result) {
                    return $result['success'] ?? false;
                });

                // Extract filenames for database storage
                $imageFilenames = array_column($uploadedImages, 'filename');
            }

            // Insert property
            $propertyData = [
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'area' => $area,
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'location' => $location,
                'type' => $type,
                'status' => $status,
                'featured' => $featured,
                'amenities' => json_encode(array_unique($amenities)),
                'images' => json_encode($imageFilenames),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $propertyId = $this->db->table('properties')->insert($propertyData);

            if (!$propertyId) {
                // Clean up uploaded images if property creation failed
                foreach ($uploadedImages as $image) {
                    $imageUpload->deleteImage($image['filename']);
                }
                throw new Exception('Failed to create property. Please try again.');
            }

            // Log activity
            $this->logActivity('Property created: ' . $title);

            $this->setFlash('success', 'Property created successfully!');
            $this->redirect('/admin/properties');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/admin/properties/create');
        }
    }

    /**
     * Show edit property form
     */
    public function edit($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new Exception('Invalid property ID');
            }

            $property = $this->db->table('properties')->where('id', $id)->first();

            if (!$property) {
                throw new Exception('Property not found');
            }

            // Parse amenities
            $amenities = [];
            if (!empty($property['amenities'])) {
                $amenities = json_decode($property['amenities'], true) ?? [];
            }

            $this->render('admin/properties/edit', [
                'page_title' => 'Edit Property - APS Dream Home Admin',
                'page_description' => 'Edit property details',
                'property' => $property,
                'amenities' => $amenities
            ], 'layouts/base');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/admin/properties');
        }
    }

    /**
     * Update property
     */
    public function update($id)
    {
        try {
            $this->validateCsrfToken($_POST['csrf_token'] ?? '');

            if (!$id || !is_numeric($id)) {
                throw new Exception('Invalid property ID');
            }

            // Get form data
            $title = trim(Security::sanitize($_POST['title']) ?? '');
            $description = trim(Security::sanitize($_POST['description']) ?? '');
            $price = (float)(Security::sanitize($_POST['price']) ?? 0);
            $area = (float)(Security::sanitize($_POST['area']) ?? 0);
            $bedrooms = (int)(Security::sanitize($_POST['bedrooms']) ?? 0);
            $bathrooms = (int)(Security::sanitize($_POST['bathrooms']) ?? 0);
            $location = trim(Security::sanitize($_POST['location']) ?? '');
            $type = Security::sanitize($_POST['type']) ?? 'residential';
            $status = Security::sanitize($_POST['status']) ?? 'active';
            $featured = isset($_POST['featured']) ? 1 : 0;

            // Amenities as array
            $amenities = [];
            if (!empty(Security::sanitize($_POST['amenities']))) {
                $amenities = is_array(Security::sanitize($_POST['amenities'])) ? Security::sanitize($_POST['amenities']) : [Security::sanitize($_POST['amenities'])];
            }

            // Validate required fields
            if (empty($title) || empty($location) || $price <= 0) {
                throw new Exception('Title, location, and price are required. Price must be greater than 0.');
            }

            // Update property
            $updated = $this->db->table('properties')
                ->where('id', $id)
                ->update([
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'area' => $area,
                    'bedrooms' => $bedrooms,
                    'bathrooms' => $bathrooms,
                    'location' => $location,
                    'type' => $type,
                    'status' => $status,
                    'featured' => $featured,
                    'amenities' => json_encode($amenities),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            if ($updated === false) {
                throw new Exception('Failed to update property. Please try again.');
            }

            // Log activity
            $this->logActivity('Property updated: ' . $title);

            $this->setFlash('success', 'Property updated successfully!');
            $this->redirect('/admin/properties');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/admin/properties/edit/' . $id);
        }
    }

    /**
     * Delete property
     */
    public function destroy($id)
    {
        try {
            $this->validateCsrfToken($_POST['csrf_token'] ?? '');

            if (!$id || !is_numeric($id)) {
                throw new Exception('Invalid property ID');
            }

            $property = $this->db->table('properties')->where('id', $id)->first();

            if (!$property) {
                throw new Exception('Property not found');
            }

            // Delete property
            $deleted = $this->db->table('properties')->where('id', $id)->delete();

            if (!$deleted) {
                throw new Exception('Failed to delete property. Please try again.');
            }

            // Log activity
            $this->logActivity('Property deleted: ' . $property['title']);

            $this->setFlash('success', 'Property deleted successfully!');
            $this->redirect('/admin/properties');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/admin/properties');
        }
    }

    /**
     * Toggle property featured status
     */
    public function toggleFeatured($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new Exception('Invalid property ID');
            }

            $property = $this->db->table('properties')->where('id', $id)->first();

            if (!$property) {
                throw new Exception('Property not found');
            }

            $newStatus = $property['featured'] ? 0 : 1;

            $this->db->table('properties')
                ->where('id', $id)
                ->update([
                    'featured' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $action = $newStatus ? 'marked as featured' : 'unmarked as featured';
            $this->logActivity('Property ' . $action . ': ' . $property['title']);

            echo json_encode(['success' => true, 'featured' => $newStatus]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Require admin access
     */
    private function requireAdmin()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        // Check if user is admin (you can implement proper role checking)
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            $this->setFlash('error', 'Access denied. Admin privileges required.');
            $this->redirect('/dashboard');
            return;
        }
    }

    /**
     * Log admin activity
     */
    private function logActivity($description)
    {
        try {
            $this->db->table('user_activity_log')->insert([
                'user_id' => $_SESSION['user_id'],
                'action' => 'admin_property',
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Don't throw error for logging failures
        }
    }
}
