<?php
namespace App\Services;

use App\Core\Database\Database;

/**
 * TenantService — Multi-tenant SaaS management
 * CRUD for tenants, plan enforcement, usage tracking, tenant context.
 */
class TenantService
{
    private static ?self $instance = null;
    private ?\PDO $pdo = null;
    private ?array $currentTenant = null;

    private function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── Tenant Context ──────────────────────────────────────

    /**
     * Set current tenant (called by TenantMiddleware or boot).
     */
    public function setCurrentTenant(int $tenantId): void
    {
        $this->currentTenant = $this->getById($tenantId);
    }

    /**
     * Get current tenant.
     */
    public function getCurrentTenant(): ?array
    {
        return $this->currentTenant;
    }

    /**
     * Get current tenant ID.
     */
    public function getCurrentTenantId(): int
    {
        return (int)($this->currentTenant['id'] ?? 1);
    }

    // ── CRUD ────────────────────────────────────────────────

    /**
     * List tenants with pagination and search.
     */
    public function list(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(t.name LIKE ? OR t.slug LIKE ? OR t.contact_email LIKE ?)";
            $s = "%{$filters['search']}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $where[] = "t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['plan_id'])) {
            $where[] = "t.plan_id = ?";
            $params[] = (int)$filters['plan_id'];
        }

        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int)($filters['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $whereStr = implode(' AND ', $where);

        // Count
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM tenants t WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch with plan info
        $sql = "SELECT t.*, sp.name AS plan_name, sp.price_monthly
                FROM tenants t
                LEFT JOIN subscription_plans sp ON sp.id = t.plan_id
                WHERE {$whereStr}
                ORDER BY t.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $tenants = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Attach usage for each tenant
        foreach ($tenants as &$tenant) {
            $tenant['usage'] = $this->getCurrentUsage((int)$tenant['id']);
            $tenant['users_count'] = $this->countUsers((int)$tenant['id']);
        }

        return [
            'tenants'   => $tenants,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'pages'     => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Get tenant by ID with plan info.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, sp.name AS plan_name, sp.price_monthly, sp.price_yearly,
                   sp.max_users AS plan_max_users, sp.max_leads AS plan_max_leads,
                   sp.max_properties AS plan_max_properties
            FROM tenants t
            LEFT JOIN subscription_plans sp ON sp.id = t.plan_id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $tenant = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        if ($tenant) {
            $tenant['usage'] = $this->getCurrentUsage($id);
            $tenant['users_count'] = $this->countUsers($id);
            $tenant['leads_count'] = $this->countLeads($id);
            $tenant['properties_count'] = $this->countProperties($id);
        }

        return $tenant;
    }

    /**
     * Get tenant by slug.
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tenants WHERE slug = ? AND deleted_at IS NULL");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create new tenant.
     */
    public function create(array $data): int
    {
        $slug = $data['slug'] ?? $this->generateSlug($data['name']);

        $stmt = $this->pdo->prepare("
            INSERT INTO tenants (name, slug, domain, contact_name, contact_email, contact_phone,
                                 address, city, state, plan_id, status, max_users, max_leads,
                                 max_properties, storage_limit_mb, primary_color, secondary_color,
                                 features_enabled, trial_ends_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $planId = (int)($data['plan_id'] ?? 1);
        $plan = $this->getPlan($planId);
        $status = $data['status'] ?? 'trial';
        $trialEnds = null;
        if ($status === 'trial') {
            $trialEnds = date('Y-m-d H:i:s', strtotime('+14 days'));
        }

        $stmt->execute([
            $data['name'],
            $slug,
            $data['domain'] ?? null,
            $data['contact_name'] ?? null,
            $data['contact_email'] ?? null,
            $data['contact_phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $planId,
            $status,
            $plan['max_users'] ?? 1,
            $plan['max_leads'] ?? 50,
            $plan['max_properties'] ?? 10,
            $plan['storage_limit_mb'] ?? 100,
            $data['primary_color'] ?? '#667eea',
            $data['secondary_color'] ?? '#764ba2',
            $data['features_enabled'] ?? null,
            $trialEnds,
        ]);

        $tenantId = (int)$this->pdo->lastInsertId();

        // Log activity
        $this->logActivity($tenantId, 'tenant_created', "Tenant '{$data['name']}' created");

        return $tenantId;
    }

    /**
     * Update tenant.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $updatable = ['name', 'slug', 'domain', 'logo_url', 'primary_color', 'secondary_color',
                       'contact_name', 'contact_email', 'contact_phone', 'address', 'city', 'state',
                       'plan_id', 'status', 'max_users', 'max_leads', 'max_properties',
                       'storage_limit_mb', 'features_enabled', 'config', 'settings'];

        foreach ($updatable as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $params[] = $id;
        $sql = "UPDATE tenants SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $this->logActivity($id, 'tenant_updated', 'Tenant settings updated');

        return true;
    }

    /**
     * Soft-delete tenant.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tenants SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $this->logActivity($id, 'tenant_deleted', 'Tenant soft-deleted');
        return $stmt->rowCount() > 0;
    }

    /**
     * Restore soft-deleted tenant.
     */
    public function restore(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tenants SET deleted_at = NULL WHERE id = ?");
        $stmt->execute([$id]);
        $this->logActivity($id, 'tenant_restored', 'Tenant restored');
        return $stmt->rowCount() > 0;
    }

    /**
     * Suspend tenant.
     */
    public function suspend(int $id, string $reason = ''): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tenants SET status = 'suspended' WHERE id = ?");
        $stmt->execute([$id]);
        $this->logActivity($id, 'tenant_suspended', "Suspended: {$reason}");
        return $stmt->rowCount() > 0;
    }

    // ── Plans ───────────────────────────────────────────────

    /**
     * Get all active plans.
     */
    public function getPlans(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get plan by ID.
     */
    public function getPlan(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM subscription_plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    // ── Plan Enforcement ────────────────────────────────────

    /**
     * Check if tenant can add more users.
     */
    public function canAddUser(int $tenantId): bool
    {
        $tenant = $this->getById($tenantId);
        if (!$tenant) return false;
        return $tenant['users_count'] < $tenant['max_users'];
    }

    /**
     * Check if tenant can create more leads.
     */
    public function canCreateLead(int $tenantId): bool
    {
        $tenant = $this->getById($tenantId);
        if (!$tenant) return false;
        return $tenant['leads_count'] < $tenant['max_leads'];
    }

    /**
     * Check if tenant can add more properties.
     */
    public function canAddProperty(int $tenantId): bool
    {
        $tenant = $this->getById($tenantId);
        if (!$tenant) return false;
        return $tenant['properties_count'] < $tenant['max_properties'];
    }

    /**
     * Check if tenant has a specific feature enabled.
     */
    public function hasFeature(int $tenantId, string $feature): bool
    {
        $tenant = $this->getById($tenantId);
        if (!$tenant) return false;

        // Enterprise has everything
        if (($tenant['plan_id'] ?? 0) >= 4) return true;

        $features = json_decode($tenant['features_enabled'] ?? '{}', true) ?: [];
        return !empty($features[$feature]);
    }

    /**
     * Get usage percentage for a metric.
     */
    public function getUsagePercent(int $tenantId, string $metric): float
    {
        $tenant = $this->getById($tenantId);
        if (!$tenant) return 0;

        $usage = $tenant['usage'] ?? [];
        $used = $usage[$metric . '_count'] ?? 0;

        $limitMap = [
            'users'      => 'max_users',
            'leads'      => 'max_leads',
            'properties' => 'max_properties',
        ];

        $limitKey = $limitMap[$metric] ?? null;
        if (!$limitKey) return 0;

        $limit = (int)($tenant[$limitKey] ?? 1);
        return $limit > 0 ? round(($used / $limit) * 100, 1) : 0;
    }

    // ── Usage Tracking ──────────────────────────────────────

    /**
     * Get current billing period usage.
     */
    public function getCurrentUsage(int $tenantId): array
    {
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');

        $stmt = $this->pdo->prepare("
            SELECT * FROM tenant_usage
            WHERE tenant_id = ? AND period_start = ?
            LIMIT 1
        ");
        $stmt->execute([$tenantId, $periodStart]);
        $usage = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$usage) {
            // Create usage record for this period
            $stmt2 = $this->pdo->prepare("
                INSERT IGNORE INTO tenant_usage (tenant_id, period_start, period_end, users_count, leads_created, properties_count)
                VALUES (?, ?, ?, 0, 0, 0)
            ");
            $stmt2->execute([$tenantId, $periodStart, $periodEnd]);
            return ['users_count' => 0, 'leads_created' => 0, 'properties_count' => 0, 'api_calls' => 0, 'emails_sent' => 0, 'sms_sent' => 0];
        }

        return $usage;
    }

    /**
     * Increment usage counter.
     */
    public function incrementUsage(int $tenantId, string $metric, int $amount = 1): void
    {
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');
        $column = $metric . '_count'; // leads_created, users_count, etc.

        // Ensure column name is safe
        $validColumns = ['users_count', 'leads_created', 'properties_count', 'api_calls', 'storage_used_mb', 'emails_sent', 'sms_sent'];
        if (!in_array($column, $validColumns)) return;

        $sql = "INSERT INTO tenant_usage (tenant_id, period_start, period_end, {$column})
                VALUES (?, ?, ?, {$amount})
                ON DUPLICATE KEY UPDATE {$column} = {$column} + {$amount}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tenantId, $periodStart, $periodEnd]);
    }

    // ── Dashboard Stats ─────────────────────────────────────

    /**
     * Get dashboard stats for Super Admin.
     */
    public function getDashboardStats(): array
    {
        $stats = [];

        $stats['total_tenants'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenants WHERE deleted_at IS NULL")->fetchColumn();
        $stats['active_tenants'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenants WHERE status = 'active' AND deleted_at IS NULL")->fetchColumn();
        $stats['trial_tenants'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenants WHERE status = 'trial' AND deleted_at IS NULL")->fetchColumn();
        $stats['suspended_tenants'] = (int)$this->pdo->query("SELECT COUNT(*) FROM tenants WHERE status = 'suspended' AND deleted_at IS NULL")->fetchColumn();

        // Revenue (monthly)
        $stats['monthly_revenue'] = (float)($this->pdo->query("
            SELECT COALESCE(SUM(sp.price_monthly), 0)
            FROM tenants t
            JOIN subscription_plans sp ON sp.id = t.plan_id
            WHERE t.status IN ('active', 'trial') AND t.deleted_at IS NULL
        ")->fetchColumn());

        // Plan distribution
        $stats['by_plan'] = $this->pdo->query("
            SELECT sp.name, COUNT(t.id) AS count
            FROM subscription_plans sp
            LEFT JOIN tenants t ON t.plan_id = sp.id AND t.deleted_at IS NULL
            WHERE sp.is_active = 1
            GROUP BY sp.id, sp.name
            ORDER BY sp.sort_order
        ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Recent tenants
        $stats['recent_tenants'] = $this->pdo->query("
            SELECT t.name, t.slug, t.status, t.created_at, sp.name AS plan_name
            FROM tenants t
            LEFT JOIN subscription_plans sp ON sp.id = t.plan_id
            WHERE t.deleted_at IS NULL
            ORDER BY t.created_at DESC
            LIMIT 5
        ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $stats;
    }

    // ── Private Helpers ─────────────────────────────────────

    private function countUsers(int $tenantId): int
    {
        // For now count from users table (will add tenant_id column later)
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE status != 'deleted'")->fetchColumn();
    }

    private function countLeads(int $tenantId): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL")->fetchColumn();
    }

    private function countProperties(int $tenantId): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM user_properties WHERE status != 'deleted'")->fetchColumn();
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $base = $slug;
        $i = 1;
        while ($this->getBySlug($slug)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function logActivity(int $tenantId, string $action, string $details): void
    {
        try {
            $adminId = $_SESSION['admin_id'] ?? null;
            $stmt = $this->pdo->prepare("
                INSERT INTO user_activity_logs_unified (user_id, action, context, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $adminId,
                $action,
                json_encode(['tenant_id' => $tenantId, 'details' => $details]),
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            ]);
        } catch (\Throwable $e) {
            error_log('TenantService::logActivity error: ' . $e->getMessage());
        }
    }
}
