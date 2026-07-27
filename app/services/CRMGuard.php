<?php
/**
 * CRMGuard — Feature toggle enforcement for CRM settings
 * Reads crm_settings table, caches for request lifetime, provides gate checks.
 *
 * Usage:
 *   $guard = CRMGuard::getInstance();
 *   if (!$guard->canCreateLead('agent')) { ... }
 *   if (!$guard->isEnabled('auto_assign')) { ... }
 */
namespace App\Services;

class CRMGuard
{
    private static ?self $instance = null;
    private ?\PDO $pdo = null;
    private array $settings = [];
    private bool $loaded = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Load settings from DB (once per request).
     */
    private function load(): void
    {
        if ($this->loaded) return;
        $this->loaded = true;

        try {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $rows = $this->pdo->query("SELECT setting_key, setting_value FROM crm_settings")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $this->settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
            error_log('CRMGuard load error: ' . $e->getMessage());
            // Defaults — all enabled
            $this->settings = [
                'crm_enabled' => '1',
                'crm_lead_create_roles' => 'admin,manager,associate,agent,employee,telecaller',
                'crm_lead_delete_roles' => 'admin,manager,associate,agent,employee',
                'crm_auto_assign_enabled' => '1',
                'crm_auto_assign_method' => 'round_robin',
                'crm_scoring_enabled' => '1',
                'crm_scoring_hot_threshold' => '70',
                'crm_scoring_warm_threshold' => '40',
                'crm_drip_enabled' => '1',
                'crm_sla_enabled' => '1',
                'crm_sla_response_hours' => '24',
                'crm_trash_retention_days' => '30',
                'crm_export_enabled' => '1',
                'crm_import_enabled' => '1',
                'crm_kanban_enabled' => '1',
            ];
        }
    }

    /**
     * Check if a boolean feature is enabled.
     * @param string $feature Key WITHOUT 'crm_' prefix (e.g. 'auto_assign', 'scoring', 'drip')
     */
    public function isEnabled(string $feature): bool
    {
        $this->load();
        $key = 'crm_' . $feature;
        return ($this->settings[$key] ?? '1') === '1';
    }

    /**
     * Check if CRM is globally enabled.
     */
    public function isCrmEnabled(): bool
    {
        $this->load();
        return ($this->settings['crm_enabled'] ?? '1') === '1';
    }

    /**
     * Check if a specific role can create leads.
     */
    public function canCreateLead(string $role): bool
    {
        $this->load();
        if (!$this->isCrmEnabled()) return false;
        $allowed = array_map('trim', explode(',', $this->settings['crm_lead_create_roles'] ?? ''));
        return in_array(strtolower($role), $allowed);
    }

    /**
     * Check if a specific role can delete leads.
     */
    public function canDeleteLead(string $role): bool
    {
        $this->load();
        if (!$this->isCrmEnabled()) return false;
        $allowed = array_map('trim', explode(',', $this->settings['crm_lead_delete_roles'] ?? ''));
        return in_array(strtolower($role), $allowed);
    }

    /**
     * Check if lead scoring is enabled.
     */
    public function isScoringEnabled(): bool
    {
        return $this->isEnabled('scoring');
    }

    /**
     * Check if auto-assignment is enabled.
     */
    public function isAutoAssignEnabled(): bool
    {
        return $this->isEnabled('auto_assign');
    }

    /**
     * Get auto-assign method.
     */
    public function getAutoAssignMethod(): string
    {
        $this->load();
        return $this->settings['crm_auto_assign_method'] ?? 'round_robin';
    }

    /**
     * Check if drip campaigns are enabled.
     */
    public function isDripEnabled(): bool
    {
        return $this->isEnabled('drip');
    }

    /**
     * Check if SLA tracking is enabled.
     */
    public function isSlaEnabled(): bool
    {
        return $this->isEnabled('sla');
    }

    /**
     * Check if export is enabled.
     */
    public function isExportEnabled(): bool
    {
        return $this->isEnabled('export');
    }

    /**
     * Check if import is enabled.
     */
    public function isImportEnabled(): bool
    {
        return $this->isEnabled('import');
    }

    /**
     * Check if kanban view is enabled.
     */
    public function isKanbanEnabled(): bool
    {
        return $this->isEnabled('kanban');
    }

    /**
     * Get scoring thresholds.
     */
    public function getHotThreshold(): int
    {
        $this->load();
        return (int)($this->settings['crm_scoring_hot_threshold'] ?? 70);
    }

    public function getWarmThreshold(): int
    {
        $this->load();
        return (int)($this->settings['crm_scoring_warm_threshold'] ?? 40);
    }

    /**
     * Get SLA response hours.
     */
    public function getSlaResponseHours(): int
    {
        $this->load();
        return (int)($this->settings['crm_sla_response_hours'] ?? 24);
    }

    /**
     * Get trash retention days.
     */
    public function getTrashRetentionDays(): int
    {
        $this->load();
        return (int)($this->settings['crm_trash_retention_days'] ?? 30);
    }

    /**
     * Get all settings (for admin display).
     */
    public function getAll(): array
    {
        $this->load();
        return $this->settings;
    }

    /**
     * Get a specific setting value.
     */
    public function get(string $key, $default = null)
    {
        $this->load();
        return $this->settings[$key] ?? $default;
    }
}
