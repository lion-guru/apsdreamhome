<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;

// Admin Layout Manager
class LayoutManager {
    use ServiceTenantTrait;

    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function getLayoutSettings() {
        $result = null;
        try {
            $result = $this->db->fetch("SELECT * FROM layout_settings WHERE id = 1");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        return $result ? json_decode($result['settings'], true) : $this->getDefaultSettings();
    }
    
    public function updateLayoutSettings($settings) {
        $json = json_encode($settings);
        $this->db->query("UPDATE layout_settings SET settings = ?, updated_at = NOW() WHERE id = 1", [$json]);
        return true;
    }
    
    // Get default settings
    private function getDefaultSettings() {
        return [
            'premium_layout' => true,
            'header_type' => 'dynamic',
            'footer_type' => 'dynamic',
            'navigation_items' => [
                ['label' => '🏠 Home', 'url' => '/', 'active' => true],
                ['label' => '🏢 Properties', 'url' => '/properties', 'active' => false],
                ['label' => '🔧 Tools Hub', 'url' => '/tools-hub', 'active' => false],
                ['label' => '📚 About', 'url' => '/about', 'active' => false],
                ['label' => '📞 Contact', 'url' => '/contact', 'active' => false],
                ['label' => '🔂 Admin', 'url' => '/admin/login', 'active' => false]
            ],
            'footer_content' => '<p>© 2026 APS Dream Home. All rights reserved.</p>',
            'custom_css' => '',
            'custom_js' => ''
        ];
    }
    
    // Generate navigation JSON
    public function generateNavigationJson($items) {
        return json_encode($items);
    }
    
    // Generate footer HTML
    public function generateFooterHtml($content) {
        return $content;
    }
}
