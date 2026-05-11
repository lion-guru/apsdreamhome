<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Http\Controllers\BaseController;

class LayoutController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function layoutManager() {
        require_once __DIR__ . '/../../../Services/LayoutManager.php';
        $layoutManager = new \App\Services\LayoutManager($this->db);
        $settings = $layoutManager->getLayoutSettings();
        $site = [];
        $site['nav_json'] = $layoutManager->generateNavigationJson($settings['navigation_items']);
        $site['footer_html'] = $layoutManager->generateFooterHtml($settings['footer_content']);
        $premium_layout = $settings['premium_layout'];
        require_once __DIR__ . '/../../../views/admin/layout_manager.php';
    }
    
    public function updateLayoutSettings() {
        require_once __DIR__ . '/../../../Services/LayoutManager.php';
        
        $layoutManager = new \App\Services\LayoutManager($this->db);
        
        $settings = [
            'premium_layout' => isset($_POST['premium_layout']),
            'header_type' => $_POST['header_type'] ?? 'dynamic',
            'footer_type' => $_POST['footer_type'] ?? 'dynamic',
            'navigation_items' => json_decode($_POST['navigation_items'] ?? '[]', true),
            'footer_content' => $_POST['footer_content'] ?? '',
            'custom_css' => $_POST['custom_css'] ?? '',
            'custom_js' => $_POST['custom_js'] ?? ''
        ];
        
        $layoutManager->updateLayoutSettings($settings);
        
        $_SESSION['success'] = 'Layout settings updated successfully!';
        header('Location: ' . BASE_URL . '/admin/layout-manager');
        exit;
    }
}
