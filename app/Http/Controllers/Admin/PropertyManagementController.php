<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Core\Security;
use Exception;

class PropertyManagementController extends BaseController
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * List properties with site integration
     */
    public function index()
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $type = $_GET['type'] ?? '';
            $siteId = intval($_GET['site_id'] ?? 0);
            
            $offset = ($page - 1) * 10;
            $where = ["1=1"];
            $params = [];

            if ($siteId > 0) {
                $where[] = "p.site_id = :site_id";
                $params['site_id'] = $siteId;
            }

            if (!empty($search)) {
                $where[] = "(p.title LIKE :search OR p.location LIKE :search OR p.description LIKE :search)";
                $params['search'] = '%' . $search . '%';
            }

            if (!empty($status)) {
                $where[] = "p.status = :status";
                $params['status'] = $status;
            }

            if (!empty($type)) {
                $where[] = "p.type = :type";
                $params['type'] = $type;
            }

            $whereClause = implode(' AND ', $where);

            // Get properties with site information
            $sql = "SELECT p.*, s.site_name, s.location as site_location, s.city as site_city
                    FROM properties p
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE $whereClause
                    ORDER BY p.created_at DESC
                    LIMIT :offset, :limit";

            $params['offset'] = $offset;
            $params['limit'] = 10;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll();

            // Get total count
            $countSql = str_replace("SELECT p.*, s.site_name, s.location as site_location, s.city as site_city", "SELECT COUNT(*)", $sql);
            $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
            $countSql = preg_replace('/LIMIT.*$/', '', $countSql);
            
            $countParams = $params;
            unset($countParams['offset'], $countParams['limit']);
            
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch()['total'];

            // Get sites for dropdown
            $sites = $this->db->fetchAll("SELECT id, site_name, location FROM sites ORDER BY site_name");

            return $this->render('admin/properties/index', [
                'properties' => $properties,
                'sites' => $sites,
                'total' => $total,
                'current_page' => $page,
                'total_pages' => ceil($total / 10),
                'filters' => ['search' => $search, 'status' => $status, 'type' => $type, 'site_id' => $siteId]
            ]);

        } catch (Exception $e) {
            error_log("Property listing error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load properties');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show add property form
     */
    public function create($siteId = null)
    {
        try {
            $siteId = $siteId ? intval($siteId) : intval($_GET['site_id'] ?? 0);
            $sites = $this->db->fetchAll("SELECT id, site_name, location FROM sites ORDER BY site_name");
            $propertyTypes = $this->db->fetchAll("SELECT * FROM property_types ORDER BY name");
            
            return $this->render('admin/properties/create', [
                'sites' => $sites,
                'property_types' => $propertyTypes,
                'selected_site_id' => $siteId,
                'page_title' => 'Add New Property - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Property create form error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property form');
            return $this->redirect('admin/properties');
        }
    }

    /**
     * Store new property
     */
    public function store()
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/properties');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/properties');
        }

        try {
            $data = $this->post();
            
            $siteId = intval($data['site_id'] ?? 0);
            $title = trim($data['title'] ?? '');
            $description = trim($data['description'] ?? '');
            $price = floatval($data['price'] ?? 0);
            $location = trim($data['location'] ?? '');
            $type = $data['type'] ?? 'apartment';
            $status = $data['status'] ?? 'active';
            $city = trim($data['city'] ?? '');
            $state = trim($data['state'] ?? '');
            $pincode = trim($data['pincode'] ?? '');
            $bedrooms = intval($data['bedrooms'] ?? 0);
            $bathrooms = intval($data['bathrooms'] ?? 0);
            $area = floatval($data['area'] ?? 0);
            $areaUnit = trim($data['area_unit'] ?? 'sqft');
            $features = trim($data['features'] ?? '');
            $amenities = trim($data['amenities'] ?? '');
            $latitude = floatval($data['latitude'] ?? 0);
            $longitude = floatval($data['longitude'] ?? 0);
            $featured = intval($data['featured'] ?? 0);

            // Validation
            if ($siteId <= 0 || empty($title) || $price <= 0 || empty($location)) {
                $this->setFlash('error', 'Please fill in all required fields');
                return $this->redirect('admin/properties/create');
            }

            // Check if site exists
            $site = $this->db->fetchOne("SELECT id FROM sites WHERE id = ? LIMIT 1", [$siteId]);
            if (!$site) {
                $this->setFlash('error', 'Invalid site selected');
                return $this->redirect('admin/properties/create');
            }

            $sql = "INSERT INTO properties (site_id, title, description, price, location, type, status,
                           city, state, pincode, bedrooms, bathrooms, area, area_unit, features, amenities,
                           latitude, longitude, featured, created_at, updated_at)
                    VALUES (:site_id, :title, :description, :price, :location, :type, :status,
                           :city, :state, :pincode, :bedrooms, :bathrooms, :area, :area_unit, :features, :amenities,
                           :latitude, :longitude, :featured, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'site_id' => $siteId,
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'location' => $location,
                'type' => $type,
                'status' => $status,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode,
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'area' => $area,
                'area_unit' => $areaUnit,
                'features' => $features,
                'amenities' => $amenities,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'featured' => $featured
            ]);

            if ($success) {
                $propertyId = (int)$this->db->lastInsertId();
                
                // Handle image uploads if any
                $this->handlePropertyImages($propertyId, $_FILES['images'] ?? []);
                
                $this->setFlash('success', 'Property added successfully');
                return $this->redirect('admin/properties?site_id=' . $siteId);
            } else {
                $this->setFlash('error', 'Failed to add property');
                return $this->redirect('admin/properties/create');
            }

        } catch (Exception $e) {
            error_log("Property creation error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to add property');
            return $this->redirect('admin/properties/create');
        }
    }

    /**
     * Show property details
     */
    public function show($id)
    {
        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                $this->setFlash('error', 'Invalid property ID');
                return $this->redirect('admin/properties');
            }

            // Get property details with site information
            $sql = "SELECT p.*, s.site_name, s.location as site_location, s.city as site_city
                    FROM properties p
                    LEFT JOIN sites s ON p.site_id = s.id
                    WHERE p.id = :property_id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['property_id' => $propertyId]);
            $property = $stmt->fetch();

            if (!$property) {
                $this->setFlash('error', 'Property not found');
                return $this->redirect('admin/properties');
            }

            // Get property images
            $imagesSql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order";
            $imagesStmt = $this->db->prepare($imagesSql);
            $imagesStmt->execute([$propertyId]);
            $images = $imagesStmt->fetchAll();

            // Get booking history for this property
            $bookingsSql = "SELECT b.*, u.name as customer_name, u.email as customer_email
                           FROM bookings b
                           LEFT JOIN users u ON b.customer_id = u.id
                           WHERE b.property_id = :property_id
                           ORDER BY b.created_at DESC";
            
            $bookingsStmt = $this->db->prepare($bookingsSql);
            $bookingsStmt->execute(['property_id' => $propertyId]);
            $bookings = $bookingsStmt->fetchAll();

            return $this->render('admin/properties/show', [
                'property' => $property,
                'images' => $images,
                'bookings' => $bookings,
                'page_title' => 'Property Details - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Property show error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property details');
            return $this->redirect('admin/properties');
        }
    }

    /**
     * Show edit property form
     */
    public function edit($id)
    {
        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                $this->setFlash('error', 'Invalid property ID');
                return $this->redirect('admin/properties');
            }

            $property = $this->db->fetchOne("SELECT * FROM properties WHERE id = ? LIMIT 1", [$propertyId]);
            
            if (!$property) {
                $this->setFlash('error', 'Property not found');
                return $this->redirect('admin/properties');
            }

            $sites = $this->db->fetchAll("SELECT id, site_name, location FROM sites ORDER BY site_name");
            $propertyTypes = $this->db->fetchAll("SELECT * FROM property_types ORDER BY name");

            return $this->render('admin/properties/edit', [
                'property' => $property,
                'sites' => $sites,
                'property_types' => $propertyTypes,
                'page_title' => 'Edit Property - APS Dream Home'
            ]);

        } catch (Exception $e) {
            error_log("Property edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property for editing');
            return $this->redirect('admin/properties');
        }
    }

    /**
     * Update property
     */
    public function update($id)
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/properties');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/properties');
        }

        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                $this->setFlash('error', 'Invalid property ID');
                return $this->redirect('admin/properties');
            }

            $property = $this->db->fetchOne("SELECT id FROM properties WHERE id = ? LIMIT 1", [$propertyId]);
            if (!$property) {
                $this->setFlash('error', 'Property not found');
                return $this->redirect('admin/properties');
            }

            $data = $this->post();
            
            $sql = "UPDATE properties 
                    SET site_id = :site_id, title = :title, description = :description, price = :price,
                        location = :location, type = :type, status = :status, city = :city, state = :state,
                        pincode = :pincode, bedrooms = :bedrooms, bathrooms = :bathrooms, area = :area,
                        area_unit = :area_unit, features = :features, amenities = :amenities,
                        latitude = :latitude, longitude = :longitude, featured = :featured, updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'site_id' => intval($data['site_id']),
                'title' => trim($data['title']),
                'description' => trim($data['description']),
                'price' => floatval($data['price']),
                'location' => trim($data['location']),
                'type' => $data['type'],
                'status' => $data['status'],
                'city' => trim($data['city']),
                'state' => trim($data['state']),
                'pincode' => trim($data['pincode']),
                'bedrooms' => intval($data['bedrooms']),
                'bathrooms' => intval($data['bathrooms']),
                'area' => floatval($data['area']),
                'area_unit' => trim($data['area_unit']),
                'features' => trim($data['features']),
                'amenities' => trim($data['amenities']),
                'latitude' => floatval($data['latitude']),
                'longitude' => floatval($data['longitude']),
                'featured' => intval($data['featured']),
                'id' => $propertyId
            ]);

            if ($success) {
                // Handle image updates if any
                $this->handlePropertyImages($propertyId, $_FILES['images'] ?? []);
                
                $this->setFlash('success', 'Property updated successfully');
                return $this->redirect('admin/properties');
            } else {
                $this->setFlash('error', 'Failed to update property');
                return $this->redirect("admin/properties/{$propertyId}/edit");
            }

        } catch (Exception $e) {
            error_log("Property update error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update property');
            return $this->redirect("admin/properties/{$id}/edit");
        }
    }

    /**
     * Delete property
     */
    public function destroy($id)
    {
        if ($this->method() !== 'POST') {
            $this->setFlash('error', 'Invalid request method');
            return $this->redirect('admin/properties');
        }

        if (!$this->validateCsrfTokenLocal()) {
            $this->setFlash('error', 'Security validation failed');
            return $this->redirect('admin/properties');
        }

        try {
            $propertyId = intval($id);
            if ($propertyId <= 0) {
                $this->setFlash('error', 'Invalid property ID');
                return $this->redirect('admin/properties');
            }

            $property = $this->db->fetchOne("SELECT * FROM properties WHERE id = ? LIMIT 1", [$propertyId]);
            if (!$property) {
                $this->setFlash('error', 'Property not found');
                return $this->redirect('admin/properties');
            }

            // Check if property has bookings
            $bookingCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM bookings WHERE property_id = ?", [$propertyId])['count'];

            if ($bookingCount > 0) {
                $this->setFlash('error', 'Cannot delete property with existing bookings');
                return $this->redirect('admin/properties');
            }

            // Start transaction for safe deletion
            $this->db->beginTransaction();

            try {
                // Delete property images
                $this->db->prepare("DELETE FROM property_images WHERE property_id = ?")->execute([$propertyId]);
                
                // Delete property
                $this->db->prepare("DELETE FROM properties WHERE id = ?")->execute([$propertyId]);

                $this->db->commit();
                $this->setFlash('success', 'Property deleted successfully');

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

            return $this->redirect('admin/properties');

        } catch (Exception $e) {
            error_log("Property deletion error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to delete property');
            return $this->redirect('admin/properties');
        }
    }

    /**
     * Handle property image uploads
     */
    private function handlePropertyImages(int $propertyId, array $images): void
    {
        try {
            if (empty($images) || !isset($images['name'])) {
                return;
            }

            $uploadDir = __DIR__ . '/../../../uploads/properties/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imageCount = count($images['name']);
            for ($i = 0; $i < $imageCount; $i++) {
                if ($images['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = time() . '_' . basename($images['name'][$i]);
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($images['tmp_name'][$i], $targetPath)) {
                        // Insert image record
                        $sql = "INSERT INTO property_images (property_id, image_name, image_path, sort_order)
                                VALUES (?, ?, ?, ?)";
                        
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([$propertyId, $fileName, 'uploads/properties/' . $fileName, $i + 1]);
                    }
                }
            }

        } catch (Exception $e) {
            error_log("Property image upload error: " . $e->getMessage());
        }
    }

    /**
     * Check property availability
     */
    public function checkAvailability()
    {
        try {
            $siteId = intval($_GET['site_id'] ?? 0);
            $propertyId = intval($_GET['property_id'] ?? 0);

            if ($siteId > 0) {
                // Get available properties for site
                $sql = "SELECT id, title, price, location, type, bedrooms, bathrooms, area
                        FROM properties 
                        WHERE site_id = :site_id AND status = 'active'
                        ORDER BY title";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['site_id' => $siteId]);
                $properties = $stmt->fetchAll();

                return json_encode([
                    'success' => true,
                    'properties' => $properties
                ]);
            } elseif ($propertyId > 0) {
                // Check specific property availability
                $property = $this->db->fetchOne(
                    "SELECT status, price FROM properties WHERE id = ? LIMIT 1", 
                    [$propertyId]
                );

                if ($property) {
                    return json_encode([
                        'success' => true,
                        'available' => $property['status'] === 'active',
                        'price' => $property['price']
                    ]);
                } else {
                    return json_encode([
                        'success' => false,
                        'message' => 'Property not found'
                    ]);
                }
            }

            return json_encode([
                'success' => false,
                'message' => 'Invalid parameters'
            ]);

        } catch (Exception $e) {
            error_log("Property availability check error: " . $e->getMessage());
            return json_encode([
                'success' => false,
                'message' => 'Failed to check availability'
            ]);
        }
    }
}
