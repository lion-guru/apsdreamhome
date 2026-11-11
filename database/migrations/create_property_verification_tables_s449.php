<?php
/**
 * Migration: create the property-verification tables
 *
 * The property-verification module is COMPLETE code but was never migrated:
 *
 *   - App\Services\PropertyVerificationService   (441 lines - full CRUD)
 *   - App\Http\Controllers\Api\PropertyVerificationApiController
 *   - 18 live mobile API routes (/api/v2/mobile/property-verification/*)
 *
 * ...but all FOUR of its tables were missing, so every one of those routes
 * 500'd with "Table 'apsdreamhome.property_verification_levels' doesn't
 * exist" (and the sub-calls failed on the next missing table).
 *
 * The feature IS customer-facing: both public pages advertise it with the
 * same four priced levels -
 *
 *   app/views/pages/property_verification.php
 *   mobile/.../presentation/pages/tools/property_verification_page.dart
 *
 *     Basic    ?0     (Free)     - Online records check, RERA status
 *     Premium  ?999   (Popular)  - + Physical site visit
 *     Gold     ?2,499             - + Legal opinion letter
 *     Platinum ?4,999             - + Title insurance
 *
 * so the levels table is seeded from that published copy - the API and the
 * marketing page then tell the customer the same story.
 *
 * Schema below is derived from the LIVE column usage of
 * PropertyVerificationService (every column the service reads or writes),
 * not from a guess.
 *
 * Idempotent: CREATE TABLE IF NOT EXISTS + INSERT ... WHERE NOT EXISTS for
 * the seed. Re-running is a no-op.
 *
 * Run via: php database/migrations/create_property_verification_tables_s449.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "OK  Database connected\n\n";
} catch (\Throwable $e) {
    echo "ERR Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$tables = [

    // ---- verification levels (the priced plans) -------------------------
    'property_verification_levels' => "CREATE TABLE IF NOT EXISTS `property_verification_levels` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL,
        `description` VARCHAR(255) NULL DEFAULT NULL,
        `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `features` TEXT NULL DEFAULT NULL,
        `icon` VARCHAR(100) NULL DEFAULT NULL,
        `badge_color` VARCHAR(20) NULL DEFAULT NULL,
        `validity_days` INT(11) NULL DEFAULT NULL,
        `sort_order` INT(11) NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_pvl_tenant_slug` (`tenant_id`, `slug`),
        KEY `idx_pvl_active` (`is_active`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // ---- verification requests ----------------------------------------
    'property_verification_requests' => "CREATE TABLE IF NOT EXISTS `property_verification_requests` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
        `property_id` INT(11) NOT NULL,
        `property_type` VARCHAR(50) NOT NULL DEFAULT 'plot',
        `verification_level_id` INT(11) NOT NULL,
        `requested_by` INT(11) NOT NULL,
        `assigned_to` INT(11) NULL DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'draft',
        `priority` VARCHAR(50) NOT NULL DEFAULT 'standard',
        `title_deed` VARCHAR(255) NULL DEFAULT NULL,
        `sale_deed` VARCHAR(255) NULL DEFAULT NULL,
        `tax_receipts` VARCHAR(255) NULL DEFAULT NULL,
        `encumbrance_certificate` VARCHAR(255) NULL DEFAULT NULL,
        `approved_building_plan` VARCHAR(255) NULL DEFAULT NULL,
        `identity_proof` VARCHAR(255) NULL DEFAULT NULL,
        `additional_documents` TEXT NULL DEFAULT NULL,
        `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        `payment_id` VARCHAR(100) NULL DEFAULT NULL,
        `verification_notes` TEXT NULL DEFAULT NULL,
        `inspection_scheduled_at` DATETIME NULL DEFAULT NULL,
        `inspection_completed_at` DATETIME NULL DEFAULT NULL,
        `inspector_id` INT(11) NULL DEFAULT NULL,
        `inspection_report` TEXT NULL DEFAULT NULL,
        `inspection_photos` TEXT NULL DEFAULT NULL,
        `badge_verification_code` VARCHAR(100) NULL DEFAULT NULL,
        `badge_awarded_at` DATETIME NULL DEFAULT NULL,
        `badge_expires_at` DATETIME NULL DEFAULT NULL,
        `reviewed_at` DATETIME NULL DEFAULT NULL,
        `approved_at` DATETIME NULL DEFAULT NULL,
        `rejected_at` DATETIME NULL DEFAULT NULL,
        `rejection_reason` TEXT NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_pvr_requested_by` (`requested_by`, `status`),
        KEY `idx_pvr_property` (`property_id`),
        KEY `idx_pvr_level` (`verification_level_id`),
        KEY `idx_pvr_tenant` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // ---- uploaded documents -------------------------------------------
    'property_verification_documents' => "CREATE TABLE IF NOT EXISTS `property_verification_documents` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
        `request_id` INT(11) NOT NULL,
        `document_type` VARCHAR(100) NOT NULL,
        `file_path` VARCHAR(255) NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `file_size` INT(11) NOT NULL DEFAULT 0,
        `mime_type` VARCHAR(100) NULL DEFAULT NULL,
        `uploaded_by` INT(11) NOT NULL,
        `verified` TINYINT(1) NOT NULL DEFAULT 0,
        `verified_by` INT(11) NULL DEFAULT NULL,
        `verified_at` DATETIME NULL DEFAULT NULL,
        `verification_notes` TEXT NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_pvd_request` (`request_id`),
        KEY `idx_pvd_tenant` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // ---- issued badges --------------------------------------------------
    'property_verification_badges' => "CREATE TABLE IF NOT EXISTS `property_verification_badges` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
        `request_id` INT(11) NOT NULL,
        `property_id` INT(11) NOT NULL,
        `verification_level_id` INT(11) NOT NULL,
        `badge_code` VARCHAR(100) NOT NULL,
        `issued_at` DATETIME NOT NULL,
        `issued_by` INT(11) NULL DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'active',
        `revoked_by` INT(11) NULL DEFAULT NULL,
        `revocation_reason` TEXT NULL DEFAULT NULL,
        `revoked_at` DATETIME NULL DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_pvb_code` (`badge_code`),
        KEY `idx_pvb_request` (`request_id`),
        KEY `idx_pvb_property` (`property_id`),
        KEY `idx_pvb_tenant` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tables as $name => $ddl) {
    $exists = $pdo->query("SHOW TABLES LIKE '$name'")->fetch();
    if ($exists) {
        echo "SKIP table $name already exists\n";
        continue;
    }
    try {
        $pdo->exec($ddl);
        echo "OK  created table $name\n";
    } catch (\Throwable $e) {
        echo "ERR failed to create $name: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ---------------------------------------------------------------------------
// Seed the four published levels (Basic / Premium / Gold / Platinum).
// Copy taken from app/views/pages/property_verification.php + the Flutter
// property_verification_page.dart so the API and the marketing page agree.
// ---------------------------------------------------------------------------
$levels = [
    [
        'name' => 'Basic',
        'slug' => 'basic',
        'description' => 'Online document verification and basic background check',
        'price' => 0.00,
        'features' => ['Online records check', 'RERA status verification', 'Basic badge'],
        'icon' => 'fa-shield-alt',
        'badge_color' => '#198754',
        'sort_order' => 1,
    ],
    [
        'name' => 'Premium',
        'slug' => 'premium',
        'description' => 'Full document + physical verification',
        'price' => 999.00,
        'features' => ['All Basic features', 'Physical site visit', 'Premium badge'],
        'icon' => 'fa-shield-alt',
        'badge_color' => '#0d6efd',
        'sort_order' => 2,
    ],
    [
        'name' => 'Gold',
        'slug' => 'gold',
        'description' => 'Complete verification with legal opinion',
        'price' => 2499.00,
        'features' => ['All Premium features', 'Legal opinion letter', 'Gold badge'],
        'icon' => 'fa-shield-alt',
        'badge_color' => '#ffc107',
        'sort_order' => 3,
    ],
    [
        'name' => 'Platinum',
        'slug' => 'platinum',
        'description' => 'Full verification + title insurance',
        'price' => 4999.00,
        'features' => ['All Gold features', 'Title insurance', 'Platinum badge'],
        'icon' => 'fa-shield-alt',
        'badge_color' => '#6f42c1',
        'sort_order' => 4,
    ],
];

$seed = $pdo->prepare(
    "INSERT INTO property_verification_levels
        (tenant_id, name, slug, description, price, features, icon, badge_color, sort_order, is_active)
     SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, 1
     FROM DUAL
     WHERE NOT EXISTS (
        SELECT 1 FROM property_verification_levels WHERE tenant_id = ? AND slug = ?
     )"
);

foreach ($levels as $l) {
    $seed->execute([
        1,
        $l['name'],
        $l['slug'],
        $l['description'],
        $l['price'],
        json_encode($l['features']),
        $l['icon'],
        $l['badge_color'],
        $l['sort_order'],
        1,
        $l['slug'],
    ]);
    echo sprintf("  seed %-9s ?%-9s features=%d\n", $l['name'], number_format($l['price'], 0), count($l['features']));
}

// Verify the columns the service actually reads/writes really exist.
$required = [
    'property_verification_levels' => ['id', 'tenant_id', 'name', 'slug', 'description', 'price', 'features', 'icon', 'badge_color', 'validity_days', 'sort_order', 'is_active', 'created_at', 'updated_at'],
    'property_verification_requests' => ['id', 'tenant_id', 'property_id', 'property_type', 'verification_level_id', 'requested_by', 'assigned_to', 'status', 'priority', 'title_deed', 'sale_deed', 'tax_receipts', 'encumbrance_certificate', 'approved_building_plan', 'identity_proof', 'additional_documents', 'amount_paid', 'payment_status', 'payment_id', 'verification_notes', 'inspection_scheduled_at', 'inspection_completed_at', 'inspector_id', 'inspection_report', 'inspection_photos', 'badge_verification_code', 'badge_awarded_at', 'badge_expires_at', 'reviewed_at', 'approved_at', 'rejected_at', 'rejection_reason', 'created_at', 'updated_at'],
    'property_verification_documents' => ['id', 'tenant_id', 'request_id', 'document_type', 'file_path', 'file_name', 'file_size', 'mime_type', 'uploaded_by', 'verified', 'verified_by', 'verified_at', 'verification_notes', 'created_at', 'updated_at'],
    'property_verification_badges' => ['id', 'tenant_id', 'request_id', 'property_id', 'verification_level_id', 'badge_code', 'issued_at', 'issued_by', 'status', 'revoked_by', 'revocation_reason', 'revoked_at', 'created_at', 'updated_at'],
];

$bad = 0;
foreach ($required as $table => $cols) {
    $missing = [];
    foreach ($cols as $c) {
        $r = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$c'")->fetch();
        if (!$r) { $missing[] = $c; }
    }
    if ($missing) {
        echo "ERR $table missing: " . implode(', ', $missing) . "\n";
        $bad++;
    } else {
        echo "OK  verified $table (" . count($cols) . " columns)\n";
    }
}
if ($bad) { exit(1); }

$count = $pdo->query("SELECT COUNT(*) FROM property_verification_levels")->fetchColumn();
echo "\nlevels seeded: $count\nDONE\n";
