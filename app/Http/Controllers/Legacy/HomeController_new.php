<?php
/**
 * Home Controller
 * Handles homepage and general pages
 */

namespace App\Controllers;

class HomeController extends BaseController {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display homepage
     */
    public function index() {
        // Set page data
        $this->data['page_title'] = 'Home - ' . config('app.name');
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL]
        ];

        // Get featured properties
        $this->data['featured_properties'] = $this->getFeaturedProperties();

        // Get locations for search dropdown
        $this->data['locations'] = $this->getLocations();

        // Get company statistics
        $this->data['company_stats'] = $this->getCompanyStats();

        // Render the homepage using the new view template
        $this->render('pages/homepage_new');
    }

    /**
     * Get featured properties for homepage
     */
    private function getFeaturedProperties() {
        try {
            $stmt = $this->db->prepare("
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
                    p.featured_until,
                    p.floors,
                    u.id as agent_id,
                    u.name as agent_name,
                    u.phone as agent_phone,
                    u.email as agent_email,
                    u.profile_image as agent_image,
                    u.status as agent_status,
                    pt.id as property_type_id,
                    pt.name as property_type,
                    pt.icon as property_type_icon,
                    (SELECT pi.image_path
                     FROM property_images pi
                     WHERE pi.property_id = p.id
                     ORDER BY pi.is_primary DESC, pi.sort_order ASC
                     LIMIT 1) as main_image,
                    (SELECT COUNT(*) FROM property_images pi2 WHERE pi2.property_id = p.id) as total_images,
                    (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
                    (SELECT COUNT(*) FROM property_reviews pr2 WHERE pr2.property_id = p.id) as total_reviews
                FROM properties p
                LEFT JOIN users u ON p.created_by = u.id AND u.status = 'active' AND u.role = 'agent'
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'available'
                  AND p.featured = 1
                  AND (p.featured_until IS NULL OR p.featured_until >= NOW())
                ORDER BY
                    CASE WHEN p.featured_until IS NOT NULL THEN 0 ELSE 1 END,
                    p.featured_until ASC,
                    p.created_at DESC
                LIMIT 12
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Featured properties query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get locations for search dropdown
     */
    private function getLocations() {
        try {
            $location_stmt = $this->db->prepare("
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
            $raw_locations = $location_stmt->fetchAll(PDO::FETCH_ASSOC);

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
        } catch (PDOException $e) {
            error_log('Locations query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get company statistics
     */
    private function getCompanyStats() {
        try {
            $company_stmt = $this->db->prepare("
                SELECT
                    (SELECT COUNT(*) FROM properties WHERE status IN ('available', 'sold')) as total_properties,
                    (SELECT COUNT(*) FROM properties WHERE status = 'sold') as sold_properties,
                    (SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active') as total_customers,
                    (SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'active') as total_agents,
                    (SELECT COUNT(DISTINCT city) FROM properties WHERE city IS NOT NULL AND city != '') as cities_count,
                    (SELECT AVG(rating) FROM property_reviews WHERE rating > 0) as avg_rating,
                    (SELECT COUNT(*) FROM property_reviews WHERE rating >= 4) as satisfied_customers
            ");

            $company_stmt->execute();
            $company_data = $company_stmt->fetch(PDO::FETCH_ASSOC);

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
                    'client_satisfaction' => $company_data['avg_rating'] ? round($company_data['avg_rating'], 1) : 4.8,
                    'repeat_customers' => (int)($company_data['satisfied_customers'] ?? 0)
                ];
            }

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
        } catch (PDOException $e) {
            error_log('Company stats query error: ' . $e->getMessage());
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
}

?>
