<?php
/**
 * Property Favorite Controller
 * Handles property favorites functionality
 */

namespace App\Controllers;

class PropertyFavoriteController extends BaseController {
    protected $pdo;
    
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
        
        // Initialize database connection
        require_once '../../../includes/db_connection.php';
                global $con;
        $this->pdo = $con;
    }

    /**
     * Toggle property favorite status (AJAX endpoint)
     */
    public function toggle() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Please login to add favorites']);
            return;
        }

        // Get POST data
        $property_id = (int)($_POST['property_id'] ?? 0);

        if (!$property_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
            return;
        }

        try {
            // Check if property exists
            if (!$this->propertyExists($property_id)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Property not found']);
                return;
            }

            $user_id = $_SESSION['user_id'];

            // Check if already favorited
            if ($this->isFavorited($user_id, $property_id)) {
                // Remove from favorites
                $this->removeFavorite($user_id, $property_id);
                $is_favorited = false;
                $message = 'Removed from favorites';
            } else {
                // Add to favorites
                $this->addFavorite($user_id, $property_id);
                $is_favorited = true;
                $message = 'Added to favorites';
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'is_favorited' => $is_favorited
            ]);

        } catch (Exception $e) {
            error_log('Favorite toggle error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Get user's favorite properties
     */
    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'My Favorite Properties - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'My Favorites', 'url' => BASE_URL . 'favorites']
        ];

        // Get user's favorites with property details
        $this->data['favorites'] = $this->getUserFavorites($_SESSION['user_id']);

        // Render the favorites page
        $this->render('user/favorites');
    }

    /**
     * Remove favorite (AJAX endpoint)
     */
    public function remove() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Please login']);
            return;
        }

        $property_id = (int)($_POST['property_id'] ?? 0);

        if (!$property_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
            return;
        }

        try {
            $this->removeFavorite($_SESSION['user_id'], $property_id);
            echo json_encode(['success' => true, 'message' => 'Removed from favorites']);
        } catch (Exception $e) {
            error_log('Remove favorite error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Check if property is favorited by user
     */
    private function isFavorited($user_id, $property_id) {
        try {
            if (!$this->pdo) {
                return false;
            }

            $stmt = $this->pdo->prepare("SELECT id FROM property_favorites WHERE user_id = ? AND property_id = ?");
            $stmt->execute([$user_id, $property_id]);
            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            error_log('Check favorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add property to favorites
     */
    private function addFavorite($user_id, $property_id) {
        try {
            if (!$this->pdo) {
                throw new Exception('Database connection not available');
            }

            $stmt = $this->pdo->prepare("INSERT INTO property_favorites (user_id, property_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $property_id]);

        } catch (Exception $e) {
            error_log('Add favorite error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove property from favorites
     */
    private function removeFavorite($user_id, $property_id) {
        try {
            if (!$this->pdo) {
                throw new Exception('Database connection not available');
            }

            $stmt = $this->pdo->prepare("DELETE FROM property_favorites WHERE user_id = ? AND property_id = ?");
            $stmt->execute([$user_id, $property_id]);

        } catch (Exception $e) {
            error_log('Remove favorite error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if property exists
     */
    private function propertyExists($property_id) {
        try {
            if (!$this->pdo) {
                return false;
            }

            $stmt = $this->pdo->prepare("SELECT id FROM properties WHERE id = ? AND status = 'available'");
            $stmt->execute([$property_id]);
            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            error_log('Property exists check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's favorite properties with details
     */
    private function getUserFavorites($user_id) {
        try {
            if (!$this->pdo) {
                return [];
            }

            $sql = "
                SELECT
                    p.id,
                    p.title,
                    p.price,
                    p.city,
                    p.state,
                    p.bedrooms,
                    p.bathrooms,
                    p.area_sqft,
                    p.featured,
                    p.created_at,
                    pt.name as property_type,
                    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as main_image
                FROM property_favorites pf
                JOIN properties p ON pf.property_id = p.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE pf.user_id = ?
                ORDER BY pf.created_at DESC
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Get user favorites error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
