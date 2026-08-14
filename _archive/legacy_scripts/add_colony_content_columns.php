<?php
/**
 * Migration: Add content columns to colonies table for dynamic landing pages
 * Run: php scripts/add_colony_content_columns.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

$db = \App\Core\Database\Database::getInstance();

echo "Adding content columns to colonies table...\n";

$columns = [
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS slug VARCHAR(200) AFTER name",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS banner_image VARCHAR(500) AFTER image_path",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS key_highlights LONGTEXT AFTER amenities",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS nearby_places LONGTEXT AFTER key_highlights",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS gallery_images LONGTEXT AFTER nearby_places",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS youtube_video_url VARCHAR(500) AFTER gallery_images",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS meta_title VARCHAR(200) AFTER youtube_video_url",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS meta_description TEXT AFTER meta_title",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS show_plots_publicly TINYINT(1) DEFAULT 0 AFTER is_active",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(20) AFTER show_plots_publicly",
    "ALTER TABLE colonies ADD COLUMN IF NOT EXISTS contact_email VARCHAR(100) AFTER contact_phone",
    "ALTER TABLE colonies ADD INDEX IF NOT EXISTS idx_slug (slug)",
];

foreach ($columns as $sql) {
    try {
        $db->query($sql);
        echo "  OK: " . substr($sql, 0, 80) . "...\n";
    } catch (\Exception $e) {
        // Column already exists or syntax differences - try without IF NOT EXISTS
        $sql = str_replace(" IF NOT EXISTS", "", $sql);
        $sql = str_replace("IF NOT EXISTS ", "", $sql);
        $sql = str_replace("ADD INDEX IF NOT EXISTS", "ADD INDEX", $sql);
        try {
            $db->query($sql);
            echo "  OK: " . substr($sql, 0, 80) . "...\n";
        } catch (\Exception $e2) {
            echo "  SKIP (likely exists): " . $e2->getMessage() . "\n";
        }
    }
}

// Generate slugs for existing colonies that don't have one
$colonies = $db->fetchAll("SELECT id, name FROM colonies WHERE slug IS NULL OR slug = ''");
foreach ($colonies as $c) {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($c['name'])), '-'));
    $slug = preg_replace('/-+/', '-', $slug);
    $db->query("UPDATE colonies SET slug = ? WHERE id = ?", [$slug, $c['id']]);
    echo "  Generated slug '$slug' for '{$c['name']}'\n";
}

echo "\nDone! Colonies table updated.\n";?>