<?php
/**
 * Home Controller - Simplified Version
 * Handles homepage without complex model dependencies
 */

namespace App\Controllers;

use App\Http\Controllers\BaseController;
use Exception;

class HomeControllerSimple extends BaseController {
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display enhanced modern homepage
     */
    public function index() {
        $this->enhanced();
    }

    /**
     * Display enhanced modern homepage
     */
    public function enhanced() {
        // Set page data
        $this->data['page_title'] = 'APS Dream Home - Your Trusted Real Estate Partner';
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL]
        ];

        // Get featured properties (using direct database queries)
        $this->data['featured_properties'] = $this->getFeaturedProperties();

        // Get locations for search dropdown
        $this->data['locations'] = $this->getLocations();

        // Get company statistics
        $this->data['company_stats'] = $this->getCompanyStats();

        // Render the enhanced homepage
        $this->render('pages/homepage_enhanced', [], null);
    }

    /**
     * Get featured properties for homepage
     */
    private function getFeaturedProperties() {
        try {
            // Use global PDO connection instead of model dependency
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->prepare("
                SELECT
                    p.id,
                    p.title,
                    p.address,
                    p.price,
                    p.bedrooms,
                    p.bathrooms,
                    p.area_sqft,
                    p.status,
                    p.description,
                    p.created_at,
                    p.city,
                    p.state,
                    p.latitude,
                    p.longitude,
                    p.featured,
                    (SELECT pi.image_path
                     FROM property_images pi
                     WHERE pi.property_id = p.id
                     ORDER BY pi.is_primary DESC, pi.sort_order ASC
                     LIMIT 1) as main_image,
                    (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images
                FROM properties p
                WHERE p.status = 'available'
                  AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT 12
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Featured properties query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get locations for search dropdown
     */
    private function getLocations() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $location_stmt = $pdo->prepare("
                SELECT
                    city,
                    state,
                    COUNT(*) as property_count
                FROM properties
                WHERE city IS NOT NULL
                  AND city != ''
                  AND status = 'available'
                GROUP BY city, state
                HAVING property_count > 0
                ORDER BY property_count DESC, state ASC, city ASC
                LIMIT 50
            ");

            $location_stmt->execute();
            $raw_locations = $location_stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group locations by state for better organization
            $locations = [];
            foreach ($raw_locations as $location) {
                $state = $location['state'] ?? 'Other';
                if (!isset($locations[$state])) {
                    $locations[$state] = [];
                }
                $locations[$state][] = [
                    'city' => $location['city'],
                    'count' => $location['property_count']
                ];
            }

            return $locations;
        } catch (Exception $e) {
            error_log('Locations query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get company statistics
     */
    private function getCompanyStats() {
        try {
            global $pdo;
            if (!$pdo) {
                return $this->getDefaultStats();
            }

            $company_stmt = $pdo->prepare("
                SELECT
                    (SELECT COUNT(*) FROM properties WHERE status IN ('available', 'sold')) as total_properties,
                    (SELECT COUNT(*) FROM properties WHERE status = 'sold') as sold_properties,
                    (SELECT COUNT(*) FROM users WHERE status = 'active') as total_customers,
                    (SELECT COUNT(*) FROM users WHERE status = 'active') as total_agents,
                    (SELECT COUNT(DISTINCT city) FROM properties WHERE city IS NOT NULL AND city != '') as cities_count
            ");

            $company_stmt->execute();
            $company_data = $company_stmt->fetch(\PDO::FETCH_ASSOC);

            if ($company_data) {
                return [
                    'properties_listed' => (int)($company_data['total_properties'] ?? 0),
                    'properties_sold' => (int)($company_data['sold_properties'] ?? 0),
                    'happy_customers' => (int)($company_data['total_customers'] ?? 0),
                    'expert_agents' => (int)($company_data['total_agents'] ?? 0),
                    'years_experience' => 15, // Company experience (can be made dynamic)
                    'cities_covered' => (int)($company_data['cities_count'] ?? 0),
                    'awards_won' => 25, // Company awards (can be made dynamic)
                    'projects_completed' => (int)($company_data['sold_properties'] ?? 0) * 3, // Estimated projects
                    'client_satisfaction' => 4.8, // Default value since property_reviews table might not exist
                    'repeat_customers' => 0 // Default value since property_reviews table might not exist
                ];
            }

            return $this->getDefaultStats();
        } catch (Exception $e) {
            error_log('Company stats query error: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    /**
     * Get default statistics when database is not available
     */
    private function getDefaultStats() {
        return [
            'properties_listed' => 0,
            'properties_sold' => 0,
            'happy_customers' => 0,
            'expert_agents' => 0,
            'years_experience' => 15,
            'cities_covered' => 0,
            'awards_won' => 25,
            'projects_completed' => 0,
            'client_satisfaction' => 4.8,
            'repeat_customers' => 0
        ];
    }
}

?>
