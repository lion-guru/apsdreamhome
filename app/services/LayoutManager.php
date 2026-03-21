<?php
// Admin Layout Manager
class LayoutManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Get current layout settings
    public function getLayoutSettings() {
        $query = "SELECT * FROM layout_settings WHERE id = 1";
        $result = $this->db->query($query);
        return $result ? json_decode($result['settings'], true) : $this->getDefaultSettings();
    }
    
    // Update layout settings
    public function updateLayoutSettings($settings) {
        $json = json_encode($settings);
        $query = "UPDATE layout_settings SET settings = ?, updated_at = NOW() WHERE id = 1";
        $this->db->query($query, [$json]);
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

// Usage example in controllers:
$layoutManager = new LayoutManager($database);
$settings = $layoutManager->getLayoutSettings();

// Set global variables for layout
$premium_layout = $settings['premium_layout'];
$site['nav_json'] = $layoutManager->generateNavigationJson($settings['navigation_items']);
$site['footer_html'] = $layoutManager->generateFooterHtml($settings['footer_content']);
?>
