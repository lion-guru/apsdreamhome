<?php
namespace App\Services;

use App\Traits\ServiceTenantTrait;
use PDO;

class SiteSettings
{
    use ServiceTenantTrait;

    private static $cache;

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $defaults = [
            'brand_name' => 'APS Dream Home',
            'logo_url' => '/assets/images/logo/apslogo.png',
            'favicon_url' => '/assets/images/icons/icon-192x192.png',
            'nav_json' => json_encode([
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Properties', 'url' => '/properties'],
                ['label' => 'About', 'url' => '/about'],
                ['label' => 'Contact', 'url' => '/contact']
            ]),
            'social_json' => json_encode([
                ['icon' => 'fab fa-facebook-f', 'url' => '#'],
                ['icon' => 'fab fa-instagram', 'url' => '#'],
                ['icon' => 'fab fa-linkedin-in', 'url' => '#']
            ]),
            'footer_html' => ''
        ];
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $settings = $defaults;
            foreach ($rows as $row) {
                $key = $row['setting_key'];
                $val = $row['setting_value'];
                if ($val !== null && $val !== '' && isset($settings[$key])) {
                    $settings[$key] = $val;
                }
            }
            self::$cache = $settings;
        } catch (\Throwable $e) {
            self::$cache = $defaults;
        }
        return self::$cache;
    }

    public static function update(array $data): bool
    {
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO site_settings (setting_key, setting_value, category, value, updated_at)
                    VALUES (?, ?, 'general', ?, NOW())
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), value = VALUES(value), updated_at = NOW()
                ");
                $stmt->execute([$key, $value, $value]);
            }
            self::$cache = null;
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

