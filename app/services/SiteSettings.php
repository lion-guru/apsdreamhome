<?php
namespace App\Services;

use PDO;

class SiteSettings
{
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
            $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                brand_name VARCHAR(255) NULL,
                logo_url VARCHAR(512) NULL,
                favicon_url VARCHAR(512) NULL,
                nav_json TEXT NULL,
                social_json TEXT NULL,
                footer_html TEXT NULL,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            $stmt = $pdo->query("SELECT brand_name, logo_url, favicon_url, nav_json, social_json, footer_html FROM site_settings ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                self::$cache = array_merge($defaults, array_filter($row, fn($v) => $v !== null && $v !== ''));
            } else {
                self::$cache = $defaults;
            }
        } catch (\Throwable $e) {
            self::$cache = $defaults;
        }
        return self::$cache;
    }

    public static function update(array $data): bool
    {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("INSERT INTO site_settings (brand_name, logo_url, favicon_url, nav_json, social_json, footer_html) VALUES (:brand_name, :logo_url, :favicon_url, :nav_json, :social_json, :footer_html)");
            $ok = $stmt->execute([
                ':brand_name' => $data['brand_name'] ?? null,
                ':logo_url' => $data['logo_url'] ?? null,
                ':favicon_url' => $data['favicon_url'] ?? null,
                ':nav_json' => $data['nav_json'] ?? null,
                ':social_json' => $data['social_json'] ?? null,
                ':footer_html' => $data['footer_html'] ?? null,
            ]);
            self::$cache = null;
            return $ok;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

