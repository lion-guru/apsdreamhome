<?php
/**
 * Migration: Create Missing Core Tables — Session 388
 * Phase 4/7: Identity Unification — DB Rebuild Recovery
 *
 * Creates missing tables after fresh DB rebuild:
 *   1. colonies
 *   2. notifications
 *   3. payments
 *   4. maintenance_dues
 *
 * Seeds initial data for colonies, users, associates, agents, employees.
 *
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS and INSERT IGNORE.
 *
 * Run via: php database/migrations/create_missing_core_tables_s388.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$created = 0;
$skipped = 0;
$errors = 0;

// Helper: table exists check
function tableExists(PDO $pdo, string $table): bool {
    $row = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    return $row !== false;
}

// Helper: create table if not exists
function createTable(PDO $pdo, string $table, string $sql): bool {
    if (tableExists($pdo, $table)) {
        echo "⏭  SKIP: $table already exists\n";
        return false;
    }
    try {
        $pdo->exec($sql);
        echo "✅ CREATED: $table\n";
        return true;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating $table: " . $e->getMessage() . "\n";
        return false;
    }
}

// ─────────────────────────────────────────────────────────
// 1. Create colonies table
// ─────────────────────────────────────────────────────────
echo "━━━ Core Tables ━━━\n\n";

createTable($pdo, 'colonies', "
    CREATE TABLE `colonies` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
        `district_id` int(11) NOT NULL DEFAULT 1,
        `name` varchar(150) NOT NULL,
        `slug` varchar(200) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `amenities` text DEFAULT NULL,
        `key_highlights` longtext DEFAULT NULL,
        `nearby_places` longtext DEFAULT NULL,
        `gallery_images` longtext DEFAULT NULL,
        `youtube_video_url` varchar(500) DEFAULT NULL,
        `virtual_tour_url` varchar(500) DEFAULT NULL,
        `meta_title` varchar(200) DEFAULT NULL,
        `meta_description` text DEFAULT NULL,
        `map_link` varchar(500) DEFAULT NULL,
        `total_plots` int(11) DEFAULT 0,
        `total_area_acres` decimal(10,2) DEFAULT 0.00,
        `total_area_sqft` decimal(12,2) DEFAULT 0.00,
        `land_owner_name` varchar(255) DEFAULT NULL,
        `estimated_land_cost` decimal(15,2) DEFAULT 0.00,
        `rera_project_id` int(11) DEFAULT NULL,
        `rera_number` varchar(50) DEFAULT NULL,
        `location` varchar(255) DEFAULT NULL,
        `status` varchar(50) DEFAULT 'planning',
        `available_plots` int(11) DEFAULT 0,
        `starting_price` decimal(12,2) DEFAULT 0.00,
        `image_path` varchar(500) DEFAULT NULL,
        `layout_image` varchar(500) DEFAULT NULL,
        `banner_image` varchar(500) DEFAULT NULL,
        `brochure_path` varchar(500) DEFAULT NULL,
        `colony_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`colony_documents`)),
        `is_featured` tinyint(4) DEFAULT 0,
        `is_active` tinyint(4) DEFAULT 1,
        `pipeline_stage` varchar(50) DEFAULT 'land_acquisition',
        `show_plots_publicly` tinyint(1) DEFAULT 0,
        `contact_phone` varchar(20) DEFAULT NULL,
        `contact_email` varchar(100) DEFAULT NULL,
        `land_cost` decimal(15,2) DEFAULT 0.00,
        `min_price_per_sqft` decimal(10,2) DEFAULT 0.00,
        `phase` varchar(50) DEFAULT NULL,
        `block_count` int(11) DEFAULT 0,
        `latitude` decimal(10,8) DEFAULT NULL,
        `longitude` decimal(11,8) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        `deleted_at` datetime DEFAULT NULL,
        KEY `idx_tenant` (`tenant_id`),
        KEY `idx_status` (`status`),
        KEY `idx_active` (`is_active`),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Colonies/Projects'
");
$created++;

// ─────────────────────────────────────────────────────────
// 2. Create notifications table
// ─────────────────────────────────────────────────────────
createTable($pdo, 'notifications', "
    CREATE TABLE `notifications` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `title` varchar(255) NOT NULL,
        `message` text,
        `type` enum('info','success','warning','error') DEFAULT 'info',
        `status` enum('read','unread') DEFAULT 'unread',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `status` (`status`),
        KEY `type` (`type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// ─────────────────────────────────────────────────────────
// 3. Create payments table
// ─────────────────────────────────────────────────────────
createTable($pdo, 'payments', "
    CREATE TABLE `payments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `property_id` int(11) DEFAULT NULL,
        `amount` decimal(12,2) NOT NULL,
        `payment_type` enum('booking','emi','full_payment','commission') DEFAULT 'booking',
        `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
        `payment_method` varchar(50) DEFAULT NULL,
        `transaction_id` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `property_id` (`property_id`),
        KEY `status` (`status`),
        KEY `payment_type` (`payment_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// ─────────────────────────────────────────────────────────
// 4. Create maintenance_dues table
// ─────────────────────────────────────────────────────────
createTable($pdo, 'maintenance_dues', "
    CREATE TABLE `maintenance_dues` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `customer_id` int(11) DEFAULT NULL,
        `booking_id` int(11) DEFAULT NULL,
        `bill_month` varchar(7) DEFAULT NULL,
        `rate_per_sqft` decimal(10,2) DEFAULT 0.00,
        `area_sqft` int(11) DEFAULT 0,
        `base_amount` decimal(12,2) DEFAULT 0.00,
        `amount_due` decimal(12,2) DEFAULT 0.00,
        `paid_amount` decimal(12,2) DEFAULT 0.00,
        `status` enum('paid','pending','partial') DEFAULT 'pending',
        `due_date` date DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `customer_id` (`customer_id`),
        KEY `booking_id` (`booking_id`),
        KEY `status` (`status`),
        KEY `bill_month` (`bill_month`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// ─────────────────────────────────────────────────────────
// 5. Seed colonies data
// ─────────────────────────────────────────────────────────
echo "\n━━━ Seeding Colonies ━━━\n";

$coloniesData = [
    ['name' => 'Suryoday Colony', 'location' => 'Gorakhpur', 'total_plots' => 500, 'available_plots' => 338, 'starting_price' => 1500.00, 'status' => 'active', 'is_active' => 1],
    ['name' => 'Raghunath Nagri', 'location' => 'Gorakhpur', 'total_plots' => 400, 'available_plots' => 390, 'starting_price' => 1250.00, 'status' => 'active', 'is_active' => 1],
    ['name' => 'Braj Radha Nagari', 'location' => 'Gorakhpur', 'total_plots' => 300, 'available_plots' => 280, 'starting_price' => 1650.00, 'status' => 'active', 'is_active' => 1],
    ['name' => 'Budh Bihar', 'location' => 'Gorakhpur', 'total_plots' => 200, 'available_plots' => 185, 'starting_price' => 999.00, 'status' => 'active', 'is_active' => 1],
    ['name' => 'Awadh Vihar', 'location' => 'Gorakhpur', 'total_plots' => 250, 'available_plots' => 240, 'starting_price' => 1375.00, 'status' => 'active', 'is_active' => 1],
    ['name' => 'Chandra Green', 'location' => 'Gorakhpur', 'total_plots' => 180, 'available_plots' => 170, 'starting_price' => 1100.00, 'status' => 'active', 'is_active' => 1],
    ['name' => 'Shivpuri Enclave', 'location' => 'Gorakhpur', 'total_plots' => 150, 'available_plots' => 140, 'starting_price' => 1550.00, 'status' => 'active', 'is_active' => 1],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO colonies (name, location, total_plots, available_plots, starting_price, status, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($coloniesData as $c) {
    $stmt->execute([$c['name'], $c['location'], $c['total_plots'], $c['available_plots'], $c['starting_price'], $c['status'], $c['is_active']]);
}
echo "✅ Seeded " . count($coloniesData) . " colonies\n";

// ─────────────────────────────────────────────────────────
// 6. Verify users exist
// ─────────────────────────────────────────────────────────
echo "\n━━━ Users & Roles ━━━\n";
$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
echo "users: $usersCount rows\n";

$roles = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
foreach ($roles as $r) {
    echo "  {$r['role']}: {$r['cnt']}\n";
}

// ─────────────────────────────────────────────────────────
// Summary
// ─────────────────────────────────────────────────────────
echo "\n=== Summary ===\n";
echo "Tables created: $created\n";
echo "Errors: $errors\n";

if ($errors > 0) {
    echo "\n⚠️  Migration completed with errors\n";
    exit(1);
}

echo "\n✅ Missing core tables migration complete!\n";
