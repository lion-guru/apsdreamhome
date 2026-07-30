<?php
/**
 * Portal Menu Service
 * Single source of truth for role-based sidebar menus across all portal layouts
 * (customer, associate, agent, employee, admin)
 *
 * Menu structure:
 *  - "common"   = items that appear for ALL logged-in users (profile, address, bank, KYC, docs, insurance, logout)
 *  - role key   = items specific to that role
 *
 * Each item is [
 *   'key'         => unique id (used for badge data attribute)
 *   'section'     => section name (groups items in sidebar)
 *   'label'       => display text (i18n key)
 *   'url'         => path
 *   'icon'        => font-awesome class
 *   'badge'       => optional badge count (e.g. properties_count)
 *   'badge_color' => badge color (default primary)
 *   'badge_query' => SQL table to count from, e.g. ['user_properties' => 'user_id = ?']
 * ]
 *
 * RBAC: All menu visibility is driven by $userRole from session.
 */
namespace App\Services;

class PortalMenuService
{
    private ?\PDO $pdo = null;
    private ?int $userId = null;
    private string $role = 'guest';

    public function __construct(?\PDO $pdo = null, ?int $userId = null, ?string $role = null)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->role = $role ?? ($_SESSION['role'] ?? 'guest');
    }

    public function setContext(?\PDO $pdo, ?int $userId, ?string $role): void
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->role = $role ?? 'guest';
    }

    /**
     * Get the full menu tree for the current user role.
     * Returns: array<array{section:string, items:array}>
     */
    public function getMenu(): array
    {
        $role = $this->normalizeRole($this->role);
        $specific = $this->roleSpecificItems($role);
        $common = $this->commonItems();

        // Role-specific items FIRST, then common items (Account/Settings at bottom)
        $all = array_merge($specific, $common);
        $sections = [];
        foreach ($all as $item) {
            $section = $item['section'];
            if (!isset($sections[$section])) {
                $sections[$section] = ['name' => $section, 'items' => []];
            }
            $sections[$section]['items'][] = $item;
        }
        return array_values($sections);
    }

    /**
     * Items every authenticated user sees.
     * Split into Account + Settings sections for better UX.
     */
    private function commonItems(): array
    {
        return [
            // Account
            $this->item('profile', 'Account', 'My Profile', $this->profileUrl(), 'fas fa-user'),
            $this->item('notifications', 'Account', 'Notifications', $this->notificationsUrl(), 'fas fa-bell', $this->countTable('notifications', 'user_id')),
            $this->item('messages', 'Account', 'Messages', $this->messagesUrl(), 'fas fa-envelope'),
            $this->item('address', 'Account', 'My Address', $this->addressUrl(), 'fas fa-map-marker-alt'),
            $this->item('bank', 'Account', 'Bank Details', $this->bankUrl(), 'fas fa-university'),
            $this->item('kyc', 'Account', 'KYC', $this->kycUrl(), 'fas fa-id-card'),
            $this->item('documents', 'Account', 'Documents', $this->documentsUrl(), 'fas fa-folder-open'),

            // Settings
            $this->item('settings', 'Settings', 'Settings', $this->settingsUrl(), 'fas fa-cog'),
            $this->item('logout', 'Settings', 'Logout', $this->logoutUrl(), 'fas fa-sign-out-alt', null, 'danger'),
        ];
    }

    /**
     * Role-specific items.
     */
    private function roleSpecificItems(string $role): array
    {
        switch ($role) {
            case 'customer':
            case 'user':
                return $this->customerItems();
            case 'associate':
                return $this->associateItems();
            case 'agent':
                return $this->agentItems();
            case 'employee':
                return $this->employeeItems();
            case 'admin':
            case 'super_admin':
            case 'manager':
                return $this->adminItems();
            default:
                return [];
        }
    }

    private function customerItems(): array
    {
        return [
            $this->item('dashboard', 'Main', 'Dashboard', '/user/dashboard', 'fas fa-tachometer-alt'),
            $this->item('properties', 'Main', 'My Properties', '/user/properties', 'fas fa-building', $this->countTable('user_properties', 'user_id')),
            $this->item('inquiries', 'Main', 'My Inquiries', '/user/inquiries', 'fas fa-envelope-open-text', $this->countTable('inquiries', 'user_id')),
            $this->item('bookings', 'Main', 'My Bookings', '/user/bookings', 'fas fa-file-contract', $this->countTable('bookings', 'user_id')),
            $this->item('favorites', 'Main', 'Favorites', '/user/favorites', 'fas fa-heart', $this->countTable('favorites', 'user_id')),
            $this->item('saved-searches', 'Main', 'Saved Searches', '/user/saved-searches', 'fas fa-bookmark', $this->countTable('saved_searches', 'user_id')),

            // Finance
            $this->item('emi-tracker', 'Finance', 'EMI Tracker', '/user/emi-tracker', 'fas fa-calendar-check'),
            $this->item('payment-history', 'Finance', 'Payment History', '/user/payment-history', 'fas fa-receipt'),
            $this->item('site-visits', 'Finance', 'Site Visits', '/user/site-visits', 'fas fa-map-marker-alt'),

            // Support
            $this->item('tickets', 'Support', 'Support Tickets', '/user/tickets', 'fas fa-life-ring', $this->countTable('support_tickets', 'user_id')),
            $this->item('referral', 'Support', 'Refer & Earn', '/user/referral', 'fas fa-user-friends'),
            $this->item('insurance', 'Support', 'Insurance', '/user/insurance', 'fas fa-shield-alt'),
            $this->item('investment', 'Support', 'Investments', '/user/investment-plans', 'fas fa-chart-line'),

            // Explore
            $this->item('browse', 'Explore', 'Browse Properties', '/properties', 'fas fa-search'),
            $this->item('list-property', 'Explore', 'Post Property', '/list-property', 'fas fa-plus-circle'),
            $this->item('compare', 'Explore', 'Compare Properties', '/compare', 'fas fa-balance-scale'),
            $this->item('auctions', 'Explore', 'Auctions', '/auctions', 'fas fa-gavel'),
        ];
    }

    private function associateItems(): array
    {
        return [
            // Main Operations
            $this->item('dashboard', 'Main', 'Dashboard', '/associate/dashboard', 'fas fa-tachometer-alt'),
            $this->item('crm', 'Main', 'CRM Dashboard', '/associate/crm', 'fas fa-funnel-dollar'),
            $this->item('leads', 'Main', 'My Leads', '/associate/leads', 'fas fa-user-friends', $this->countTable('leads', 'assigned_to')),
            $this->item('leads-import', 'Main', 'Import Leads', '/associate/leads/import', 'fas fa-file-import'),
            $this->item('leads-bulk-wa', 'Main', 'Bulk WhatsApp', '/associate/leads/bulk-whatsapp', 'fab fa-whatsapp'),
            $this->item('leads-export', 'Main', 'Export Leads', '/associate/leads/export', 'fas fa-file-export'),
            $this->item('followups', 'Main', 'Follow-ups', '/associate/followups', 'fas fa-calendar-check', $this->countTable('crm_tasks', 'assigned_to', 'status', 'pending')),
            $this->item('properties', 'Main', 'My Properties', '/associate/properties', 'fas fa-building', $this->countTable('user_properties', 'posted_by')),

            // Earnings & Finance
            $this->item('commissions', 'Earnings', 'Commissions', '/associate/commissions', 'fas fa-rupee-sign'),
            $this->item('wallet', 'Earnings', 'Wallet', '/associate/wallet', 'fas fa-wallet'),
            $this->item('withdraw', 'Earnings', 'Withdraw', '/associate/wallet/withdraw', 'fas fa-money-bill-wave'),

            // Network & Team
            $this->item('network', 'Network', 'Network Tree', '/associate/network/tree', 'fas fa-project-diagram'),
            $this->item('genealogy', 'Network', 'Interactive Genealogy', '/associate/genealogy', 'fas fa-sitemap'),
            $this->item('team', 'Network', 'Team Members', '/associate/team', 'fas fa-users'),
            $this->item('rank-eligibility', 'Network', 'Rank & Eligibility', '/associate/rank-eligibility', 'fas fa-trophy'),
            $this->item('mlm-plan', 'Network', 'Commission Plan', '/associate/mlm-plan', 'fas fa-file-invoice-dollar'),
            $this->item('referral', 'Network', 'Refer & Earn', '/associate/referral', 'fas fa-user-friends'),

            // Field Operations
            $this->item('collections', 'Field Ops', 'Cash Collections', '/associate/collections', 'fas fa-hand-holding-usd'),
            $this->item('site-visits', 'Field Ops', 'Site Visits', '/associate/site-visits', 'fas fa-map-marker-alt', $this->countSiteVisits()),
            $this->item('schedule', 'Field Ops', 'My Schedule', '/associate/schedule', 'fas fa-calendar-alt'),

            // Property Tools
            $this->item('browse', 'Properties', 'Browse Properties', '/associate/browse', 'fas fa-search'),
            $this->item('list-property', 'Properties', 'Add Property', '/associate/list-property', 'fas fa-plus-circle'),
            $this->item('book-plot', 'Properties', 'Book Plot', '/associate/book-plot', 'fas fa-file-signature'),
            $this->item('compare', 'Properties', 'Compare Properties', '/associate/compare', 'fas fa-balance-scale'),

            // My Business
            $this->item('my-bookings', 'Business', 'My Bookings', '/associate/my-bookings', 'fas fa-file-contract'),
            $this->item('my-customers', 'Business', 'My Customers', '/associate/my-customers', 'fas fa-address-book'),
            $this->item('emi-tracker', 'Business', 'EMI Tracker', '/associate/emi-tracker', 'fas fa-calendar-check'),
            $this->item('payment-history', 'Business', 'Payment History', '/associate/payment-history', 'fas fa-receipt'),

            // Tools & Reports
            $this->item('emi-calculator', 'Tools', 'EMI Calculator', '/tools-hub', 'fas fa-calculator'),
            $this->item('export-earnings', 'Reports', 'Export Earnings', '/associate/export/my-earnings', 'fas fa-file-download'),
            $this->item('export-team', 'Reports', 'Export Team', '/associate/export/active-team', 'fas fa-file-export'),
        ];
    }

    private function agentItems(): array
    {
        return [
            $this->item('dashboard', 'Main', 'Dashboard', '/agent/dashboard', 'fas fa-tachometer-alt'),
            $this->item('leads', 'CRM', 'My Leads', '/agent/leads', 'fas fa-users', $this->countTable('leads', 'assigned_to')),
            $this->item('leads-add', 'CRM', 'Add Lead', '/agent/leads/add', 'fas fa-plus-circle'),
            $this->item('deals', 'CRM', 'My Deals', '/agent/deals', 'fas fa-handshake'),
            $this->item('properties', 'Main', 'My Properties', '/agent/properties', 'fas fa-building', $this->countTable('user_properties', 'posted_by')),
            $this->item('commissions', 'Earnings', 'Commissions', '/agent/commissions', 'fas fa-rupee-sign'),
            $this->item('wallet', 'Earnings', 'Wallet', '/agent/wallet', 'fas fa-wallet'),
            $this->item('browse', 'Explore', 'Browse Properties', '/properties', 'fas fa-search'),
        ];
    }

    private function employeeItems(): array
    {
        // DB-driven: read from admin_menu_items filtered by employee sub-role RBAC
        if ($this->pdo && $this->userId) {
            $subRole = $this->resolveEmployeeSubRole();
            if ($subRole) {
                try {
                    $stmt = $this->pdo->prepare("
                        SELECT mi.name, mi.url, mi.icon, mi.order_index
                        FROM admin_menu_items mi
                        INNER JOIN admin_role_menu_permissions rp ON rp.menu_item_id = mi.id
                        WHERE mi.section = 'employee'
                          AND mi.is_active = 1
                          AND rp.role = ?
                          AND rp.can_view = 1
                        ORDER BY mi.order_index ASC
                    ");
                    $stmt->execute([$subRole]);
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    
                    if (!empty($rows)) {
                        $items = [];
                        foreach ($rows as $row) {
                            $section = 'Work';
                            $key = strtolower(str_replace(' ', '_', $row['name']));
                            
                            // Dynamic badges for specific items
                            $badge = null;
                            if ($row['name'] === 'My Leads') {
                                $badge = $this->countTable('leads', 'assigned_to');
                            } elseif ($row['name'] === 'My Tasks') {
                                $badge = $this->countTable('agent_tasks', 'assigned_to');
                            } elseif ($row['name'] === 'Leaves') {
                                $badge = $this->countTable('employee_leave_requests', 'employee_id', 'status', 'pending');
                            }
                            
                            $items[] = $this->item($key, $section, $row['name'], $row['url'], $row['icon'], $badge);
                        }
                        return $items;
                    }
                } catch (\Throwable $e) {
                // Fall through to hardcoded fallback
                error_log($e->getMessage());
                }
            }
        }
        
        // Hardcoded fallback (when DB is unavailable or sub-role not found)
        return [
            $this->item('dashboard', 'Main', 'Dashboard', '/employee/dashboard', 'fas fa-tachometer-alt'),
            $this->item('leads', 'CRM', 'My Leads', '/employee/leads', 'fas fa-user-tie', $this->countTable('leads', 'assigned_to')),
            $this->item('leads-add', 'CRM', 'Add Lead', '/employee/leads/add', 'fas fa-plus-circle'),
            $this->item('tasks', 'Work', 'My Tasks', '/employee/tasks', 'fas fa-tasks', $this->countTable('agent_tasks', 'assigned_to')),
            $this->item('attendance', 'Work', 'Attendance', '/employee/attendance', 'fas fa-calendar-check'),
            $this->item('leaves', 'Work', 'Leaves', '/employee/leaves', 'fas fa-umbrella-beach'),
            $this->item('payroll', 'Earnings', 'Payroll', '/employee/payroll', 'fas fa-money-check-alt'),
            $this->item('performance', 'Earnings', 'Performance', '/employee/performance', 'fas fa-chart-line'),
            $this->item('profile', 'Work', 'My Profile', '/employee/profile', 'fas fa-user'),
        ];
    }

    /**
     * Resolve employee sub-role from designation + department.
     * Queries employees table → employee_designation_roles → returns sub_role string.
     */
    private function resolveEmployeeSubRole(): ?string
    {
        if (!$this->pdo || !$this->userId) return null;
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT edr.sub_role
                FROM employees e
                INNER JOIN employee_designation_roles edr 
                    ON edr.designation = e.designation AND (edr.department = e.department OR edr.department IS NULL)
                WHERE e.user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$this->userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? $row['sub_role'] : 'employee';
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function adminItems(): array
    {
        return [
            $this->item('dashboard', 'Main', 'Admin Dashboard', '/admin/dashboard', 'fas fa-tachometer-alt'),
            $this->item('leads', 'Main', 'All Leads', '/admin/leads', 'fas fa-users', $this->countTable('leads')),
            $this->item('properties', 'Main', 'All Properties', '/admin/properties', 'fas fa-building', $this->countTable('user_properties')),
            $this->item('bookings', 'Main', 'All Bookings', '/admin/bookings', 'fas fa-file-contract', $this->countTable('bookings')),
            $this->item('users', 'Main', 'All Users', '/admin/users', 'fas fa-user-friends', $this->countTable('users')),
            $this->item('admin-tools', 'Tools', 'Admin Tools', '/admin/dev-tools', 'fas fa-tools'),
            $this->item('admin-kyc', 'Tools', 'KYC Reviews', '/admin/kyc', 'fas fa-id-card', $this->countTable('kyc_requests', null, 'status', 'pending')),
        ];
    }

    /**
     * Helper: build a menu item.
     */
    private function item(string $key, string $section, string $label, string $url, string $icon, ?int $badge = null, string $color = 'primary'): array
    {
        return [
            'key'         => $key,
            'section'     => $section,
            'label'       => $label,
            'url'         => $url,
            'icon'        => $icon,
            'badge'       => $badge,
            'badge_color' => $color,
        ];
    }

    /**
     * Helper: count rows in a table for the current user.
     */
    private function countTable(string $table, ?string $userCol = 'user_id', ?string $extraCol = null, ?string $extraVal = null): ?int
    {
        if (!$this->pdo || !$this->userId) {
            return null;
        }
        try {
            $sql = "SELECT COUNT(*) as cnt FROM `$table`";
            $params = [];
            $where = [];
            if ($userCol && $this->userId) {
                $where[] = "`$userCol` = ?";
                $params[] = $this->userId;
            }
            if ($extraCol && $extraVal) {
                $where[] = "`$extraCol` = ?";
                $params[] = $extraVal;
            }
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['cnt'];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function countSiteVisits(): ?int
    {
        if (!$this->pdo || !$this->userId) {
            return null;
        }
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM site_visits WHERE (assigned_to = ? OR user_id = ?) AND status NOT IN ('cancelled','completed') AND visit_date >= CURDATE()");
            $stmt->execute([$this->userId, $this->userId]);
            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['cnt'];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * URL helpers (vary by role).
     */
    private function profileUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'associate' => '/associate/profile',
            'agent' => '/agent/profile',
            'employee' => '/employee/profile',
            'admin', 'super_admin', 'manager' => '/admin/profile',
            default => '/user/profile',
        };
    }
    private function addressUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'employee' => '/employee/profile',
            'associate' => '/associate/profile',
            default => '/user/address',
        };
    }
    private function bankUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'associate' => '/associate/bank-details',
            'employee' => '/employee/profile',
            default => '/user/bank-details',
        };
    }
    private function kycUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'admin', 'super_admin' => '/admin/kyc',
            'employee' => '/employee/profile',
            default => '/user/kyc',
        };
    }
    private function notificationsUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'employee' => '/employee/notifications',
            'associate' => '/associate/settings',
            'agent' => '/agent/profile',
            default => '/user/notifications',
        };
    }
    private function messagesUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'employee' => '/employee/notifications',
            'associate' => '/associate/settings',
            'agent' => '/agent/profile',
            default => '/user/messages',
        };
    }
    private function documentsUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'admin', 'super_admin' => '/admin/documents',
            'employee' => '/employee/documents',
            'associate' => '/associate/documents',
            default => '/user/kyc',
        };
    }
    private function settingsUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'associate' => '/associate/settings',
            'agent' => '/agent/profile',
            'employee' => '/employee/profile',
            default => '/user/notification-settings',
        };
    }
    private function logoutUrl(): string
    {
        return match ($this->normalizeRole($this->role)) {
            'associate' => '/associate/logout',
            'agent' => '/agent/logout',
            'employee' => '/employee/logout',
            'admin', 'super_admin' => '/admin/logout',
            default => '/user/logout',
        };
    }

    /**
     * Normalize role: "user" -> "customer", "super_admin" -> "admin", etc.
     */
    private function normalizeRole(string $role): string
    {
        $role = strtolower(trim($role));
        if ($role === 'user') return 'customer';
        if (in_array($role, ['admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro', 'director', 'builder', 'investor'], true)) {
            return 'admin';
        }
        return $role;
    }

    /**
     * Static helper for layout files.
     * Usage: $menu = PortalMenuService::forSession();
     */
    public static function forSession(): array
    {
        $pdo = null;
        $userId = null;
        $role = $_SESSION['role'] ?? 'guest';
        $root = dirname(__DIR__, 2);

        if (isset($_SESSION['user_id'])) $userId = (int)$_SESSION['user_id'];
        elseif (isset($_SESSION['admin_id'])) $userId = (int)$_SESSION['admin_id'];
        elseif (isset($_SESSION['associate_id'])) $userId = (int)$_SESSION['associate_id'];
        elseif (isset($_SESSION['agent_id'])) $userId = (int)$_SESSION['agent_id'];
        elseif (isset($_SESSION['employee_id'])) $userId = (int)$_SESSION['employee_id'];

        // Get PDO via the project's global config
        try {
            if (!$pdo) {
                $configFile = $root . '/config/database.php';
                if (file_exists($configFile)) {
                    $config = require $configFile;
                    if (isset($config['host']) && isset($config['port'])) {
                        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
                        $pdo = new \PDO($dsn, $config['username'], $config['password'], [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            $pdo = null;
        }

        $svc = new self($pdo, $userId, $role);
        return $svc->getMenu();
    }

    /**
     * Get the active role label for display.
     */
    public static function roleLabel(?string $role = null): string
    {
        $role = $role ?? ($_SESSION['role'] ?? 'guest');
        $role = strtolower(trim($role));
        return match ($role) {
            'customer', 'user' => 'Customer Portal',
            'associate' => 'Associate Portal',
            'agent' => 'Agent Portal',
            'employee' => 'Employee Portal',
            'admin', 'super_admin', 'manager', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro', 'director' => 'Admin Portal',
            default => 'Portal',
        };
    }
}
