<?php
/**
 * Virtual Tour & AR Controller
 * Handles 3D virtual tours and augmented reality features
 */

namespace App\Controllers;

class VirtualTourController extends BaseController {

    /**
     * Display virtual tour interface
     */
    public function index($property_id) {
        $property = $this->getPropertyDetails($property_id);

        if (!$property) {
            $this->setFlashMessage('error', 'Property not found');
            $this->redirect(BASE_URL . 'properties');
            return;
        }

        $this->data['page_title'] = 'Virtual Tour - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['tour_data'] = $this->getVirtualTourData($property_id);
        $this->data['ar_enabled'] = $this->isAREnabled();

        $this->render('virtual_tour/index');
    }

    /**
     * Get virtual tour data for property
     */
    public function getTourData($property_id) {
        header('Content-Type: application/json');

        $tour_data = $this->getVirtualTourData($property_id);

        sendJsonResponse([
            'success' => true,
            'data' => $tour_data
        ]);
    }

    /**
     * Upload 360° images for virtual tour
     */
    public function uploadPanorama() {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            sendJsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $property_id = $_POST['property_id'] ?? '';
        $panorama_type = $_POST['panorama_type'] ?? 'interior';

        if (empty($property_id) || !isset($_FILES['panorama_image'])) {
            sendJsonResponse(['success' => false, 'error' => 'Property ID and image are required'], 400);
        }

        try {
            $upload_result = $this->uploadPanoramaImage($_FILES['panorama_image'], $property_id, $panorama_type);

            if ($upload_result['success']) {
                // Save panorama data to database
                $this->savePanoramaData($property_id, $upload_result['file_path'], $panorama_type);

                sendJsonResponse([
                    'success' => true,
                    'message' => 'Panorama uploaded successfully',
                    'data' => $upload_result
                ]);
            } else {
                sendJsonResponse(['success' => false, 'error' => $upload_result['error']], 400);
            }

        } catch (\Exception $e) {
            error_log('Panorama upload error: ' . $e->getMessage());
            sendJsonResponse(['success' => false, 'error' => 'Upload failed'], 500);
        }
    }

    /**
     * AR furniture placement
     */
    public function arFurniture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $room_data = json_decode(file_get_contents('php://input'), true);

            if (!$room_data) {
                sendJsonResponse(['success' => false, 'error' => 'Invalid room data'], 400);
            }

            // Process AR furniture placement
            $ar_result = $this->processARFurniture($room_data);

