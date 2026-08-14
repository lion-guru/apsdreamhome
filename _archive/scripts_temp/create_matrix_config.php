<?php
/**
 * Session 51: Create mlm_matrix_config table + add commission_path column to mlm_network_tree
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database.php';
$db = App\Core\Database\Database::getInstance();
$pdo = $db->getConnection();

echo "=== Creating mlm_matrix_config table ===\n";
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS mlm_matrix_config (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(50) NOT NULL UNIQUE,
        config_value VARCHAR(255) NOT NULL,
        description VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table created.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Seeding matrix config defaults ===\n";
$configs = [
    ['display_mode', 'hybrid', 'Tree display mode: binary, unilevel, or hybrid'],
    ['default_view', 'hybrid', 'Default view for genealogy page'],
    ['show_commission_paths', '1', 'Overlay commission flow paths on tree'],
    ['max_display_levels', '8', 'Maximum tree levels to display'],
    ['show_rank_badges', '1', 'Show rank badges on tree nodes'],
    ['show_volume_info', '1', 'Show BV/volume info on tree nodes'],
    ['tree_layout', 'tree', 'D3 layout: tree or cluster'],
    ['enable_matrix_view', '1', 'Enable matrix (forced placement) view'],
    ['matrix_width', '3', 'Matrix width for forced placement (2=binary, 3=trinary)'],
    ['show_earning_paths', '1', 'Highlight earning commission paths'],
];
$ins = $pdo->prepare("INSERT IGNORE INTO mlm_matrix_config (config_key, config_value, description) VALUES (?, ?, ?)");
foreach ($configs as $c) {
    $ins->execute($c);
    echo "  Set: {$c[0]} = {$c[1]}\n";
}

echo "\n=== Adding commission_path column to mlm_network_tree ===\n";
try {
    $pdo->exec("ALTER TABLE mlm_network_tree ADD COLUMN commission_path VARCHAR(500) DEFAULT NULL COMMENT 'JSON-encoded commission earning path'");
    echo "Column added.\n";
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . " (column may already exist)\n";
}

echo "\n=== Adding bv columns to mlm_network_tree ===\n";
try {
    $pdo->exec("ALTER TABLE mlm_network_tree ADD COLUMN personal_bv DECIMAL(12,2) DEFAULT 0 COMMENT 'Personal business volume'");
    echo "personal_bv added.\n";
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . " (column may already exist)\n";
}
try {
    $pdo->exec("ALTER TABLE mlm_network_tree ADD COLUMN total_team_bv DECIMAL(12,2) DEFAULT 0 COMMENT 'Total team business volume'");
    echo "total_team_bv added.\n";
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . " (column may already exist)\n";
}

echo "\n=== Done! ===\n";?>