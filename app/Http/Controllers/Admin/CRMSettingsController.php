<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;

class CRMSettingsController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $this->requireAdmin();
        $tid = $this->tenantId();

        // Get all CRM settings matching the view
        $settingsKeys = [
            'crm_enabled',
            'crm_lead_create_roles',
            'crm_lead_delete_roles',
            'crm_auto_assign_enabled',
            'crm_require_attendance',
            'crm_lead_assignment_strategy',
            'crm_daily_lead_cap',
            'crm_scoring_enabled',
            'crm_scoring_hot_threshold',
            'crm_scoring_warm_threshold',
            'crm_whatsapp_integration',
            'crm_email_drip',
            'crm_call_recording',
            'crm_sla_response_hours',
            'crm_trash_retention_days',
            'crm_export_enabled',
            'crm_import_enabled'
        ];
        
        $placeholders = str_repeat('?,', count($settingsKeys) - 1) . '?';
        
        $query = "SELECT `key`, value FROM settings WHERE `key` IN ($placeholders)";
        $params = $settingsKeys;
        
        if ($tid > 1) {
            $query .= " AND tenant_id = ?";
            $params[] = $tid;
        }

        $rows = $this->db->fetchAll($query, $params);
        
        // Default values for standard settings
        $settings = [
            'crm_enabled' => '1',
            'crm_lead_create_roles' => 'admin,manager,associate,agent',
            'crm_lead_delete_roles' => 'admin,manager',
            'crm_auto_assign_enabled' => '0',
            'crm_lead_assignment_strategy' => 'round_robin',
            'crm_require_attendance' => '0',
            'crm_daily_lead_cap' => '50',
            'crm_scoring_enabled' => '0',
            'crm_scoring_hot_threshold' => '70',
            'crm_scoring_warm_threshold' => '40',
            'crm_whatsapp_integration' => '0',
            'crm_email_drip' => '0',
            'crm_call_recording' => '0',
            'crm_sla_response_hours' => '24',
            'crm_trash_retention_days' => '30',
            'crm_export_enabled' => '1',
            'crm_import_enabled' => '1'
        ];

        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        $this->render('admin/crm/settings', [
            'page_title' => 'CRM Auto-Assignment Settings',
            'settings' => $settings,
            'success' => $_SESSION['crm_settings_success'] ?? null,
            'error' => $_SESSION['crm_settings_error'] ?? null,
        ]);
        
        unset($_SESSION['crm_settings_success'], $_SESSION['crm_settings_error']);
    }

    public function update()
    {
        $this->requireAdmin();
        $tid = $this->tenantId();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['crm_settings_error'] = 'Invalid CSRF token';
            $this->redirect('/admin/settings/crm');
            return;
        }

        $updates = [];
        
        // Process arrays (roles)
        $updates['crm_lead_create_roles'] = isset($_POST['crm_lead_create_roles']) && is_array($_POST['crm_lead_create_roles']) 
            ? implode(',', $_POST['crm_lead_create_roles']) 
            : 'admin';
            
        $updates['crm_lead_delete_roles'] = isset($_POST['crm_lead_delete_roles']) && is_array($_POST['crm_lead_delete_roles']) 
            ? implode(',', $_POST['crm_lead_delete_roles']) 
            : 'admin';
            
        // Process standard form values mapping
        $settingsKeys = [
            'crm_enabled', 'crm_auto_assign_enabled', 'crm_require_attendance', 
            'crm_lead_assignment_strategy', 'crm_daily_lead_cap', 
            'crm_scoring_enabled', 'crm_scoring_hot_threshold', 'crm_scoring_warm_threshold',
            'crm_whatsapp_integration', 'crm_email_drip', 'crm_call_recording',
            'crm_sla_response_hours', 'crm_trash_retention_days',
            'crm_export_enabled', 'crm_import_enabled'
        ];
        
        foreach ($settingsKeys as $key) {
            if (isset($_POST[$key])) {
                $updates[$key] = $_POST[$key];
            }
        }

        try {
            foreach ($updates as $key => $val) {
                // Upsert
                $q = "SELECT id FROM settings WHERE `key` = ?";
                $p = [$key];
                if ($tid > 1) {
                    $q .= " AND tenant_id = ?";
                    $p[] = $tid;
                }
                
                $exists = $this->db->fetchOne($q, $p);
                if ($exists) {
                    // Try to see if updated_at exists by running query
                    $u = "UPDATE settings SET value = ? WHERE id = ?";
                    $this->db->query($u, [$val, $exists['id']]);
                } else {
                    $this->db->query("INSERT INTO settings (`key`, value) VALUES (?, ?)", [$key, $val]);
                }
            }
            $_SESSION['crm_settings_success'] = 'CRM settings updated successfully.';
        } catch (\Exception $e) {
            error_log("CRMSettings update error: " . $e->getMessage());
            $_SESSION['crm_settings_error'] = 'Failed to update CRM settings.';
        }

        $this->redirect('/admin/settings/crm');
    }
}
