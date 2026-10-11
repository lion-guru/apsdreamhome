<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$rules = [
    [
        'colony_id' => null,
        'name' => 'Corner Plot Premium',
        'attribute_key' => 'is_corner',
        'condition_type' => 'equals_true',
        'condition_value' => '1',
        'charge_type' => 'percentage',
        'charge_value' => 10.00,
        'priority' => 10,
    ],
    [
        'colony_id' => null,
        'name' => 'Park Facing Premium',
        'attribute_key' => 'is_park_facing',
        'condition_type' => 'equals_true',
        'condition_value' => '1',
        'charge_type' => 'percentage',
        'charge_value' => 5.00,
        'priority' => 20,
    ],
    [
        'colony_id' => null,
        'name' => 'Wide Road Premium (>=40ft)',
        'attribute_key' => 'road_width_ft',
        'condition_type' => 'greater_than',
        'condition_value' => '40',
        'charge_type' => 'percentage',
        'charge_value' => 10.00,
        'priority' => 30,
    ],
    [
        'colony_id' => null,
        'name' => 'Commercial Front Premium',
        'attribute_key' => 'is_commercial_front',
        'condition_type' => 'equals_true',
        'condition_value' => '1',
        'charge_type' => 'percentage',
        'charge_value' => 15.00,
        'priority' => 40,
    ],
    [
        'colony_id' => null,
        'name' => 'Corner Plot Fixed Premium',
        'attribute_key' => 'is_corner',
        'condition_type' => 'equals_true',
        'condition_value' => '1',
        'charge_type' => 'fixed_per_sqft',
        'charge_value' => 150.00,
        'priority' => 5,
    ],
    [
        'colony_id' => null,
        'name' => 'Park Facing Fixed Premium',
        'attribute_key' => 'is_park_facing',
        'condition_type' => 'equals_true',
        'condition_value' => '1',
        'charge_type' => 'fixed_per_sqft',
        'charge_value' => 75.00,
        'priority' => 15,
    ],
];

$db = \App\Core\Database\Database::getInstance()->getConnection();

foreach ($rules as $rule) {
    try {
        // Idempotent: skip names already seeded (safe to re-run).
        $exists = $db->fetchOne("SELECT id FROM plot_plc_rules WHERE name = ? LIMIT 1", [$rule['name']]);
        if ($exists) {
            echo "Skipped (exists): {$rule['name']}\n";
            continue;
        }
        $stmt = $db->prepare("
            INSERT INTO plot_plc_rules
            (colony_id, name, attribute_key, condition_type, condition_value, charge_type, charge_value, priority, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $rule['colony_id'] ?? null,
            $rule['name'],
            $rule['attribute_key'],
            $rule['condition_type'],
            $rule['condition_value'],
            $rule['charge_type'],
            $rule['charge_value'],
            $rule['priority'],
        ]);
        echo "Created: {$rule['name']}\n";
    } catch (\Exception $e) {
        echo "ERR: {$rule['name']} - " . $e->getMessage() . "\n";
    }
}

echo "Done seeding PLC rules.\n";