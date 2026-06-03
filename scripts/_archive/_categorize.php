<?php
/**
 * Categorize all 145 scripts and output archive plan
 */
$scripts = glob('scripts/*.php');
$archive = [];
$keep = [];

$keepCategories = [
    'migrations' => ['create_migrations_table.php', 'track_migration.php', 'view_migrations.php'],
    'critical_fix' => ['fix_pincodes_table.php', 'fix_user_properties_schema.php', 'fix_testimonials_table.php', 'fix_schema.php', 'fix_mlm_extensions.php'],
    'seed_essential' => ['seed_voice_agents.php', 'seed_api_keys.php', 'seed_bank_data.php', 'seed_complete_location_data.php', 'seed_pincodes.php', 'seed_feature_tables.php', 'seed_feature_tables_2.php'],
    'runtime' => ['cron_daily_compliance.php'],
    'maintenance' => ['add_property_image_column.php', 'add_ticket_booking_column.php', 'add_colony_content_columns.php', 'add_user_tracking_columns.php', 'add_admin_menu_items.php', 'add_voice_ai_indexes.php', 'apply_performance_indexes.php'],
    'drop_reusable' => ['drop_broken_views.php'], // Only this one is reusable
];

$keepFlat = [];
foreach ($keepCategories as $cat => $files) $keepFlat = array_merge($keepFlat, $files);

foreach ($scripts as $s) {
    $name = basename($s);
    if (in_array($name, $keepFlat)) {
        $keep[] = $name;
    } else {
        $archive[] = $name;
    }
}

echo "KEEP: " . count($keep) . " scripts\n";
foreach ($keep as $n) echo "  $n\n";

echo "\nARCHIVE: " . count($archive) . " scripts\n";
foreach ($archive as $n) echo "  $n\n";

echo "\nTotal: " . count($scripts) . "\n";
