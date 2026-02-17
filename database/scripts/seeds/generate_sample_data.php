<?php
/**
 * Generate Sample Property Data API
 * 
 * This script handles the AJAX request to generate sample property data.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Prevent direct access to this file
define('SECURE_ACCESS', true);

// Include configuration and functions
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start secure session
start_secure_session('aps_dream_home');

// Set JSON header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'summary' => []
];

// Function to add log entry
function addLog(&$response, $message, $type = 'info') {
    if (!isset($response['logs'])) {
        $response['logs'] = [];
    }
    
    $response['logs'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Check if user is authenticated and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

// Get input parameters
$numProperties = filter_input(INPUT_POST, 'num_properties', FILTER_VALIDATE_INT, [
    'options' => [
        'default' => 20,
        'min_range' => 1,
        'max_range' => 1000
    ]
]);

$agentId = filter_input(INPUT_POST, 'agent_id', FILTER_VALIDATE_INT, [
    'options' => [
        'default' => 1,
        'min_range' => 1
    ]
]);

$truncateFirst = filter_input(INPUT_POST, 'truncate_first', FILTER_VALIDATE_BOOLEAN);

// Initialize summary
$summary = [
    'Properties Created' => 0,
    'Property Types' => 0,
    'Amenities Added' => 0,
    'Images Generated' => 0,
    'Execution Time' => 0
];

// Start timing
try {
    $startTime = microtime(true);
    
    // Check if the database connection is available
    if (!($db instanceof mysqli)) {
        throw new Exception('Database connection failed');
    }
    
    // Begin transaction
    $db->begin_transaction();
    
    // Truncate tables if requested
    if ($truncateFirst) {
        addLog($response, 'Clearing existing data...', 'warning');
        
        // Disable foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        
        // Truncate tables in the correct order to avoid foreign key constraints
        $tables = [
            'property_favorites',
            'visit_reminders',
            'property_visits',
            'property_amenities',
            'properties',
            'property_types'
        ];
        
        foreach ($tables as $table) {
            $db->query("TRUNCATE TABLE `$table`");
        }
        
        // Re-enable foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS = 1');
        
        addLog($response, 'Existing data cleared successfully', 'success');
    }
    
    // Insert property types if they don't exist
    addLog($response, 'Checking property types...', 'info');
    
    $propertyTypes = [
        ['Apartment', 'apartment', 'Residential units within a larger building', 'fa-building'],
        ['Villa', 'villa', 'Luxury standalone house with private garden', 'fa-home'],
        ['House', 'house', 'Single-family residential building', 'fa-home'],
        ['Office', 'office', 'Commercial office space', 'fa-building'],
        ['Building', 'building', 'Commercial building with multiple units', 'fa-building'],
        ['Townhouse', 'townhouse', 'Multi-floor home sharing walls with adjacent properties', 'fa-home'],
        ['Shop', 'shop', 'Retail commercial space', 'fa-store'],
        ['Garage', 'garage', 'Parking or storage space', 'fa-warehouse'],
        ['Land', 'land', 'Vacant land for development', 'fa-mountain'],
        ['Farm', 'farm', 'Agricultural land with or without structures', 'fa-tractor']
    ];
    
    $typeMap = [];
    $typeCount = 0;
    
    foreach ($propertyTypes as $type) {
        $query = "INSERT IGNORE INTO property_types (name, slug, description, icon, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt = $db->prepare($query);
        $stmt->bind_param('ssss', $type[0], $type[1], $type[2], $type[3]);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $typeId = $db->insert_id;
                $typeMap[$type[1]] = $typeId;
                $typeCount++;
            } else {
                // Type already exists, get its ID
                $getStmt = $db->prepare("SELECT id FROM property_types WHERE slug = ?");
                $getStmt->bind_param('s', $type[1]);
                $getStmt->execute();
                $result = $getStmt->get_result();
                $row = $result->fetch_assoc();
                $typeMap[$type[1]] = $row['id'];
                $getStmt->close();
            }
        }
        $stmt->close();
    }
    
    $summary['Property Types'] = count($typeMap);
    addLog($response, "Found/created {$summary['Property Types']} property types", 'success');
    
    // Sample data for properties
    $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'];
    $states = ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA', 'TX', 'CA', 'TX', 'CA'];
    $descriptions = [
        'Beautiful property with amazing views and modern amenities.',
        'Spacious and well-maintained property in a prime location.',
        'Luxury living at its finest with top-notch finishes throughout.',
        'Perfect for families with plenty of space and great schools nearby.',
        'Investment opportunity with great potential for appreciation.'
    ];
    
    $amenities = [
        'Swimming Pool', 'Gym', 'Parking', 'Garden', 'Security',
        'Air Conditioning', 'Heating', 'Fireplace', 'Balcony', 'Garage',
        'Elevator', 'Doorman', 'Laundry', 'Storage', 'Furnished'
    ];
    
    // Insert sample properties
    addLog($response, "Generating $numProperties sample properties...", 'info');
    
    for ($i = 0; $i < $numProperties; $i++) {
        // Select a random property type
        $typeKey = array_rand($typeMap);
        $typeId = $typeMap[$typeKey];
        
        // Generate property data
        $title = ucfirst($typeKey) . ' ' . ($i + 1) . ' in ' . $cities[array_rand($cities)];
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $title)) . '-' . uniqid();
        $description = $descriptions[array_rand($descriptions)];
        $price = rand(100000, 5000000);
        $area = rand(800, 10000);
        $bedrooms = rand(1, 10);
        $bathrooms = rand(1, $bedrooms + 2);
        $garages = rand(0, 4);
        $yearBuilt = rand(1950, 2023);
        $cityIndex = array_rand($cities);
        $city = $cities[$cityIndex];
        $state = $states[$cityIndex];
        $zip = rand(10000, 99999);
        $address = rand(100, 9999) . ' ' . ucfirst(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 8)) . ' St';
        $isFeatured = rand(0, 1) ? 1 : 0;
        $status = ['draft', 'published', 'sold', 'rented'][rand(0, 3)];
        $listingStatus = ['for_sale', 'for_rent', 'sold', 'rented'][rand(0, 3)];
        
        // Insert property
        $query = "INSERT INTO properties (
            title, slug, description, property_type_id, price, area, bedrooms, bathrooms, 
            garages, year_built, address, city, state, zip_code, is_featured, 
            status, listing_status, agent_id, created_by, updated_by, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param(
            'sssidiisssssssisiiii',
            $title,
            $slug,
            $description,
            $typeId,
            $price,
            $area,
            $bedrooms,
            $bathrooms,
            $garages,
            $yearBuilt,
            $address,
            $city,
            $state,
            $zip,
            $isFeatured,
            $status,
            $listingStatus,
            $agentId,
            $agentId,
            $agentId
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert property: " . $stmt->error);
        }
        
        $propertyId = $db->insert_id;
        $summary['Properties Created']++;
        
        // Add random amenities
        $propertyAmenities = array_rand(array_flip($amenities), rand(3, 8));
        if (!is_array($propertyAmenities)) {
            $propertyAmenities = [$propertyAmenities];
        }
        
        foreach ($propertyAmenities as $amenity) {
            $query = "INSERT INTO property_amenities (property_id, amenity_name) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bind_param('is', $propertyId, $amenity);
            
            if ($stmt->execute()) {
                $summary['Amenities Added']++;
            }
            $stmt->close();
        }
        
        // Add a featured image URL (placeholder)
        $featuredImage = "https://picsum.photos/800/600?property=" . $propertyId;
        $query = "UPDATE properties SET featured_image = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('si', $featuredImage, $propertyId);
        $stmt->execute();
        $stmt->close();
        
        $summary['Images Generated']++;
        
        // Log progress
        if (($i + 1) % 5 === 0) {
            addLog($response, "Generated {$summary['Properties Created']} properties so far...", 'info');
        }
    }
    
    // Commit transaction
    $db->commit();
    
    // Calculate execution time
    $executionTime = round(microtime(true) - $startTime, 2);
    $summary['Execution Time'] = "{$executionTime} seconds";
    
    $response['success'] = true;
    $response['message'] = "Successfully generated {$summary['Properties Created']} sample properties.";
    $response['summary'] = $summary;
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db) && $db instanceof mysqli) {
        $db->rollback();
    }
    
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    
    // Add error to logs if available
    if (isset($response['logs'])) {
        addLog($response, $response['message'], 'error');
    }
}

// Send the response
echo json_encode($response);
?>
