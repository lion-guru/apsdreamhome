<?php
/**
 * Migration: Seed YouTube social links, featured brand video URLs, and company website settings
 * Session: 357
 * Idempotent: Safe to re-run, checks existing keys before inserting or updating
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

try {
    $db = \App\Core\Database\Database::getInstance()->getConnection();

    $settingsToSeed = [
        'social_youtube' => [
            'value' => 'https://youtube.com/@apsdreamhomes?si=kFGBniCRsdQXRyr9',
            'category' => 'social'
        ],
        'featured_video_url' => [
            'value' => 'https://www.youtube.com/watch?v=uB63fG1n5sY',
            'category' => 'media'
        ],
        'featured_video_url_2' => [
            'value' => 'https://www.youtube.com/watch?v=t71NMYPrnF0',
            'category' => 'media'
        ],
        'brand_video_local' => [
            'value' => '/assets/images/logo/apslogovideo.mp4',
            'category' => 'media'
        ],
        'company_website' => [
            'value' => 'https://www.apsdreamhomes.co.in',
            'category' => 'general'
        ],
    ];

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($settingsToSeed as $key => $info) {
        $stmt = $db->prepare("SELECT id, setting_value, value FROM site_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $insertStmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, value, category) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$key, $info['value'], $info['value'], $info['category']]);
            $inserted++;
            echo "INSERTED: {$key} => {$info['value']}\n";
        } else {
            if ($row['setting_value'] !== $info['value'] || $row['value'] !== $info['value']) {
                $upStmt = $db->prepare("UPDATE site_settings SET setting_value = ?, value = ?, updated_at = NOW() WHERE setting_key = ?");
                $upStmt->execute([$info['value'], $info['value'], $key]);
                $updated++;
                echo "UPDATED: {$key} => {$info['value']}\n";
            } else {
                $skipped++;
                echo "SKIP: {$key} already up-to-date\n";
            }
        }
    }

    echo "Migration Complete: {$inserted} inserted, {$updated} updated, {$skipped} skipped.\n";
} catch (\Throwable $e) {
    echo "ERROR in Migration: " . $e->getMessage() . "\n";
    exit(1);
}
