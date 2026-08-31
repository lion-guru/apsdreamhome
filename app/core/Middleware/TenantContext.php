<?php
namespace App\Core\Middleware;

use App\Services\TenantService;

/**
 * TenantContext — Resolves and sets tenant context for every request.
 *
 * Priority order:
 * 1. HTTP header: X-Tenant-ID or X-Tenant-Slug (API/mobile)
 * 2. Custom domain: crm.theirbrand.com (white-label)
 * 3. Subdomain: {tenant}.apsdreamhome.com
 * 4. Query param: ?tenant_id=N (admin switching)
 * 5. Session: $_SESSION['tenant_id'] (persistent)
 * 6. Default: APS Dream Home (id=1)
 *
 * Call TenantContext::resolve() from BaseController::__construct() or AdminController.
 */
class TenantContext
{
    private static ?array $tenant = null;
    private static ?int $tenantId = null;

    /**
     * Resolve tenant from request and set in session + TenantService.
     */
    public static function resolve(): void
    {
        if (self::$tenant !== null) return;

        $db = \App\Core\Database\Database::getInstance()->getConnection();

        // 1. HTTP Header (API/Mobile)
        $headerId = $_SERVER['HTTP_X_TENANT_ID'] ?? null;
        $headerSlug = $_SERVER['HTTP_X_TENANT_SLUG'] ?? null;

        if ($headerId && is_numeric($headerId)) {
            self::setById((int)$headerId, $db);
            return;
        }
        if ($headerSlug) {
            self::setBySlug($headerSlug, $db);
            return;
        }

        // 2. Custom domain (white-label)
        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Strip port if present
        $hostname = preg_replace('/:\d+$/', '', $host);
        try {
            $stmt = $db->prepare("SELECT id FROM tenants WHERE domain = ? AND status IN ('active', 'trial') LIMIT 1");
            $stmt->execute([$hostname]);
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                self::setById((int)$row['id'], $db);
                return;
            }
        } catch (\Throwable $e) {
            // domain column may not exist yet — fall through
        }

        // 3. Subdomain
        $hostParts = explode('.', $host);
        if (count($hostParts) > 2) {
            $subdomain = $hostParts[0];
            if (!in_array($subdomain, ['www', 'api', 'admin', 'mail'])) {
                self::setBySlug($subdomain, $db);
                return;
            }
        }

        // 4. Query param (admin switching) — gated: only admins may impersonate via URL
        if (!empty($_GET['tenant_id']) && is_numeric($_GET['tenant_id'])) {
            $isAdmin = !empty($_SESSION['admin_id']) || (($_SESSION['role'] ?? '') === 'admin') || (($_SESSION['admin_role'] ?? '') === 'admin') || !empty($_SESSION['is_superadmin']);
            if ($isAdmin) {
                self::setById((int)$_GET['tenant_id'], $db);
                error_log('TenantContext: admin tenant switch via ?tenant_id=' . (int)$_GET['tenant_id'] . ' by session ' . json_encode(['admin_id'=>$_SESSION['admin_id']??null,'role'=>$_SESSION['role']??null]));
                return;
            }
            // Non-admin ?tenant_id ignored — fall through to session/default
            error_log('TenantContext: blocked non-admin tenant_id spoof attempt: ' . (int)$_GET['tenant_id']);
        }

        // 5. Session
        if (!empty($_SESSION['tenant_id'])) {
            self::setById((int)$_SESSION['tenant_id'], $db);
            return;
        }

        // 6. Default: APS Dream Home
        self::setById(1, $db);
    }

    /**
     * Force-set tenant by ID (used for single-tenant mode).
     */
    public static function setById(int $id, ?\PDO $db = null): void
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        }

        try {
            $stmt = $db->prepare("SELECT * FROM tenants WHERE id = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$id]);
            self::$tenant = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('TenantContext::setById error: ' . $e->getMessage());
            self::$tenant = null;
        }

        self::$tenantId = self::$tenant ? (int)self::$tenant['id'] : 1;
        $_SESSION['tenant_id'] = self::$tenantId;

        // Set in TenantService
        if (self::$tenant) {
            TenantService::getInstance()->setCurrentTenant(self::$tenantId);
        }
    }

    /**
     * Force-set tenant by slug.
     */
    public static function setBySlug(string $slug, ?\PDO $db = null): void
    {
        if ($db === null) {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
        }

        try {
            $stmt = $db->prepare("SELECT * FROM tenants WHERE slug = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$slug]);
            self::$tenant = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('TenantContext::setBySlug error: ' . $e->getMessage());
            self::$tenant = null;
        }

        self::$tenantId = self::$tenant ? (int)self::$tenant['id'] : 1;
        $_SESSION['tenant_id'] = self::$tenantId;

        if (self::$tenant) {
            TenantService::getInstance()->setCurrentTenant(self::$tenantId);
        }
    }

    /**
     * Get current tenant ID.
     */
    public static function getId(): int
    {
        if (self::$tenantId === null) {
            self::resolve();
        }
        return self::$tenantId ?? 1;
    }

    /**
     * Get current tenant data.
     */
    public static function get(): ?array
    {
        if (self::$tenant === null) {
            self::resolve();
        }
        return self::$tenant;
    }

    /**
     * Get tenant setting value.
     */
    public static function getSetting(string $key, $default = null)
    {
        $tenant = self::get();
        if (!$tenant) return $default;

        $settings = json_decode($tenant['settings'] ?? '{}', true) ?: [];
        $config = json_decode($tenant['config'] ?? '{}', true) ?: [];

        return $settings[$key] ?? $config[$key] ?? $default;
    }

    /**
     * Get tenant branding colors.
     */
    public static function getColors(): array
    {
        $tenant = self::get();
        return [
            'primary'   => $tenant['primary_color'] ?? '#667eea',
            'secondary' => $tenant['secondary_color'] ?? '#764ba2',
        ];
    }

    /**
     * Get tenant logo URL.
     */
    public static function getLogo(): string
    {
        $tenant = self::get();
        return $tenant['logo_url'] ?? '';
    }

    /**
     * Get tenant name.
     */
    public static function getName(): string
    {
        $tenant = self::get();
        return $tenant['name'] ?? 'APS Dream Home';
    }

    /**
     * Check if multi-tenancy is enabled (more than 1 active tenant).
     */
    public static function isMultiTenant(): bool
    {
        static $check = null;
        if ($check === null) {
            try {
                $db = \App\Core\Database\Database::getInstance()->getConnection();
                $count = (int)$db->query("SELECT COUNT(*) FROM tenants WHERE status IN ('active','trial') AND deleted_at IS NULL")->fetchColumn();
                $check = $count > 1;
            } catch (\Throwable $e) {
                $check = false;
            }
        }
        return $check;
    }

    /**
     * Reset (for testing).
     */
    public static function reset(): void
    {
        self::$tenant = null;
        self::$tenantId = null;
    }
}
