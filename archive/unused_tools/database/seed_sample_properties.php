<?php
/**
 * Sample Properties Seeder
 * Creates demo properties for testing the APS Dream Home system
 */

require_once __DIR__ . '/includes/db_connection.php';

class SamplePropertiesSeeder {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Create sample properties for testing
     */
    public function createSampleProperties() {
        echo "🏠 Creating Sample Properties for APS Dream Home...\n\n";

        // Sample property data
        $properties = [
            [
                'title' => 'Luxury Apartment in Gorakhpur City Center',
                'description' => 'Spacious 3 BHK apartment with modern amenities, located in the heart of Gorakhpur. Features include modular kitchen, attached bathrooms, and 24/7 security.',
                'price' => 4500000,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqft' => 1200,
                'address' => 'Medical College Road, Gorakhpur',
                'city' => 'Gorakhpur',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 1, // Apartment
                'status' => 'available',
                'featured' => 1,
                'created_by' => 1 // Admin user
            ],
            [
                'title' => 'Premium Villa in Lucknow Gomti Nagar',
                'description' => 'Beautiful 4 BHK villa with private garden, swimming pool, and modern interiors. Perfect for luxury living in Lucknow\'s most prestigious area.',
                'price' => 15000000,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area_sqft' => 2500,
                'address' => 'Gomti Nagar Extension, Lucknow',
                'city' => 'Lucknow',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 2, // Villa
                'status' => 'available',
                'featured' => 1,
                'created_by' => 1
            ],
            [
                'title' => 'Modern 2 BHK Apartment in Varanasi',
                'description' => 'Contemporary 2 BHK apartment near BHU campus. Features smart home technology, covered parking, and excellent connectivity.',
                'price' => 3200000,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_sqft' => 950,
                'address' => 'Lanka, Varanasi',
                'city' => 'Varanasi',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 1, // Apartment
                'status' => 'available',
                'featured' => 0,
                'created_by' => 1
            ],
            [
                'title' => 'Commercial Space in Kanpur Mall',
                'description' => 'Prime commercial space in Kanpur\'s busiest shopping mall. Ideal for retail business with high footfall and excellent visibility.',
                'price' => 8000000,
                'bedrooms' => 0,
                'bathrooms' => 1,
                'area_sqft' => 800,
                'address' => 'Mall Road, Kanpur',
                'city' => 'Kanpur',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 5, // Commercial
                'status' => 'available',
                'featured' => 1,
                'created_by' => 1
            ],
            [
                'title' => 'Independent House in Allahabad',
                'description' => 'Charming 3 BHK independent house with garden and parking space. Located in peaceful residential area with good connectivity.',
                'price' => 5500000,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqft' => 1500,
                'address' => 'Civil Lines, Allahabad',
                'city' => 'Allahabad',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 3, // Independent House
                'status' => 'available',
                'featured' => 0,
                'created_by' => 1
            ],
            [
                'title' => 'Studio Apartment in Noida',
                'description' => 'Modern studio apartment in Noida\'s tech hub. Perfect for young professionals with gym, swimming pool, and metro connectivity.',
                'price' => 2800000,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqft' => 650,
                'address' => 'Sector 62, Noida',
                'city' => 'Noida',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 1, // Apartment
                'status' => 'available',
                'featured' => 0,
                'created_by' => 1
            ],
            [
                'title' => 'Duplex Villa in Ghaziabad',
                'description' => 'Luxurious duplex villa with modern architecture, private terrace, and premium fittings. Located in upscale Ghaziabad neighborhood.',
                'price' => 12000000,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area_sqft' => 2200,
                'address' => 'Indirapuram, Ghaziabad',
                'city' => 'Ghaziabad',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 2, // Villa
                'status' => 'available',
                'featured' => 1,
                'created_by' => 1
            ],
            [
                'title' => 'Plot for Sale in Meerut',
                'description' => 'Prime residential plot in developing area of Meerut. Ready for construction with all amenities and good connectivity.',
                'price' => 2500000,
                'bedrooms' => 0,
                'bathrooms' => 0,
                'area_sqft' => 2000,
                'address' => 'Shastri Nagar, Meerut',
                'city' => 'Meerut',
                'state' => 'Uttar Pradesh',
                'property_type_id' => 4, // Plot/Land
                'status' => 'available',
                'featured' => 0,
                'created_by' => 1
            ]
        ];

        $successCount = 0;
        $errorCount = 0;

        foreach ($properties as $property) {
            try {
                $stmt = $this->conn->prepare("
                    INSERT INTO properties
                    (title, description, price, bedrooms, bathrooms, area_sqft, address, city, state, property_type_id, status, featured, created_by, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");

                $result = $stmt->execute([
                    $property['title'],
                    $property['description'],
                    $property['price'],
                    $property['bedrooms'],
                    $property['bathrooms'],
                    $property['area_sqft'],
                    $property['address'],
                    $property['city'],
                    $property['state'],
                    $property['property_type_id'],
                    $property['status'],
                    $property['featured'],
                    $property['created_by']
                ]);

                if ($result) {
                    $propertyId = $this->conn->lastInsertId();
                    echo "✅ {$property['title']} (₹" . number_format($property['price']) . ")\n";
                    $successCount++;

                    // Add sample property images
                    $this->addSampleImages($propertyId, $property['city']);

                } else {
                    echo "❌ Failed to create: {$property['title']}\n";
                    $errorCount++;
                }

            } catch (PDOException $e) {
                echo "❌ Error creating {$property['title']}: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }

        echo "\n📊 Summary:\n";
        echo "✅ Successfully created: {$successCount} properties\n";
        echo "❌ Failed to create: {$errorCount} properties\n";
        echo "🔗 Total properties in system: " . ($successCount + $errorCount) . "\n";

        return $successCount > 0;
    }

    /**
     * Add sample property images
     */
    private function addSampleImages($propertyId, $city) {
        $sampleImages = [
            "https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop",
            "https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop",
            "https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&h=600&fit=crop"
        ];

        foreach ($sampleImages as $index => $imageUrl) {
            try {
                $stmt = $this->conn->prepare("
                    INSERT INTO property_images
                    (property_id, image_path, is_primary, sort_order, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");

                $isPrimary = ($index === 0) ? 1 : 0;
                $stmt->execute([$propertyId, $imageUrl, $isPrimary, $index]);

            } catch (PDOException $e) {
                // Skip image insertion errors
            }
        }
    }

    /**
     * Create property types if they don't exist
     */
    public function createPropertyTypes() {
        echo "🏷️  Creating Property Types...\n";

        $propertyTypes = [
            ['name' => 'Apartment', 'description' => 'Modern apartment units'],
            ['name' => 'Villa', 'description' => 'Independent luxury villas'],
            ['name' => 'Independent House', 'description' => 'Standalone residential houses'],
            ['name' => 'Plot/Land', 'description' => 'Residential and commercial plots'],
            ['name' => 'Commercial', 'description' => 'Commercial spaces and offices'],
            ['name' => 'Studio', 'description' => 'Studio apartments and lofts']
        ];

        foreach ($propertyTypes as $type) {
            try {
                $stmt = $this->conn->prepare("
                    INSERT IGNORE INTO property_types (name, description, created_at)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$type['name'], $type['description']]);
                echo "✅ Property type: {$type['name']}\n";

            } catch (PDOException $e) {
                echo "⚠️  Property type {$type['name']}: Already exists\n";
            }
        }
    }

    /**
     * Display properties summary
     */
    public function displaySummary() {
        echo "\n📋 Properties Summary:\n";
        echo "=====================\n";

        try {
            // Count by city
            $stmt = $this->conn->query("
                SELECT city, COUNT(*) as count
                FROM properties
                WHERE status = 'available'
                GROUP BY city
                ORDER BY count DESC
            ");

            echo "\n🏙️  Properties by City:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "  • {$row['city']}: {$row['count']} properties\n";
            }

            // Count by type
            $stmt = $this->conn->query("
                SELECT pt.name, COUNT(p.id) as count
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'available'
                GROUP BY p.property_type_id, pt.name
                ORDER BY count DESC
            ");

            echo "\n🏠 Properties by Type:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "  • {$row['name']}: {$row['count']} properties\n";
            }

            // Featured properties
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM properties WHERE featured = 1 AND status = 'available'");
            $featured = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "\n⭐ Featured Properties: {$featured['count']}\n";

        } catch (PDOException $e) {
            echo "❌ Error generating summary: " . $e->getMessage() . "\n";
        }
    }
}

// Main execution
echo "🚀 APS Dream Home - Sample Properties Setup\n";
echo "============================================\n\n";

// Check database connection
try {
    $conn = new PDO("mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $seeder = new SamplePropertiesSeeder($conn);

    // Create property types first
    $seeder->createPropertyTypes();

    echo "\n";

    // Create sample properties
    if ($seeder->createSampleProperties()) {
        // Display summary
        $seeder->displaySummary();

        echo "\n🎉 Sample properties setup completed successfully!\n";
        echo "\n📋 What you can do now:\n";
        echo "1. Visit: http://localhost/apsdreamhomefinal/properties\n";
        echo "2. Browse and filter properties\n";
        echo "3. View property details\n";
        echo "4. Test search and filtering\n";
        echo "5. Admin panel: http://localhost/apsdreamhomefinal/admin\n";

    } else {
        echo "\n❌ Sample properties setup failed\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\n💡 Make sure:\n";
    echo "1. XAMPP is running\n";
    echo "2. Database 'apsdreamhome' exists\n";
    echo "3. Database tables are created\n";
}

echo "\n";
?>
