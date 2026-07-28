<?php
namespace App\Services;

/**
 * TenantEnforcement — Enforces subscription plan limits and tenant status.
 *
 * Checks:
 * 1. Is tenant suspended? → block all CRM operations
 * 2. Is trial expired? → restrict to read-only
 * 3. Is tenant over plan limits? → block creation operations
 *
 * Usage in controllers:
 *   $enforcement = TenantEnforcement::getInstance();
 *   $result = $enforcement->canPerform($tenantId, 'create_lead');
 *   if (!$result['allowed']) { /* show error * / }
 */
class TenantEnforcement
{
    private static ?self $instance = null;
    private ?\PDO $pdo = null;
    private ?TenantService $tenantService = null;

    private function __construct()
    {
        $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        $this->tenantService = TenantService::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Main enforcement check — can this tenant perform this action?
     *
     * Actions: create_lead, create_property, add_user, send_email, send_sms, use_api
     *
     * @return array{allowed: bool, reason: string, code: string}
     */
    public function canPerform(int $tenantId, string $action): array
    {
        $tenant = $this->tenantService->getById($tenantId);
        if (!$tenant) {
            return ['allowed' => false, 'reason' => 'Tenant not found.', 'code' => 'NOT_FOUND'];
        }

        // 1. Suspended tenants can't do anything
        if (($tenant['status'] ?? '') === 'suspended') {
            return [
                'allowed' => false,
                'reason'  => 'Your account has been suspended. Please contact support.',
                'code'    => 'SUSPENDED',
            ];
        }

        // 2. Cancelled tenants can't do anything
        if (($tenant['status'] ?? '') === 'cancelled') {
            return [
                'allowed' => false,
                'reason'  => 'Your account has been cancelled. Please contact support to reactivate.',
                'code'    => 'CANCELLED',
            ];
        }

        // 3. Expired trial — restrict to read-only
        if (($tenant['status'] ?? '') === 'trial' && !empty($tenant['trial_ends_at'])) {
            $trialEnd = strtotime($tenant['trial_ends_at']);
            if ($trialEnd < time()) {
                // Allow read-only actions
                $readOnly = ['view_leads', 'view_properties', 'view_dashboard', 'view_reports'];
                if (in_array($action, $readOnly)) {
                    return ['allowed' => true, 'reason' => '', 'code' => 'OK'];
                }
                return [
                    'allowed' => false,
                    'reason'  => 'Your trial has expired. Please upgrade to continue.',
                    'code'    => 'TRIAL_EXPIRED',
                ];
            }
        }

        // 4. Plan limit enforcement
        switch ($action) {
            case 'create_lead':
                if (!$this->tenantService->canCreateLead($tenantId)) {
                    return [
                        'allowed' => false,
                        'reason'  => "Lead limit reached ({$tenant['max_leads']} max). Please upgrade your plan.",
                        'code'    => 'LIMIT_LEADS',
                    ];
                }
                break;

            case 'create_property':
                if (!$this->tenantService->canAddProperty($tenantId)) {
                    return [
                        'allowed' => false,
                        'reason'  => "Property limit reached ({$tenant['max_properties']} max). Please upgrade your plan.",
                        'code'    => 'LIMIT_PROPERTIES',
                    ];
                }
                break;

            case 'add_user':
                if (!$this->tenantService->canAddUser($tenantId)) {
                    return [
                        'allowed' => false,
                        'reason'  => "User limit reached ({$tenant['max_users']} max). Please upgrade your plan.",
                        'code'    => 'LIMIT_USERS',
                    ];
                }
                break;
        }

        return ['allowed' => true, 'reason' => '', 'code' => 'OK'];
    }

    /**
     * Get enforcement status for UI display.
     *
     * @return array{
     *     status: string,
     *     trial_days_left: int|null,
     *     usage: array,
     *     warnings: array,
     *     blocks: array
     * }
     */
    public function getStatus(int $tenantId): array
    {
        $tenant = $this->tenantService->getById($tenantId);
        if (!$tenant) {
            return ['status' => 'not_found', 'trial_days_left' => null, 'usage' => [], 'warnings' => [], 'blocks' => ['Tenant not found.']];
        }

        $result = [
            'status'          => $tenant['status'] ?? 'unknown',
            'trial_days_left' => null,
            'usage'           => [],
            'warnings'        => [],
            'blocks'          => [],
        ];

        // Trial countdown
        if (($tenant['status'] ?? '') === 'trial' && !empty($tenant['trial_ends_at'])) {
            $daysLeft = (int)ceil((strtotime($tenant['trial_ends_at']) - time()) / 86400);
            $result['trial_days_left'] = max(0, $daysLeft);
            if ($daysLeft <= 3) {
                $result['warnings'][] = "Trial expires in {$daysLeft} day(s). Upgrade to keep full access.";
            }
            if ($daysLeft <= 0) {
                $result['blocks'][] = 'Trial expired. Upgrade to continue.';
            }
        }

        // Usage stats
        $usage = $tenant['usage'] ?? [];
        $result['usage'] = [
            'leads'      => ['used' => $tenant['leads_count'] ?? 0, 'max' => $tenant['max_leads'] ?? 0],
            'properties' => ['used' => $tenant['properties_count'] ?? 0, 'max' => $tenant['max_properties'] ?? 0],
            'users'      => ['used' => $tenant['users_count'] ?? 0, 'max' => $tenant['max_users'] ?? 0],
        ];

        // Usage warnings (at 80%)
        foreach (['leads', 'properties', 'users'] as $metric) {
            $used = $result['usage'][$metric]['used'];
            $max  = $result['usage'][$metric]['max'];
            if ($max > 0 && $used >= $max * 0.8 && $used < $max) {
                $pct = round(($used / $max) * 100);
                $result['warnings'][] = ucfirst($metric) . " usage at {$pct}% ({$used}/{$max}).";
            }
            if ($max > 0 && $used >= $max) {
                $result['blocks'][] = ucfirst($metric) . " limit reached ({$used}/{$max}). Upgrade to continue.";
            }
        }

        return $result;
    }

    /**
     * Quick check: is this tenant in good standing?
     */
    public function isActive(int $tenantId): bool
    {
        $result = $this->canPerform($tenantId, 'view_dashboard');
        return $result['allowed'];
    }
}
