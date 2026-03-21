<?php

namespace App\Http\Controllers;

use App\Core\Database;

class LayoutController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        
        // Check if admin is logged in
        session_start();
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
    }
    
    // Layout Manager Page
    public function layoutManager() {
        require_once __DIR__ . '/../../Services/LayoutManager.php';
        require_once __DIR__ . '/../../views/admin/layout_manager.php';
    }
    
    // Update Layout Settings
    public function updateLayoutSettings() {
        require_once __DIR__ . '/../../Services/LayoutManager.php';
        
        $layoutManager = new LayoutManager($this->db);
        
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
        header('Location: /admin/layout-manager');
        exit;
    }
}
