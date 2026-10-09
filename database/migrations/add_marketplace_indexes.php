<?php
/**
 * Migration: Add composite indexes for marketplace and list-property queries
 * Run: php database/migrations/add_marketplace_indexes.php
 */

$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    $indexes = [
        // MarketplaceController::index() - main listing query
        // WHERE up.status = 'approved' AND up.property_type = ? AND up.listing_type = ?
        // AND up.price >= ? AND up.price <= ? AND (up.location LIKE ? OR up.city_name LIKE ? OR up.address LIKE ?)
        // ORDER BY up.is_premium DESC, up.is_featured DESC, up.is_urgent DESC, up.created_at DESC
        "CREATE INDEX idx_up_marketplace_filters ON user_properties (status, property_type, listing_type, price, created_at)",

        // MarketplaceController::index() - premium carousel
        // WHERE up.status = 'approved' AND (up.is_premium = 1 OR up.is_featured = 1 OR up.is_urgent = 1)
        // ORDER BY up.is_premium DESC, up.created_at DESC
        "CREATE INDEX idx_up_premium_featured ON user_properties (status, is_premium, is_featured, is_urgent, created_at)",

        // MarketplaceController::index() - facets
        "CREATE INDEX idx_up_status_type ON user_properties (status, property_type)",
        "CREATE INDEX idx_up_status_listing ON user_properties (status, listing_type)",

        // PropertyPageController::rentProperty() - rent listings
        // WHERE listing_type = 'rent' AND status IN ('verified','approved')
        // AND filters on location, property_type, price, bedrooms
        "CREATE INDEX idx_up_rent_filters ON user_properties (listing_type, status, property_type, price, bedrooms, city_name)",

        // PropertyPageController::handlePropertyListing() - idempotency check
        // WHERE user_id = ? AND phone = ? AND price = ? AND property_type = ? AND listing_type = ?
        "CREATE UNIQUE INDEX idx_up_idempotency ON user_properties (user_id, phone, price, property_type, listing_type, created_at)",

        // PropertyPageController::saveListingImage() + property_images joins
        // JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
        "CREATE INDEX idx_pi_property_primary ON property_images (property_id, is_primary)",

        // MarketplaceController::index() - location search
        "CREATE INDEX idx_up_location_city ON user_properties (location, city_name)",

        // PropertyPageController::handlePropertyListing() - owner lookups
        "CREATE INDEX idx_up_user_status ON user_properties (user_id, status)",
    ];

    foreach ($indexes as $i => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Index " . ($i + 1) . " created: " . $sql . "\n";
        } catch (PDOException $e) {
            // Index might already exist
            if (strpos($e->getMessage(), 'Duplicate key name') !== false || 
                strpos($e->getMessage(), 'Duplicate index') !== false ||
                $e->getCode() === '42000') {
                echo "⚠️  Index " . ($i + 1) . " already exists: " . $sql . "\n";
            } else {
                throw $e;
            }
        }
    }

    echo "\n✅ All marketplace indexes processed.\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}