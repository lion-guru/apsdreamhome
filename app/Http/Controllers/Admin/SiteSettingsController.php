<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Site Settings Controller - Custom MVC Implementation
 * Handles site settings management operations in Admin panel
 */
class SiteSettingsController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['update', 'store', 'destroy']]);
    }

    /**
     * Display site settings
     */
    public function index()
    {
        try {
            // Get all site settings
            $sql = "SELECT * FROM site_settings ORDER BY setting_key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $settings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Organize settings by category
            $organizedSettings = [
                'general' => [],
                'appearance' => [],
                'email' => [],
                'social' => [],
                'seo' => []
            ];

            foreach ($settings as $setting) {
                $category = $setting['category'] ?? 'general';
                $organizedSettings[$category][$setting['setting_key']] = $setting;
            }

            $data = [
                'page_title' => 'Site Settings - APS Dream Home',
                'active_page' => 'site_settings',
                'settings' => $organizedSettings
            ];

            return $this->render('admin/site_settings/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Site Settings Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load site settings');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Show form for editing site settings
     */
    public function edit()
    {
        try {
            $data = [
                'page_title' => 'Edit Site Settings - APS Dream Home',
                'active_page' => 'site_settings',
                'settings_categories' => [
                    'general' => 'General Settings',
                    'appearance' => 'Appearance Settings',
                    'email' => 'Email Configuration',
                    'social' => 'Social Media Links',
                    'seo' => 'SEO Settings'
                ]
            ];

            return $this->render('admin/site_settings/edit', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Site Settings Edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load settings form');
            return $this->redirect('admin/site_settings');
        }
    }

    /**
     * Update site settings
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            if (empty($data)) {
                return $this->jsonError('No data provided', 400);
            }

            $this->db->beginTransaction();

            try {
                // Process each setting
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        // Handle JSON values (like social links, navigation)
                        $value = json_encode($value);
                    }

                    // Update or insert setting
                    $sql = "INSERT INTO site_settings (setting_key, setting_value, category, updated_at) 
                             VALUES (?, ?, ?, NOW())
                             ON DUPLICATE KEY UPDATE 
                             setting_value = VALUES(setting_value), 
                             category = VALUES(category), 
                             updated_at = NOW()";

                    $stmt = $this->db->prepare($sql);

                    // Determine category based on key
                    $category = $this->getSettingCategory($key);

                    $stmt->execute([
                        $key,
                        CoreFunctionsServiceCustom::validateInput($value, 'string'),
                        $category
                    ]);
                }

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'site_settings_updated', [
                    'updated_settings' => array_keys($data)
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site settings updated successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Site Settings Update error: " . $e->getMessage());
            return $this->jsonError('Failed to update site settings', 500);
        }
    }

    /**
     * Get specific setting category
     */
    public function getCategory($category)
    {
        try {
            $validCategories = ['general', 'appearance', 'email', 'social', 'seo'];
            if (!in_array($category, $validCategories)) {
                return $this->jsonError('Invalid category', 400);
            }

            $sql = "SELECT * FROM site_settings WHERE category = ? ORDER BY setting_key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$category]);
            $settings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse([
                'success' => true,
                'data' => $settings
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Setting Category error: " . $e->getMessage());
            return $this->jsonError('Failed to load settings', 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function reset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $this->db->beginTransaction();

            try {
                // Default settings
                $defaultSettings = [
                    'site_name' => 'APS Dream Home',
                    'site_description' => 'Professional Real Estate Platform',
                    'site_keywords' => 'real estate, property, dream home',
                    'site_author' => 'APS Dream Home Team',
                    'contact_email' => 'info@apsdreamhome.com',
                    'contact_phone' => '+91-XXXXXXXXXX',
                    'contact_address' => 'Mumbai, India',
                    'social_facebook' => '',
                    'social_twitter' => '',
                    'social_linkedin' => '',
                    'social_instagram' => '',
                    'logo_url' => '/assets/images/logo.png',
                    'favicon_url' => '/assets/images/favicon.ico',
                    'primary_color' => '#007bff',
                    'secondary_color' => '#6c757d',
                    'enable_maintenance' => '0',
                    'maintenance_message' => 'Site is under maintenance'
                ];

                foreach ($defaultSettings as $key => $value) {
                    $category = $this->getSettingCategory($key);

                    $sql = "INSERT INTO site_settings (setting_key, setting_value, category, updated_at) 
                             VALUES (?, ?, ?, NOW())
                             ON DUPLICATE KEY UPDATE 
                             setting_value = VALUES(setting_value), 
                             category = VALUES(category), 
                             updated_at = NOW()";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        $key,
                        CoreFunctionsServiceCustom::validateInput($value, 'string'),
                        $category
                    ]);
                }

                $this->db->commit();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'site_settings_reset', []);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site settings reset to defaults successfully'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->loggingService->error("Site Settings Reset error: " . $e->getMessage());
            return $this->jsonError('Failed to reset site settings', 500);
        }
    }

    /**
     * Get site settings statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total settings count
            $sql = "SELECT COUNT(*) as total FROM site_settings";
            $result = $this->db->fetchOne($sql);
            $stats['total_settings'] = (int)($result['total'] ?? 0);

            // Settings by category
            $sql = "SELECT category, COUNT(*) as count FROM site_settings GROUP BY category";
            $result = $this->db->fetchAll($sql);
            $stats['by_category'] = $result ?: [];

            // Recently updated
            $sql = "SELECT setting_key, updated_at FROM site_settings ORDER BY updated_at DESC LIMIT 10";
            $result = $this->db->fetchAll($sql);
            $stats['recent_updates'] = $result ?: [];

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Site Settings Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * Export settings
     */
    public function export()
    {
        try {
            $format = $_GET['format'] ?? 'json';

            $sql = "SELECT * FROM site_settings ORDER BY category, setting_key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $settings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($format === 'csv') {
                // Generate CSV export
                $filename = 'site_settings_' . date('Y-m-d_H-i-s') . '.csv';

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $output = fopen('php://output', 'w');

                // CSV header
                fputcsv($output, ['Setting Key', 'Setting Value', 'Category', 'Updated At']);

                // CSV data
                foreach ($settings as $setting) {
                    fputcsv($output, [
                        $setting['setting_key'],
                        $setting['setting_value'],
                        $setting['category'],
                        $setting['updated_at']
                    ]);
                }

                fclose($output);
                exit;
            } else {
                // JSON export
                return $this->jsonResponse([
                    'success' => true,
                    'data' => $settings,
                    'exported_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (Exception $e) {
            $this->loggingService->error("Export Settings error: " . $e->getMessage());
            return $this->jsonError('Failed to export settings', 500);
        }
    }

    /**
     * Get setting category based on key
     */
    private function getSettingCategory(string $key): string
    {
        $categories = [
            'site_name' => 'general',
            'site_description' => 'general',
            'site_keywords' => 'seo',
            'site_author' => 'general',
            'contact_email' => 'email',
            'contact_phone' => 'general',
            'contact_address' => 'general',
            'social_facebook' => 'social',
            'social_twitter' => 'social',
            'social_linkedin' => 'social',
            'social_instagram' => 'social',
            'logo_url' => 'appearance',
            'favicon_url' => 'appearance',
            'primary_color' => 'appearance',
            'secondary_color' => 'appearance',
            'enable_maintenance' => 'general',
            'maintenance_message' => 'general'
        ];

        return $categories[$key] ?? 'general';
    }

    /**
     * JSON response helper
     */
    public function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    protected function jsonError($message, $status = 400)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
