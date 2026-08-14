<?php
require 'vendor/autoload.php';
require 'app/Core/Database.php';
$db = new App\Core\Database();
$pdo = $db->getPdo();

$items = [
    ['Drip Campaigns', 'fa-tint', '/admin/drip-campaigns', 'marketing', 99],
    ['Visit Scheduling', 'fa-calendar-check', '/admin/visits', 'operations', 95],
    ['Property Alerts', 'fa-bell', '/admin/property-alerts', 'marketing', 92],
    ['Marketing Campaigns', 'fa-bullhorn', '/admin/marketing-campaigns', 'marketing', 90],
    ['Property Comparison', 'fa-columns', '/property-comparison', 'marketing', 88],
    ['Customer Referrals', 'fa-gift', '/user/referral', 'crm', 90],
    ['Reviews & Testimonials', 'fa-star-half-alt', '/admin/reviews', 'crm', 88]
];
$added = 0;
foreach ($items as $i) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ?");
        $stmt->execute([$i[2]]);
        if (!$stmt->fetch()) {
            $pdo->prepare("INSERT INTO admin_menu_items (name, icon, url, parent_id, section, order_index, is_active, created_at) VALUES (?,?,?,0,?,?,1,NOW())")
                ->execute([$i[0], $i[1], $i[2], $i[3], $i[4]]);
            $added++;
        }
    } catch (Exception $e) { echo "Skip {$i[0]}: " . $e->getMessage() . "\n"; }
}
echo "Added $added new menu items\n";?>