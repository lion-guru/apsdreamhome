<?php
/**
 * Migration: Seed Initial Verified Rental Properties in Gorakhpur
 * Safe & Idempotent: Checks if any rentals exist; if 0, seeds 5 verified listings.
 * Zero drift on users, plots, leads.
 */

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$rentCount = (int)$pdo->query("SELECT COUNT(*) FROM user_properties WHERE listing_type = 'rent'")->fetchColumn();

if ($rentCount === 0) {
    // Get an admin/system user ID for foreign key
    $firstUserId = (int)$pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;

    $rentals = [
        [
            'tenant_id' => 1,
            'user_id' => $firstUserId,
            'posted_by' => $firstUserId,
            'posted_by_type' => 'owner',
            'name' => 'Luxury 3 BHK Flat - Medical Road',
            'phone' => '9277121112',
            'email' => 'rentals@apsdreamhome.com',
            'property_type' => 'flat',
            'listing_type' => 'rent',
            'address' => 'Near BRD Medical College, Medical College Road, Gorakhpur, UP 273013',
            'location' => 'Medical Road, Gorakhpur',
            'area_sqft' => 1450,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'furnished' => 'semi-furnished',
            'price' => 18000.00,
            'price_type' => 'per_month',
            'description' => 'Spacious 3 BHK flat with modular kitchen, 2 balconies, covered car parking, 24x7 water and lift backup. Located 500m from BRD Medical College.',
            'image' => 'assets/images/placeholder/property.svg',
            'status' => 'verified',
            'is_featured' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        [
            'tenant_id' => 1,
            'user_id' => $firstUserId,
            'posted_by' => $firstUserId,
            'posted_by_type' => 'owner',
            'name' => 'Independent 2 BHK House near AIIMS',
            'phone' => '9277121112',
            'email' => 'rentals@apsdreamhome.com',
            'property_type' => 'house',
            'listing_type' => 'rent',
            'address' => 'Sector 4, Near AIIMS Campus, Kunraghat, Gorakhpur, UP 273008',
            'location' => 'AIIMS Road, Gorakhpur',
            'area_sqft' => 1100,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'furnished' => 'furnished',
            'price' => 12500.00,
            'price_type' => 'per_month',
            'description' => 'Beautiful independent ground-floor house with private garden, AC fittings, RO water, and dedicated bike/car parking. Suitable for doctors, hospital staff, and families.',
            'image' => 'assets/images/placeholder/property.svg',
            'status' => 'verified',
            'is_featured' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        [
            'tenant_id' => 1,
            'user_id' => $firstUserId,
            'posted_by' => $firstUserId,
            'posted_by_type' => 'owner',
            'name' => 'Commercial Road-Facing Shop - Taramandal',
            'phone' => '9277121112',
            'email' => 'rentals@apsdreamhome.com',
            'property_type' => 'shop',
            'listing_type' => 'rent',
            'address' => 'Taramandal Main Road, Opp. Nauhar Lake, Gorakhpur, UP 273016',
            'location' => 'Taramandal, Gorakhpur',
            'area_sqft' => 450,
            'bedrooms' => 0,
            'bathrooms' => 1,
            'furnished' => 'unfurnished',
            'price' => 22000.00,
            'price_type' => 'per_month',
            'description' => 'High-visibility corner commercial shop with shutter, 15ft front road, washroom, and heavy footfall. Ideal for medical store, boutique, clinic, or cafe.',
            'image' => 'assets/images/placeholder/property.svg',
            'status' => 'verified',
            'is_featured' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        [
            'tenant_id' => 1,
            'user_id' => $firstUserId,
            'posted_by' => $firstUserId,
            'posted_by_type' => 'owner',
            'name' => 'Compact 1 BHK Studio Flat in Golghar',
            'phone' => '9277121112',
            'email' => 'rentals@apsdreamhome.com',
            'property_type' => 'flat',
            'listing_type' => 'rent',
            'address' => 'Baldev Plaza Lane, Golghar Central, Gorakhpur, UP 273001',
            'location' => 'Golghar, Gorakhpur',
            'area_sqft' => 550,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'furnished' => 'furnished',
            'price' => 7500.00,
            'price_type' => 'per_month',
            'description' => 'Fully furnished studio apartment in the heart of Gorakhpur city. Bed, wardrobe, geyser, kitchen counter. Walking distance to shops, banks, and Railway Station.',
            'image' => 'assets/images/placeholder/property.svg',
            'status' => 'verified',
            'is_featured' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        [
            'tenant_id' => 1,
            'user_id' => $firstUserId,
            'posted_by' => $firstUserId,
            'posted_by_type' => 'owner',
            'name' => 'Spacious 3 BHK Villa in Rapti Nagar',
            'phone' => '9277121112',
            'email' => 'rentals@apsdreamhome.com',
            'property_type' => 'villa',
            'listing_type' => 'rent',
            'address' => 'Rapti Nagar Phase 4, Chargawan Road, Gorakhpur, UP 273004',
            'location' => 'Rapti Nagar, Gorakhpur',
            'area_sqft' => 1800,
            'bedrooms' => 3,
            'bathrooms' => 3,
            'furnished' => 'semi-furnished',
            'price' => 24000.00,
            'price_type' => 'per_month',
            'description' => 'Independent duplex villa with spacious hall, private terrace, 3 master bedrooms with attached baths, and covered porch. Quiet gated colony setting.',
            'image' => 'assets/images/placeholder/property.svg',
            'status' => 'verified',
            'is_featured' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO user_properties (
            tenant_id, user_id, posted_by, posted_by_type, name, phone, email,
            property_type, listing_type, address, location, area_sqft, bedrooms,
            bathrooms, furnished, price, price_type, description, image, status,
            is_featured, created_at, updated_at
        ) VALUES (
            :tenant_id, :user_id, :posted_by, :posted_by_type, :name, :phone, :email,
            :property_type, :listing_type, :address, :location, :area_sqft, :bedrooms,
            :bathrooms, :furnished, :price, :price_type, :description, :image, :status,
            :is_featured, :created_at, :updated_at
        )
    ");

    foreach ($rentals as $item) {
        $stmt->execute($item);
    }
    echo "Seeded 5 initial verified rental listings in Gorakhpur.\n";
} else {
    echo "Rentals already exist ($rentCount listings). Skipping seed.\n";
}
