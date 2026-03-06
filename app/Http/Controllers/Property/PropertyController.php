<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\BaseController;

/**
 * PropertyController Controller
 * Handles Property listing and detail pages
 */
class PropertyController extends BaseController
{
    /**
     * Index method - Show all properties
     * @return void
     */
    public function index()
    {
        $data = [
            'page_title' => 'Properties - APS Dream Home',
            'page_description' => 'Browse our extensive collection of premium properties in Gorakhpur, Lucknow, and across Uttar Pradesh.',
            'properties' => $this->getProperties(),
            'filters' => [
                'types' => ['Apartment', 'Villa', 'Commercial', 'Plot/Land'],
                'locations' => ['Gorakhpur', 'Lucknow', 'Deoria', 'Basti', 'Maharajganj'],
                'price_ranges' => ['Under 50 Lac', '50 Lac - 1 Crore', '1 Crore - 5 Crore', 'Above 5 Crore'],
                'bedrooms' => ['1 BHK', '2 BHK', '3 BHK', '4 BHK', '5+ BHK'],
                'amenities' => ['Parking', 'Garden', 'Security', 'Power Backup', 'Lift', 'Swimming Pool']
            ]
        ];

        $this->render('properties/index', $data, 'layouts/base_new');
    }

    /**
     * Show method - Display single property details
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $property = $this->getProperty($id);
        
        if (!$property) {
            return $this->notFound('Property not found');
        }

        $data = [
            'page_title' => $property->title . ' - APS Dream Home',
            'page_description' => substr(strip_tags($property->description), 0, 160) . '...',
            'property' => $property,
            'related_properties' => $this->getRelatedProperties($id, $property->type, $property->location)
        ];

        $this->render('properties/detail', $data, 'layouts/base_new');
    }

    /**
     * Get properties list
     * @return array
     */
    private function getProperties()
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
                ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
                ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'apsdreamhome') . 
                ";charset=utf8mb4",
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $stmt = $pdo->query("SELECT * FROM properties WHERE status = 'available' ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            // Fallback to sample data
            return [
                [
                    'id' => 1,
                    'title' => 'Luxury Villa in Suryoday City',
                    'type' => 'villa',
                    'location' => 'Gorakhpur',
                    'price' => 7500000,
                    'price_display' => '₹75 Lac',
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => '2500',
                    'area_unit' => 'Sq.ft',
                    'description' => 'Luxury 4 BHK villa with modern amenities in prime location of Suryoday City, Gorakhpur. Features spacious rooms, modular kitchen, private garden, and covered parking.',
                    'image' => 'images/properties/luxury-villa-1.jpg',
                    'featured' => true,
                    'amenities' => ['Parking', 'Garden', 'Security', 'Power Backup'],
                    'created_at' => '2024-01-15'
                ],
                [
                    'id' => 2,
                    'title' => 'Modern 3 BHK Apartment',
                    'type' => 'apartment',
                    'location' => 'Lucknow',
                    'price' => 5200000,
                    'price_display' => '₹52 Lac',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => '1800',
                    'area_unit' => 'Sq.ft',
                    'description' => 'Modern 3 BHK apartment in Gomti Nagar, Lucknow with premium finishes and ample natural lighting. Well-ventilated rooms with modern fittings.',
                    'image' => 'images/properties/modern-apartment-1.jpg',
                    'featured' => true,
                    'amenities' => ['Parking', 'Lift', 'Security'],
                    'created_at' => '2024-01-20'
                ],
                [
                    'id' => 3,
                    'title' => 'Commercial Space',
                    'type' => 'commercial',
                    'location' => 'Gorakhpur',
                    'price' => 3500000,
                    'price_display' => '₹35 Lac',
                    'bedrooms' => 0,
                    'bathrooms' => 2,
                    'area' => '1200',
                    'area_unit' => 'Sq.ft',
                    'description' => 'Prime commercial space in Hazratganj, Gorakhpur suitable for retail, office, or showroom. High footfall area with excellent connectivity.',
                    'image' => 'images/properties/commercial-space-1.jpg',
                    'featured' => false,
                    'amenities' => ['Parking', 'Security'],
                    'created_at' => '2024-01-25'
                ]
            ];
        }
    }

    /**
     * Get single property
     * @param int $id
     * @return object|null
     */
    private function getProperty($id)
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
                ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
                ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'apsdreamhome') . 
                ";charset=utf8mb4",
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND status = 'available'");
            $stmt->execute([$id]);
            $property = $stmt->fetch(\PDO::FETCH_OBJ);
            
            if ($property) {
                // Add additional calculated fields
                $property->price_display = '₹' . number_format($property->price / 100000, 2);
                $property->area_display = $property->area . ' ' . $property->area_unit;
            }
            
            return $property;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get related properties
     * @param int $excludeId
     * @param string $type
     * @param string $location
     * @return array
     */
    private function getRelatedProperties($excludeId, $type, $location)
    {
        try {
            $pdo = new \PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
                ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
                ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'apsdreamhome') . 
                ";charset=utf8mb4",
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $stmt = $pdo->prepare("
                SELECT * FROM properties 
                WHERE id != ? 
                AND type = ? 
                AND location = ? 
                AND status = 'available' 
                ORDER BY created_at DESC 
                LIMIT 3
            ");
            $stmt->execute([$excludeId, $type, $location]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (Exception $e) {
            return [];
        }
    }
}
