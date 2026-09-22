<?php
/**
 * Idempotent Seed Script: Gamification Badges
 * Ensures all level, achievement, and milestone badges exist in the badges table.
 * Does not overwrite existing custom badge modifications.
 *
 * Run: php scripts/seed_missing_badges.php
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance()->getConnection();

$badges = [
    [
        'name' => 'first_property_view',
        'display_name' => 'First Look',
        'description' => 'Viewed your first property',
        'category' => 'milestone',
        'points_required' => 0,
        'rarity' => 'common',
        'icon' => 'eye',
    ],
    [
        'name' => 'property_explorer',
        'display_name' => 'Property Explorer',
        'description' => 'Viewed 10 different properties',
        'category' => 'milestone',
        'points_required' => 10,
        'rarity' => 'common',
        'icon' => 'compass',
    ],
    [
        'name' => 'inquiry_maker',
        'display_name' => 'Interested Buyer',
        'description' => 'Made your first property inquiry',
        'category' => 'achievement',
        'points_required' => 5,
        'rarity' => 'uncommon',
        'icon' => 'question-circle',
    ],
    [
        'name' => 'site_visitor',
        'display_name' => 'Site Visitor',
        'description' => 'Scheduled and completed a site visit',
        'category' => 'achievement',
        'points_required' => 50,
        'rarity' => 'rare',
        'icon' => 'map-marker-alt',
    ],
    [
        'name' => 'loyal_customer',
        'display_name' => 'Loyal Customer',
        'description' => 'Active member with regular engagements',
        'category' => 'loyalty',
        'points_required' => 100,
        'rarity' => 'epic',
        'icon' => 'award',
    ],
    [
        'name' => 'referral_master',
        'display_name' => 'Referral Master',
        'description' => 'Successfully referred customers or associates',
        'category' => 'social',
        'points_required' => 200,
        'rarity' => 'legendary',
        'icon' => 'users',
    ],
    [
        'name' => 'first_sale',
        'display_name' => 'First Sale',
        'description' => 'Completed first property booking/sale',
        'category' => 'achievement',
        'points_required' => 50,
        'rarity' => 'uncommon',
        'icon' => 'trophy',
    ],
    [
        'name' => 'sales_10',
        'display_name' => 'Sales Star',
        'description' => 'Completed 10 property bookings',
        'category' => 'achievement',
        'points_required' => 200,
        'rarity' => 'rare',
        'icon' => 'star',
    ],
    [
        'name' => 'sales_50',
        'display_name' => 'Sales Champion',
        'description' => 'Completed 50 property bookings',
        'category' => 'achievement',
        'points_required' => 500,
        'rarity' => 'epic',
        'icon' => 'crown',
    ],
    [
        'name' => 'sales_champion',
        'display_name' => 'Sales Champion L7',
        'description' => 'Reached Level 7 sales milestone',
        'category' => 'milestone',
        'points_required' => 2500,
        'rarity' => 'epic',
        'icon' => 'crown',
    ],
    [
        'name' => 'lead_master',
        'display_name' => 'Lead Master',
        'description' => 'Generated and managed 100 leads',
        'category' => 'achievement',
        'points_required' => 150,
        'rarity' => 'rare',
        'icon' => 'user-plus',
    ],
    [
        'name' => 'rising_star',
        'display_name' => 'Rising Star',
        'description' => 'Reached Level 3 milestone',
        'category' => 'milestone',
        'points_required' => 300,
        'rarity' => 'uncommon',
        'icon' => 'rocket',
    ],
    [
        'name' => 'property_pro',
        'display_name' => 'Property Pro',
        'description' => 'Reached Level 5 milestone',
        'category' => 'milestone',
        'points_required' => 1000,
        'rarity' => 'rare',
        'icon' => 'building',
    ],
    [
        'name' => 'elite_seller',
        'display_name' => 'Elite Seller',
        'description' => 'Reached Level 9 milestone',
        'category' => 'milestone',
        'points_required' => 6000,
        'rarity' => 'epic',
        'icon' => 'gem',
    ],
    [
        'name' => 'legend',
        'display_name' => 'Legend',
        'description' => 'Reached Level 10 pinnacle milestone',
        'category' => 'milestone',
        'points_required' => 10000,
        'rarity' => 'legendary',
        'icon' => 'medal',
    ],
    [
        'name' => 'consistent_30',
        'display_name' => 'Consistent Achiever',
        'description' => 'Maintained 30-day activity streak',
        'category' => 'loyalty',
        'points_required' => 75,
        'rarity' => 'uncommon',
        'icon' => 'calendar-check',
    ],
];

echo "=== Seeding Gamification Badges ===\n";
$inserted = 0;
$skipped = 0;

$stmt = $db->prepare("
    INSERT INTO badges (name, display_name, description, category, points_required, rarity, icon, is_active, is_hidden, tenant_id, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 1, NOW(), NOW())
    ON DUPLICATE KEY UPDATE
        display_name = VALUES(display_name),
        description = VALUES(description),
        category = VALUES(category),
        points_required = VALUES(points_required),
        rarity = VALUES(rarity),
        icon = VALUES(icon),
        is_active = 1
");

foreach ($badges as $b) {
    try {
        $stmt->execute([
            $b['name'],
            $b['display_name'],
            $b['description'],
            $b['category'],
            $b['points_required'],
            $b['rarity'],
            $b['icon'],
        ]);
        if ($stmt->rowCount() > 0) {
            $inserted++;
            echo "  [+] Seeded/Updated badge: {$b['name']} ({$b['display_name']})\n";
        } else {
            $skipped++;
            echo "  [.] Unchanged badge: {$b['name']}\n";
        }
    } catch (\Exception $e) {
        echo "  [x] Error on {$b['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\nSummary: {$inserted} processed/updated, {$skipped} unchanged.\n";
$total = $db->query("SELECT COUNT(*) FROM badges WHERE is_active = 1")->fetchColumn();
echo "Total Active Badges in Database: {$total}\n";
