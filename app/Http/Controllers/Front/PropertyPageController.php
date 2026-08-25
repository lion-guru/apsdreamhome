<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use PDO;

/**
 * PropertyPageController
 * Property-related pages (properties, plots, property details, buy/sell/rent/invest, list property, property interest, inquiry, featured properties, plot map, plot converter, resell)
 */
class PropertyPageController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function properties()
    {
        $featured_properties = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM properties WHERE status = 'active' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 12");
            $stmt->execute();
            $allProperties = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($allProperties as $prop) {
                $featured_properties[] = [
                    'id' => $prop['id'],
                    'title' => $prop['title'],
                    'location' => $prop['city'] . ', ' . ($prop['state'] ?? ''),
                    'city' => $prop['city'],
                    'price' => "\xE2\x82\xB9" . number_format($prop['price']),
                    'slug' => $prop['slug'] ?? strtolower(str_replace(' ', '-', $prop['title'])),
                    'type' => ucfirst($prop['type'] ?? 'Residential'),
                    'status' => 'Available',
                    'total_area' => $prop['area_sqft'] ?? null,
                    'description' => $prop['description'] ?? null,
                    'image' => $prop['image_path'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            error_log("Properties page error: " . $e->getMessage());
        }

        $this->render('pages/properties', [
            'page_title' => 'Properties - APS Dream Home',
            'page_description' => 'Browse our premium properties for sale.',
            'properties' => $featured_properties,
        ]);
    }

    public function plots()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $colonies = $this->db->fetchAll("
            SELECT c.*, d.name as district_name, s.name as state_name,
                (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available'{$tidSql}) as available_plots
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.is_active = 1
            ORDER BY c.name
        ", $params);

        $this->render('pages/plots', [
            'page_title' => 'Available Plots - APS Dream Home',
            'page_description' => 'Browse available residential and commercial plots for sale.',
            'colonies' => $colonies,
        ]);
    }

    public function colonyPlots($slug)
    {
        $colony = $this->db->fetchOne("
            SELECT c.*, d.name as district_name, s.name as state_name
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.slug = ? AND c.is_active = 1
        ", [$slug]);

        if (!$colony) {
            $this->redirect('/plots');
            return;
        }

        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND p.tenant_id = ?" : "";
        $params = [$colony['id']];
        if ($tid > 1) $params[] = $tid;

        $plots = $this->db->fetchAll("
            SELECT p.*, c.name as colony_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            WHERE p.colony_id = ? AND p.status = 'available'{$tidSql}
            ORDER BY p.plot_number
        ", $params) ?: [];

        $this->render('pages/colony_plots', [
            'page_title' => 'Plots in ' . $colony['name'] . ' - APS Dream Home',
            'page_description' => 'Available plots in ' . $colony['name'] . '.',
            'colony' => $colony,
            'plots' => $plots,
        ]);
    }

    public function propertyDetails($id = null)
    {
        $property = null;
        if ($id) {
            $property = $this->db->fetchOne("SELECT * FROM properties WHERE id = ? AND status = 'active' AND deleted_at IS NULL LIMIT 1", [$id]);
        }

        if (!$property && isset($_GET['slug'])) {
            $property = $this->db->fetchOne("SELECT * FROM properties WHERE slug = ? AND status = 'active' AND deleted_at IS NULL LIMIT 1", [$_GET['slug']]);
        }

        if (!$property) {
            $this->render('pages/404', [
                'page_title' => 'Property Not Found',
                'page_description' => 'The requested property could not be found.',
            ]);
            return;
        }

        // Get similar properties
        $similar = $this->db->fetchAll("
            SELECT * FROM properties
            WHERE id != ? AND status = 'active' AND deleted_at IS NULL
            AND (city = ? OR type = ?)
            ORDER BY created_at DESC LIMIT 4
        ", [$property['id'], $property['city'], $property['type']]) ?: [];

        // Get recent testimonials
        $testimonials = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3");
            $stmt->execute();
            $testimonials = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            // fallback
        }

        $this->render('pages/property_detail', [
            'page_title' => $property['title'] . ' - APS Dream Home',
            'page_description' => $property['description'] ?? 'View property details',
            'property' => $property,
            'similar_properties' => $similar,
            'testimonials' => $testimonials,
        ]);
    }

    public function buyProperty()
    {
        $this->render('pages/buy', [
            'page_title' => 'Buy Property - APS Dream Home',
            'page_description' => 'Find your dream property to buy.',
        ]);
    }

    public function sellProperty()
    {
        $this->render('pages/sell', [
            'page_title' => 'Sell Property - APS Dream Home',
            'page_description' => 'Sell your property with confidence.',
        ]);
    }

    public function rentProperty()
    {
        $this->render('pages/rent', [
            'page_title' => 'Rent Property - APS Dream Home',
            'page_description' => 'Find properties to rent.',
        ]);
    }

    public function investProperty()
    {
        $this->render('pages/invest', [
            'page_title' => 'Invest in Property - APS Dream Home',
            'page_description' => 'Investment opportunities in real estate.',
        ]);
    }

    public function listProperty()
    {
        $this->render('pages/list_property', [
            'page_title' => 'List Your Property - APS Dream Home',
            'page_description' => 'List your property for sale or rent.',
        ]);
    }

    public function handlePropertyListing()
    {
        // Legacy method - redirect to listProperty
        $this->redirect('/list-property');
    }

    public function propertyInterest()
    {
        $this->render('pages/property_interest', [
            'page_title' => 'Property Interest - APS Dream Home',
            'page_description' => 'Express interest in a property.',
        ]);
    }

    public function propertyInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle inquiry submission
            $_SESSION['success'] = 'Thank you for your inquiry! We will contact you soon.';
        }
        $this->redirect('/contact');
    }

    public function getFeaturedProperties()
    {
        header('Content-Type: application/json');
        try {
            $stmt = $this->db->prepare("SELECT * FROM properties WHERE status = 'active' AND featured = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 8");
            $stmt->execute();
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            echo json_encode(['success' => true, 'data' => $properties]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function plotsAvailability()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $colonies = $this->db->fetchAll("
            SELECT c.*, d.name as district_name, s.name as state_name,
                (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available'{$tidSql}) as available_plots
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.is_active = 1
            ORDER BY c.name
        ", $params);

        $this->render('pages/plots_availability', [
            'page_title' => 'Plots Availability - APS Dream Home',
            'page_description' => 'Check real-time plot availability.',
            'colonies' => $colonies,
        ]);
    }

    public function plotMap()
    {
        $this->render('pages/plot_map', [
            'page_title' => 'Interactive Plot Map - APS Dream Home',
            'page_description' => 'Explore plots on interactive map.',
        ]);
    }

    public function plotConverter()
    {
        $this->render('pages/plot_converter', [
            'page_title' => 'Plot Size Converter - APS Dream Home',
            'page_description' => 'Convert between different plot size units.',
        ]);
    }

    public function plotSizeConverter()
    {
        $this->render('pages/plot_size_converter', [
            'page_title' => 'Plot Size Converter - APS Dream Home',
            'page_description' => 'Convert between different plot size units.',
        ]);
    }

    public function resell()
    {
        $this->render('pages/resell', [
            'page_title' => 'Resell Property - APS Dream Home',
            'page_description' => 'Resell your property through us.',
        ]);
    }

    public function plot()
    {
        $this->render('pages/plot', [
            'page_title' => 'Plot Details - APS Dream Home',
            'page_description' => 'View plot details.',
        ]);
    }

    public function resellProperties()
    {
        $this->render('pages/resell_properties', [
            'page_title' => 'Resell Properties - APS Dream Home',
            'page_description' => 'Browse resell properties.',
        ]);
    }

    public function featuredProperties()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM properties WHERE status = 'active' AND featured = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 12");
            $stmt->execute();
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log("Featured properties error: " . $e->getMessage());
            $properties = [];
        }

        $this->render('pages/featured_properties', [
            'page_title' => 'Featured Properties - APS Dream Home',
            'page_description' => 'Our featured properties.',
            'properties' => $properties,
        ]);
    }

    public function colonies()
    {
        try {
            $rows = $this->db->fetchAll("SELECT c.*, d.name as district_name, s.name as state_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE c.is_active = 1 ORDER BY c.name");
            $totalPlots = $this->db->fetch("SELECT COUNT(*) as total FROM plots WHERE colony_id IN (SELECT id FROM colonies WHERE is_active = 1) AND status = 'available'")['total'] ?? 0;
            $colonies = [];
            foreach ($rows as $row) {
                $amenities = [];
                if (!empty($row['amenities'])) {
                    $amenitiesList = json_decode($row['amenities'], true);
                    if (is_array($amenitiesList)) {
                        $amenities = array_map(function($a) { return $a['name'] ?? ''; }, $amenitiesList);
                    }
                }
                $colonyPlots = $this->db->fetch("SELECT COUNT(*) as total FROM plots WHERE colony_id = ? AND status = 'available'", [$row['id']])['total'] ?? 0;
                $colonies[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'location' => trim(($row['district_name'] ?? '') . ', ' . ($row['state_name'] ?? ''), ', '),
                    'starting_price' => $row['starting_price'] ?? 0,
                    'total_plots' => $row['total_plots'] ?? 0,
                    'available_plots' => $colonyPlots,
                    'completion_percentage' => $row['completion_percentage'] ?? 0,
                    'status' => $row['status'] ?? 'active',
                    'description' => $row['description'] ?? '',
                    'amenities' => $amenities,
                    'image' => $row['image'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            error_log("Colonies page error: " . $e->getMessage());
            $colonies = [];
            $totalPlots = 0;
        }

        $colonyStats = [
            'total_colonies' => count($colonies),
            'total_area' => array_sum(array_map(static function ($c) {
                return (int)($c['available_plots'] ?? 0) * 1200;
            }, $colonies)) . ' sqft',
            'total_plots' => $totalPlots,
            'cities_covered' => count(array_unique(array_column($colonies, 'location'))),
        ];

        $this->render('pages/colonies', [
            'page_title' => 'Our Colonies - APS Dream Home',
            'page_description' => 'Explore our residential colonies and townships.',
            'colonies' => $colonies,
            'colony_stats' => $colonyStats,
            'total_plots' => $totalPlots,
        ]);
    }
}