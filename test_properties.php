<?php
/**
 * Test Properties Functionality
 * 
 * This script tests the properties functionality including database connection,
 * property retrieval, and related features.
 */

// Start secure session
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration and functions
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Set page title
$pageTitle = 'Test Properties';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { padding: 2rem 0; background-color: #f8f9fa; }
        .test-container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .test-section { margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #dee2e6; border-radius: 0.25rem; }
        .test-result { margin-top: 1rem; padding: 1rem; border-radius: 0.25rem; }
        .success { background-color: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .warning { background-color: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .info { background-color: #e2f3fd; color: #0c5460; border-left: 4px solid #17a2b8; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 0.25rem; overflow-x: auto; }
        .test-title { margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-container">
            <h1 class="mb-4">
                <i class="bi bi-house-gear me-2"></i>
                APS Dream Homes - Test Properties
            </h1>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                This page helps you test the properties functionality of the APS Dream Homes system.
            </div>
            
            <?php
            // Test database connection
            echo '<div class="test-section">';
            echo '<h2 class="test-title"><i class="bi bi-database me-2"></i>Database Connection</h2>';
            
            try {
                $db = DatabaseConfig::getConnection();
                
                if ($db->connect_error) {
                    throw new Exception("Connection failed: " . $db->connect_error);
                }
                
                echo '<div class="test-result success">';
                echo '<i class="bi bi-check-circle-fill me-2"></i>Successfully connected to the database.';
                
                // Get database info
                $result = $db->query("SELECT DATABASE() as db, VERSION() as version");
                if ($result) {
                    $info = $result->fetch_assoc();
                    echo '<div class="mt-2"><strong>Database:</strong> ' . htmlspecialchars($info['db']) . '</div>';
                    echo '<div><strong>MySQL Version:</strong> ' . htmlspecialchars($info['version']) . '</div>';
                }
                
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="test-result error">';
                echo '<i class="bi bi-x-circle-fill me-2"></i>Database connection failed: ' . htmlspecialchars($e->getMessage());
                echo '</div>';
                
                // Show configuration
                echo '<div class="test-result warning mt-3">';
                echo '<h5>Database Configuration:</h5>';
                echo '<pre>DB_HOST: ' . (defined('DB_HOST') ? htmlspecialchars(DB_HOST) : 'not defined') . "\n";
                echo 'DB_NAME: ' . (defined('DB_NAME') ? htmlspecialchars(DB_NAME) : 'not defined') . "\n";
                echo 'DB_USER: ' . (defined('DB_USER') ? htmlspecialchars(DB_USER) : 'not defined') . "\n";
                echo 'DB_PASS: ' . (defined('DB_PASS') ? '********' : 'not defined') . '</pre>';
                echo '</div>';
                
                // Don't proceed with other tests if database connection failed
                exit;
            }
            echo '</div>';
            
            // Test property types table
            echo '<div class="test-section">';
            echo '<h2 class="test-title"><i class="bi bi-tags me-2"></i>Property Types</h2>';
            
            try {
                $query = "SELECT * FROM property_types WHERE status = 'active' ORDER BY name";
                $result = $db->query($query);
                
                if ($result === false) {
                    throw new Exception("Query failed: " . $db->error);
                }
                
                $propertyTypes = [];
                while ($row = $result->fetch_assoc()) {
                    $propertyTypes[] = $row;
                }
                
                if (empty($propertyTypes)) {
                    echo '<div class="test-result warning">';
                    echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>No property types found. You may need to run the database update script.';
                    echo '</div>';
                    echo '<div class="mt-3">';
                    echo '<a href="/database/update_database.php" class="btn btn-primary me-2">';
                    echo '<i class="bi bi-database-gear me-1"></i> Run Database Update';
                    echo '</a>';
                    echo '<a href="/database/sample_properties.php" class="btn btn-outline-secondary">';
                    echo '<i class="bi bi-magic me-1"></i> Generate Sample Data';
                    echo '</a>';
                    echo '</div>';
                } else {
                    echo '<div class="test-result success">';
                    echo '<i class="bi bi-check-circle-fill me-2"></i>Found ' . count($propertyTypes) . ' property types.';
                    echo '</div>';
                    
                    // Display property types
                    echo '<div class="mt-3">';
                    echo '<h5>Available Property Types:</h5>';
                    echo '<div class="d-flex flex-wrap gap-2">';
                    foreach ($propertyTypes as $type) {
                        echo '<span class="badge bg-primary">' . htmlspecialchars($type['name']) . '</span>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="test-result error">';
                echo '<i class="bi bi-x-circle-fill me-2"></i>Error: ' . htmlspecialchars($e->getMessage());
                echo '</div>';
                
                // Check if table exists
                $result = $db->query("SHOW TABLES LIKE 'property_types'");
                if ($result->num_rows === 0) {
                    echo '<div class="test-result warning mt-3">';
                    echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>The property_types table does not exist. ';
                    echo 'You need to run the database update script.';
                    echo '</div>';
                    echo '<div class="mt-3">';
                    echo '<a href="/database/update_database.php" class="btn btn-primary">';
                    echo '<i class="bi bi-database-gear me-1"></i> Run Database Update';
                    echo '</a>';
                    echo '</div>';
                }
            }
            echo '</div>';
            
            // Test properties table
            echo '<div class="test-section">';
            echo '<h2 class="test-title"><i class="bi bi-house-door me-2"></i>Properties</h2>';
            
            try {
                $query = "SELECT p.*, pt.name as property_type_name, pt.slug as property_type_slug 
                         FROM properties p 
                         LEFT JOIN property_types pt ON p.property_type_id = pt.id 
                         WHERE p.status = 'published' 
                         ORDER BY p.created_at DESC 
                         LIMIT 5";
                
                $result = $db->query($query);
                
                if ($result === false) {
                    throw new Exception("Query failed: " . $db->error);
                }
                
                $properties = [];
                while ($row = $result->fetch_assoc()) {
                    $properties[] = $row;
                }
                
                if (empty($properties)) {
                    echo '<div class="test-result warning">';
                    echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>No published properties found. ';
                    echo 'You may need to generate sample data or add properties.';
                    echo '</div>';
                    
                    if (count($propertyTypes) > 0) {
                        echo '<div class="mt-3">';
                        echo '<a href="/database/sample_properties.php" class="btn btn-primary">';
                        echo '<i class="bi bi-magic me-1"></i> Generate Sample Properties';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="test-result success">';
                    echo '<i class="bi bi-check-circle-fill me-2"></i>Found ' . count($properties) . ' published properties.';
                    echo '</div>';
                    
                    // Display sample properties
                    echo '<div class="mt-3">';
                    echo '<h5>Sample Properties:</h5>';
                    echo '<div class="row g-3">';
                    
                    foreach ($properties as $property) {
                        echo '<div class="col-md-6">';
                        echo '<div class="card h-100">';
                        if (!empty($property['featured_image'])) {
                            echo '<img src="' . htmlspecialchars($property['featured_image']) . '" class="card-img-top" alt="' . htmlspecialchars($property['title']) . '" style="height: 180px; object-fit: cover;">';
                        }
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($property['title']) . '</h5>';
                        echo '<p class="card-text text-muted">' . htmlspecialchars($property['property_type_name']) . ' • ';
                        echo number_format($property['price']) . ' • ';
                        echo $property['bedrooms'] . ' <i class="bi bi-house-door"></i> • ';
                        echo $property['bathrooms'] . ' <i class="bi bi-droplet"></i></p>';
                        echo '<a href="/properties.php?slug=' . urlencode($property['slug']) . '" class="btn btn-sm btn-outline-primary">View Details</a>';
                        echo '</div></div></div>';
                    }
                    
                    echo '</div>'; // Close row
                    echo '</div>'; // Close mt-3
                    
                    // Link to view all properties
                    echo '<div class="mt-3">';
                    echo '<a href="/properties_new.php" class="btn btn-primary">';
                    echo '<i class="bi bi-house me-1"></i> View All Properties';
                    echo '</a>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="test-result error">';
                echo '<i class="bi bi-x-circle-fill me-2"></i>Error: ' . htmlspecialchars($e->getMessage());
                echo '</div>';
                
                // Check if table exists
                $result = $db->query("SHOW TABLES LIKE 'properties'");
                if ($result->num_rows === 0) {
                    echo '<div class="test-result warning mt-3">';
                    echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>The properties table does not exist. ';
                    echo 'You need to run the database update script.';
                    echo '</div>';
                    echo '<div class="mt-3">';
                    echo '<a href="/database/update_database.php" class="btn btn-primary">';
                    echo '<i class="bi bi-database-gear me-1"></i> Run Database Update';
                    echo '</a>';
                    echo '</div>';
                }
            }
            echo '</div>';
            
            // Test property visits
            echo '<div class="test-section">';
            echo '<h2 class="test-title"><i class="bi bi-calendar-check me-2"></i>Property Visits</h2>';
            
            try {
                $result = $db->query("SHOW TABLES LIKE 'property_visits'");
                
                if ($result->num_rows === 0) {
                    echo '<div class="test-result warning">';
                    echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>The property_visits table does not exist. ';
                    echo 'You need to run the database update script.';
                    echo '</div>';
                    echo '<div class="mt-3">';
                    echo '<a href="/database/update_database.php" class="btn btn-primary">';
                    echo '<i class="bi bi-database-gear me-1"></i> Run Database Update';
                    echo '</a>';
                    echo '</div>';
                } else {
                    // Check if there are any visits
                    $result = $db->query("SELECT COUNT(*) as count FROM property_visits");
                    $count = $result->fetch_assoc()['count'];
                    
                    echo '<div class="test-result success">';
                    echo '<i class="bi bi-check-circle-fill me-2"></i>Property visits table is available.';
                    echo '</div>';
                    
                    echo '<div class="mt-3">';
                    echo '<p>Total visits scheduled: <strong>' . $count . '</strong></p>';
                    
                    if ($count > 0) {
                        // Show recent visits
                        $result = $db->query("SELECT pv.*, p.title as property_title 
                                           FROM property_visits pv 
                                           LEFT JOIN properties p ON pv.property_id = p.id 
                                           ORDER BY pv.visit_datetime DESC 
                                           LIMIT 3");
                        
                        if ($result->num_rows > 0) {
                            echo '<h5>Recent Visits:</h5>';
                            echo '<ul class="list-group">';
                            while ($row = $result->fetch_assoc()) {
                                $datetime = new DateTime($row['visit_datetime']);
                                echo '<li class="list-group-item">';
                                echo '<strong>' . htmlspecialchars($row['visitor_name']) . '</strong> - ';
                                echo '<a href="/properties.php?slug=' . urlencode($row['property_slug'] ?? '') . '">';
                                echo htmlspecialchars($row['property_title'] ?? 'N/A') . '</a><br>';
                                echo '<small class="text-muted">' . $datetime->format('M j, Y g:i A') . ' • ';
                                echo ucfirst($row['status']) . '</small>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                    } else {
                        echo '<div class="alert alert-info">';
                        echo '<i class="bi bi-info-circle-fill me-2"></i>No visits scheduled yet. ';
                        echo 'You can schedule a visit from the property details page.';
                        echo '</div>';
                    }
                    
                    echo '</div>'; // Close mt-3
                }
                
            } catch (Exception $e) {
                echo '<div class="test-result error">';
                echo '<i class="bi bi-x-circle-fill me-2"></i>Error: ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            echo '</div>';
            
            // System information
            echo '<div class="test-section">';
            echo '<h2 class="test-title"><i class="bi bi-info-circle me-2"></i>System Information</h2>';
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<h5>PHP Version: ' . PHP_VERSION . '</h5>';
            echo '<h5>Server: ' . $_SERVER['SERVER_SOFTWARE'] . '</h5>';
            echo '<h5>Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . '</h5>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<h5>MySQL Client: ' . mysqli_get_client_info() . '</h5>';
            echo '<h5>MySQL Server: ' . ($db->server_info ?? 'N/A') . '</h5>';
            echo '<h5>Max Upload: ' . ini_get('upload_max_filesize') . '</h5>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="mt-3">';
            echo '<a href="?phpinfo=1" class="btn btn-outline-secondary me-2">';
            echo '<i class="bi bi-code-square me-1"></i> PHP Info';
            echo '</a>';
            
            echo '<a href="/" class="btn btn-outline-primary">';
            echo '<i class="bi bi-house-door me-1"></i> Go to Homepage';
            echo '</a>';
            echo '</div>';
            
            echo '</div>'; // Close test-section
            
            // Display phpinfo if requested
            if (isset($_GET['phpinfo'])) {
                phpinfo();
            }
            ?>
            
        </div> <!-- Close test-container -->
    </div> <!-- Close container -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
