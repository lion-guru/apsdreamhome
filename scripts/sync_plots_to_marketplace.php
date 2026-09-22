<?php
/**
 * Sync Plots to Marketplace Properties
 * Maps active available plots from plots table to properties table for public marketplace display
 */

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

use App\Core\Database\Database;

echo "=== Plots to Marketplace Sync ===\n\n";

$db = Database::getInstance();

// Get admin user for created_by
$admin = $db->fetchOne("SELECT id FROM users WHERE role IN ('admin','super_admin') ORDER BY id LIMIT 1");
$adminId = $admin ? $admin['id'] : 1;
echo "Using admin ID: $adminId\n\n";

// Get active colonies with site_id mapping
$colonies = $db->fetchAll("SELECT c.id, c.name, c.slug, s.id as site_id, s.city, s.state, s.pincode 
    FROM colonies c 
    LEFT JOIN sites s ON LOWER(TRIM(s.site_name)) = LOWER(TRIM(c.name))
    WHERE c.is_active = 1");

echo "Colonies with site mapping:\n";
foreach ($colonies as $colony) {
    $siteId = $colony['site_id'] ?? 'NULL';
    $city = $colony['city'] ?? 'NULL';
    echo "  - {$colony['name']} (id={$colony['id']}, site_id={$siteId}, city={$city})\n";
}
echo "\n";

// Get available plots from active colonies
$plots = $db->fetchAll("
    SELECT p.*, c.name as colony_name, c.slug as colony_slug
    FROM plots p
    JOIN colonies c ON p.colony_id = c.id
    WHERE p.is_active = 1 
    AND c.is_active = 1
    AND p.status = 'available'
    ORDER BY p.colony_id, p.plot_number
");

echo "Found " . count($plots) . " available plots to sync\n\n";

// Plot type mapping
$typeMap = [
    'residential' => 'land',
    'commercial' => 'commercial',
    'industrial' => 'commercial',
    'mixed' => 'commercial',
];

$categoryMap = [
    'residential' => 1,   // Residential
    'commercial' => 2,    // Commercial
    'industrial' => 3,    // Industrial
    'mixed' => 5,         // Mixed Use
];

$inserted = 0;
$skipped = 0;
$errors = 0;

foreach ($plots as $plot) {
    $colonyName = $plot['colony_name'];
    $siteId = null;
    
    // Find matching site
    foreach ($colonies as $colony) {
        if ($colony['id'] == $plot['colony_id'] && $colony['site_id']) {
            $siteId = $colony['site_id'];
            break;
        }
    }
    
    if (!$siteId) {
        echo "SKIP: No site mapping for colony '$colonyName' (plot {$plot['plot_number']})\n";
        $skipped++;
        continue;
    }
    
    // Find colony data for city/state/pincode
    $colonyData = null;
    foreach ($colonies as $colony) {
        if ($colony['id'] == $plot['colony_id']) {
            $colonyData = $colony;
            break;
        }
    }
    
    $city = $colonyData['city'] ?? 'Gorakhpur';
    $state = $colonyData['state'] ?? 'Uttar Pradesh';
    $pincode = $colonyData['pincode'] ?? '273008';
    $countryId = 101; // India
    
    // Determine property type
    $plotType = $plot['plot_type'] ?? 'residential';
    $propType = $typeMap[$plotType] ?? 'land';
    $categoryId = $categoryMap[$plotType] ?? 1;
    
    // Calculate price
    $price = 0;
    if ($plot['total_price'] && $plot['total_price'] > 0) {
        $price = (float)$plot['total_price'];
    } elseif ($plot['price_per_sqft'] && $plot['area_sqft']) {
        $price = (float)$plot['price_per_sqft'] * (float)$plot['area_sqft'];
    } elseif ($plot['base_price_per_sqft'] && $plot['area_sqft']) {
        $price = (float)$plot['base_price_per_sqft'] * (float)$plot['area_sqft'];
    }
    
    if ($price <= 0) {
        echo "SKIP: Plot {$plot['plot_number']} has zero/negative price\n";
        $skipped++;
        continue;
    }
    
    // Cap price at max for decimal(10,2) = 99,999,999.99
    if ($price > 99999999.99) {
        $price = 99999999.99;
    }
    
    // Build title and description
    $title = "{$plotType} Plot {$plot['plot_number']} in {$colonyName}";
    if ($plot['block']) $title .= " (Block {$plot['block']})";
    
    $desc = "{$plotType} plot in {$colonyName}";
    if ($plot['area_sqft']) $desc .= ", Area: " . number_format($plot['area_sqft'], 2) . " sqft";
    if ($plot['facing']) $desc .= ", Facing: {$plot['facing']}";
    if ($plot['corner_plot']) $desc .= ", Corner Plot";
    if ($plot['park_facing']) $desc .= ", Park Facing";
    if ($plot['road_width_ft']) $desc .= ", Road Width: {$plot['road_width_ft']} ft";
    if ($plot['description']) $desc .= ". " . $plot['description'];
    
    // Location string
    $location = "{$colonyName}, {$city}";
    
    // Area
    $areaSqft = (float)($plot['area_sqft'] ?? 0);
    
    // Coordinates
    $lat = $plot['latitude'] ?? null;
    $lng = $plot['longitude'] ?? null;
    
    // Features/amenities
    $features = [];
    if ($plot['corner_plot']) $features[] = 'Corner Plot';
    if ($plot['park_facing']) $features[] = 'Park Facing';
    if ($plot['road_width_ft']) $features[] = "{$plot['road_width_ft']} ft Road";
    if ($plot['facing']) $features[] = "{$plot['facing']} Facing";
    $featuresStr = implode(', ', $features);
    
    // Amenities from colony
    $amenities = "Gated Community, 24/7 Security, Water Supply, Electricity, Drainage, Parks";
    
    // Determine project_id - only use if it exists in projects table
    $projectId = null;
    $projectExists = $db->fetchOne("SELECT id FROM projects WHERE id = ?", [$plot['colony_id']]);
    if ($projectExists) {
        $projectId = $plot['colony_id'];
    }
    
    try {
        $id = $db->insert('properties', [
            'tenant_id' => 1,
            'title' => $title,
            'description' => $desc,
            'price' => $price,
            'location' => $location,
            'pincode' => $pincode,
            'type' => $propType,
            'category_id' => $categoryId,
            'site_id' => $siteId,
            'project_id' => $projectId,
            'plot_id' => $plot['id'],
            'land_id' => $plot['land_parcel_id'] ?? null,
            'status' => 'active',
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'city' => $city,
            'state' => $state,
            'country_id' => $countryId,
            'bedrooms' => 0,
            'bathrooms' => 0,
            'area' => $areaSqft,
            'area_sqft' => $areaSqft,
            'area_unit' => 'sqft',
            'latitude' => $lat,
            'longitude' => $lng,
            'featured' => 0,
            'amenities' => $amenities,
            'features' => $featuresStr,
        ]);
        $inserted++;
        
        if ($inserted % 50 === 0) {
            echo "Inserted $inserted properties...\n";
        }
    } catch (\Exception $e) {
        echo "ERROR inserting plot {$plot['plot_number']}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Inserted: $inserted\n";
echo "Skipped: $skipped\n";
echo "Errors: $errors\n";
echo "Total plots processed: " . count($plots) . "\n";

// Verify
$count = $db->fetchOne("SELECT COUNT(*) as cnt FROM properties WHERE plot_id IS NOT NULL");
echo "\nProperties with plot_id in marketplace: " . ($count['cnt'] ?? 0) . "\n";

echo "\nDone!\n";