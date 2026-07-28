<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class CRMSettingsController extends AdminController
{
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $this->requireAdmin();
        $db = \App\Core\Database\Database::getInstance()->getConnection();

        $settings = [];
        try {
            $rows = $db->query("SELECT setting_key, setting_value FROM crm_settings")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
            error_log('CRMSettings error: ' . $e->getMessage());
        }

        $roles = ['admin','super_admin','manager','associate','agent','employee','telecaller'];

        return $this->render('admin/crm/settings', [
            'settings' => $settings,
            'roles' => $roles,
        ]);
    }

    public function save()
    {
        $this->requireAdmin();
        $db = \App\Core\Database\Database::getInstance()->getConnection();

        // Ensure crm_settings table exists
        try {
            $db->query("CREATE TABLE IF NOT EXISTS crm_settings (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Throwable $e) {
            error_log('CRMSettings create table error: ' . $e->getMessage());
        }

        $settings = [
            'crm_enabled' => $_POST['crm_enabled'] ?? '1',
            'crm_lead_create_roles' => $_POST['crm_lead_create_roles'] ?? 'admin,manager,associate,agent',
            'crm_lead_delete_roles' => $_POST['crm_lead_delete_roles'] ?? 'admin,manager',
            'crm_auto_assign_enabled' => $_POST['crm_auto_assign_enabled'] ?? '1',
            'crm_auto_assign_method' => $_POST['crm_auto_assign_method'] ?? 'round_robin',
            'crm_scoring_enabled' => $_POST['crm_scoring_enabled'] ?? '1',
            'crm_scoring_hot_threshold' => $_POST['crm_scoring_hot_threshold'] ?? '70',
            'crm_scoring_warm_threshold' => $_POST['crm_scoring_warm_threshold'] ?? '40',
            'crm_drip_enabled' => $_POST['crm_drip_enabled'] ?? '1',
            'crm_sla_enabled' => $_POST['crm_sla_enabled'] ?? '1',
            'crm_sla_response_hours' => $_POST['crm_sla_response_hours'] ?? '24',
            'crm_trash_retention_days' => $_POST['crm_trash_retention_days'] ?? '30',
            'crm_export_enabled' => $_POST['crm_export_enabled'] ?? '1',
            'crm_import_enabled' => $_POST['crm_import_enabled'] ?? '1',
            'crm_kanban_enabled' => $_POST['crm_kanban_enabled'] ?? '1',
        ];

        try {
            foreach ($settings as $key => $value) {
                $db->query(
                    "INSERT INTO crm_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
                    [$key, $value, $value]
                );
            }
            $this->setFlash('success', 'CRM settings saved successfully');
        } catch (\Throwable $e) {
            error_log('CRMSettings save error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to save settings: ' . $e->getMessage());
        }

        return $this->redirect('/admin/crm-settings');
    }
}