            sendJsonResponse([
                'success' => true,
                'data' => $ar_result
            ]);
        }

        $this->data['page_title'] = 'AR Furniture Placement - ' . APP_NAME;
        $this->data['furniture_catalog'] = $this->getFurnitureCatalog();

        $this->render('virtual_tour/ar_furniture');
    }

    /**
     * Get property details
     */
    private function getPropertyDetails($property_id) {
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND status = 'available'");
            $stmt->execute([$property_id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Property fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get virtual tour data
     */
    private function getVirtualTourData($property_id) {
        try {
            global $pdo;

            // Get panorama images
            $sql = "SELECT * FROM property_panoramas WHERE property_id = ? ORDER BY panorama_type, created_at";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$property_id]);
            $panoramas = $stmt->fetchAll();

            // Get floor plan data
            $sql = "SELECT * FROM property_floor_plans WHERE property_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$property_id]);
            $floor_plans = $stmt->fetchAll();

            // Get room dimensions and hotspots
            $sql = "SELECT * FROM property_hotspots WHERE property_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$property_id]);
            $hotspots = $stmt->fetchAll();

            return [
                'panoramas' => $panoramas,
                'floor_plans' => $floor_plans,
                'hotspots' => $hotspots,
                'property_id' => $property_id
            ];

        } catch (\Exception $e) {
            error_log('Virtual tour data error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Upload panorama image
     */
    private function uploadPanoramaImage($file, $property_id, $panorama_type) {
        try {
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'error' => 'File upload error'];
            }

            // Check file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $allowed_types)) {
                return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, and WebP allowed'];
            }

            // Check file size (max 50MB for 360° images)
            if ($file['size'] > 50 * 1024 * 1024) {
                return ['success' => false, 'error' => 'File too large. Maximum 50MB allowed'];
            }

            // Create directory if not exists
            $upload_dir = UPLOADS_PATH . 'virtual_tours/' . $property_id . '/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $panorama_type . '_' . time() . '_' . uniqid() . '.' . $extension;
            $file_path = $upload_dir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Create thumbnail
                $thumbnail_path = $this->createThumbnail($file_path, $upload_dir . 'thumb_' . $filename);

                return [
                    'success' => true,
                    'file_path' => str_replace(UPLOADS_PATH, '', $file_path),
                    'thumbnail_path' => str_replace(UPLOADS_PATH, '', $thumbnail_path),
                    'filename' => $filename,
                    'file_size' => $file['size']
                ];
            } else {
                return ['success' => false, 'error' => 'Failed to save file'];
            }

        } catch (\Exception $e) {
            error_log('Panorama upload error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Upload failed'];
        }
    }

    /**
     * Save panorama data to database
     */
    private function savePanoramaData($property_id, $file_path, $panorama_type) {
        try {
            global $pdo;

            $sql = "INSERT INTO property_panoramas (property_id, panorama_type, file_path, thumbnail_path, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$property_id, $panorama_type, $file_path, str_replace('.jpg', '_thumb.jpg', $file_path)]);

        } catch (\Exception $e) {
            error_log('Panorama save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create thumbnail for panorama
     */
    private function createThumbnail($source_path, $thumb_path) {
        try {
            // Get image dimensions
            list($width, $height) = getimagesize($source_path);

            // Create thumbnail (square crop from center)
            $thumb_size = 300;
            $thumb_x = ($width - $thumb_size) / 2;
            $thumb_y = ($height - $thumb_size) / 2;

            // Create image resource based on type
            $source_image = null;
            $extension = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));

            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case 'png':
                    $source_image = imagecreatefrompng($source_path);
                    break;
                case 'webp':
                    $source_image = imagecreatefromwebp($source_path);
                    break;
            }

            if (!$source_image) {
                return $source_path; // Return original if thumbnail creation fails
            }

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($thumb_size, $thumb_size);
            imagecopyresampled($thumbnail, $source_image, 0, 0, $thumb_x, $thumb_y, $thumb_size, $thumb_size, $thumb_size, $thumb_size);

            // Save thumbnail
            imagejpeg($thumbnail, $thumb_path, 85);

            // Clean up memory
            imagedestroy($source_image);
            imagedestroy($thumbnail);

            return $thumb_path;

        } catch (\Exception $e) {
            error_log('Thumbnail creation error: ' . $e->getMessage());
            return $source_path;
        }
    }

    /**
     * Check if AR is enabled (device capability detection)
     */
    private function isAREnabled() {
        // In production, this would detect device capabilities
        // For now, return true if user agent supports AR
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $ar_supported_devices = [
            'iPhone', 'iPad', 'Android'
        ];

        foreach ($ar_supported_devices as $device) {
            if (strpos($user_agent, $device) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process AR furniture placement
     */
    private function processARFurniture($room_data) {
        // Simulate AR furniture placement processing
        // In production, this would use AR libraries

        return [
            'furniture_placed' => $room_data['furniture_items'] ?? [],
            'room_dimensions' => $room_data['room_dimensions'] ?? [],
            'placement_coordinates' => $room_data['coordinates'] ?? [],
            'ar_markers' => $this->generateARMarkers($room_data)
        ];
    }

    /**
     * Generate AR markers for room
     */
    private function generateARMarkers($room_data) {
        // Generate AR markers for furniture placement
        // This would typically use AR marker generation libraries

        return [
            'floor_markers' => [
                ['x' => 0, 'y' => 0, 'type' => 'floor_anchor'],
                ['x' => 100, 'y' => 0, 'type' => 'floor_anchor'],
                ['x' => 0, 'y' => 100, 'type' => 'floor_anchor'],
                ['x' => 100, 'y' => 100, 'type' => 'floor_anchor']
            ],
            'wall_markers' => [
                ['x' => 0, 'y' => 50, 'z' => 0, 'type' => 'wall_anchor'],
                ['x' => 100, 'y' => 50, 'z' => 0, 'type' => 'wall_anchor']
            ]
        ];
    }

    /**
     * Get furniture catalog for AR
     */
    private function getFurnitureCatalog() {
        return [
            'living_room' => [
                ['id' => 1, 'name' => 'Sofa Set', 'model' => 'sofa.glb', 'price' => 45000],
                ['id' => 2, 'name' => 'Center Table', 'model' => 'table.glb', 'price' => 15000],
                ['id' => 3, 'name' => 'TV Unit', 'model' => 'tv_unit.glb', 'price' => 25000]
            ],
            'bedroom' => [
                ['id' => 4, 'name' => 'King Bed', 'model' => 'bed.glb', 'price' => 60000],
                ['id' => 5, 'name' => 'Wardrobe', 'model' => 'wardrobe.glb', 'price' => 35000],
                ['id' => 6, 'name' => 'Dresser', 'model' => 'dresser.glb', 'price' => 20000]
            ],
            'kitchen' => [
                ['id' => 7, 'name' => 'Modular Kitchen', 'model' => 'kitchen.glb', 'price' => 150000],
                ['id' => 8, 'name' => 'Dining Table', 'model' => 'dining.glb', 'price' => 30000]
            ]
        ];
    }

    /**
     * Admin - Manage virtual tours
     */
    public function adminManageTours() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $properties = $this->getPropertiesWithTours();

        $this->data['page_title'] = 'Manage Virtual Tours - ' . APP_NAME;
        $this->data['properties'] = $properties;

        $this->render('admin/virtual_tour_management');
    }

    /**
     * Get properties with virtual tours
     */
    private function getPropertiesWithTours() {
        try {
            global $pdo;

            $sql = "SELECT p.id, p.title, p.city, p.state,
                           COUNT(pp.id) as panorama_count,
                           MAX(pp.created_at) as last_updated
                    FROM properties p
                    LEFT JOIN property_panoramas pp ON p.id = pp.property_id
                    WHERE p.status = 'available'
                    GROUP BY p.id, p.title, p.city, p.state
                    ORDER BY panorama_count DESC, p.created_at DESC";

            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Properties with tours error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create floor plan hotspots
     */
    public function createHotspots($property_id) {
        if (!$this->isAdmin()) {
            sendJsonResponse(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $hotspot_data = json_decode(file_get_contents('php://input'), true);

            if (!$hotspot_data) {
                sendJsonResponse(['success' => false, 'error' => 'Invalid hotspot data'], 400);
            }

            $success = $this->saveHotspotData($property_id, $hotspot_data);

            sendJsonResponse([
                'success' => $success,
                'message' => $success ? 'Hotspot created successfully' : 'Failed to create hotspot'
            ]);
        }

        // Get existing hotspots for editing
        $hotspots = $this->getHotspotData($property_id);

        $this->data['page_title'] = 'Create Hotspots - ' . APP_NAME;
        $this->data['property_id'] = $property_id;
        $this->data['hotspots'] = $hotspots;

        $this->render('virtual_tour/create_hotspots');
    }

    /**
     * Save hotspot data
     */
    private function saveHotspotData($property_id, $hotspot_data) {
        try {
            global $pdo;

            $sql = "INSERT INTO property_hotspots (property_id, room_name, x, y, z, hotspot_type, linked_room, description, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $property_id,
                $hotspot_data['room_name'] ?? '',
                $hotspot_data['x'] ?? 0,
                $hotspot_data['y'] ?? 0,
                $hotspot_data['z'] ?? 0,
                $hotspot_data['hotspot_type'] ?? 'navigation',
                $hotspot_data['linked_room'] ?? null,
                $hotspot_data['description'] ?? ''
            ]);

        } catch (\Exception $e) {
            error_log('Hotspot save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get hotspot data
     */
    private function getHotspotData($property_id) {
        try {
            global $pdo;

            $sql = "SELECT * FROM property_hotspots WHERE property_id = ? ORDER BY room_name, created_at";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$property_id]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Hotspot fetch error: ' . $e->getMessage());
            return [];
        }
    }
}
