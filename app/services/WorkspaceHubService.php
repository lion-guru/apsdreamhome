<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Http\Middleware\RBACManager;
use App\Services\AdminMenuService;

/**
 * Workspace Hub Service
 * 
 * Maps roles to their specific workspace hubs with curated menu items.
 * Provides a focused, role-appropriate dashboard experience.
 */
class WorkspaceHubService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $menuService;

    // Hub definitions
    private const HUBS = [
        'sales_crm' => [
            'name' => 'Sales & CRM Hub',
            'icon' => 'fas fa-chart-line',
            'description' => 'Leads, Site Visits, Customer Bookings, Inquiries',
            'color' => '#0d6efd', // primary
            'roles' => [
                'telecaller', 'telecalling_executive', 'telecalling_lead',
                'sales_director', 'sales_manager', 'sales_team_lead',
                'employee_sales_executive', 'employee_sales_manager',
                'agent', 'senior_agent'
            ],
            'sections' => [
                'leads' => [
                    'name' => 'Lead Management',
                    'icon' => 'fas fa-users',
                    'menus' => [
                        '/admin/leads',
                        '/admin/lead-kanban',
                        '/admin/leads/scoring',
                        '/admin/leads/trash',
                        '/admin/leads/import',
                        '/admin/leads/export/csv',
                        '/admin/leads/commission-heatmap',
                        '/admin/leads/telecaller-performance',
                        '/admin/leads/property-comparison',
                    ]
                ],
                'site_visits' => [
                    'name' => 'Site Visits',
                    'icon' => 'fas fa-map-marker-alt',
                    'menus' => [
                        '/admin/site-visits',
                        '/admin/sim-calling',
                    ]
                ],
                'bookings' => [
                    'name' => 'Customer Bookings',
                    'icon' => 'fas fa-file-contract',
                    'menus' => [
                        '/admin/bookings',
                        '/admin/sales/approvals',
                        '/admin/agreements',
                        '/admin/registry',
                        '/admin/possession',
                    ]
                ],
                'inquiries' => [
                    'name' => 'Inquiries & Communication',
                    'icon' => 'fas fa-envelope',
                    'menus' => [
                        '/admin/inquiries',
                        '/admin/messages',
                        '/admin/live-chat',
                        '/admin/campaigns',
                        '/admin/crm/templates',
                        '/admin/crm/bulk-send',
                        '/admin/crm/email-tracking/stats',
                        '/admin/crm/sla',
                        '/admin/crm/meetings',
                    ]
                ],
            ]
        ],
        'projects_inventory' => [
            'name' => 'Projects & Inventory Hub',
            'icon' => 'fas fa-city',
            'description' => 'Colonies, Layouts, Plots, Registry & Construction Tracking',
            'color' => '#198754', // success
            'roles' => [
                'project_manager', 'construction_director',
                'employee_project_manager', 'employee_site_engineer',
                'property_manager', 'property_manager',
                'operations_manager', 'employee_ops_executive', 'employee_ops_manager',
            ],
            'sections' => [
                'colonies' => [
                    'name' => 'Colony Management',
                    'icon' => 'fas fa-map',
                    'menus' => [
                        '/admin/colony-pipeline',
                        '/admin/colony-feasibility',
                        '/admin/colonies',
                        '/admin/locations/colonies',
                        '/admin/legal-colony-pipeline',
                        '/admin/legal-colony-pipeline/health',
                        '/admin/legal-colony-pipeline/analytics-all',
                        '/admin/legal-colony-pipeline/milestones/2',
                    ]
                ],
                'plots' => [
                    'name' => 'Plots Inventory',
                    'icon' => 'fas fa-th',
                    'menus' => [
                        '/admin/plots',
                        '/admin/plots/categories',
                        '/admin/plots/development',
                        '/admin/plots/availability',
                        '/admin/plots/allocation',
                        '/admin/plot-costs',
                    ]
                ],
                'land' => [
                    'name' => 'Land & Acquisitions',
                    'icon' => 'fas fa-landmark',
                    'menus' => [
                        '/admin/land-inventory/acquisitions',
                        '/admin/land-inventory/leads',
                        '/admin/land-inventory/brokers',
                        '/admin/land/records',
                        '/admin/noc-registry',
                    ]
                ],
                'projects' => [
                    'name' => 'Project Progress',
                    'icon' => 'fas fa-project-diagram',
                    'menus' => [
                        '/admin/projects',
                        '/admin/projects/progress',
                        '/admin/backoffice',
                    ]
                ],
            ]
        ],
        'finance_accounts' => [
            'name' => 'Finance & Accounts Hub',
            'icon' => 'fas fa-rupee-sign',
            'description' => 'Bookings Ledger, EMI Collections, Cashbook, Penalties, Payment Gateway',
            'color' => '#ffc107', // warning
            'roles' => [
                'accountant', 'senior_accountant', 'chartered_accountant',
                'finance_manager', 'finance_director', 'cfo',
                'employee_finance_executive', 'employee_finance_manager',
                'backoffice_staff',
            ],
            'sections' => [
                'payments' => [
                    'name' => 'Payments & Ledger',
                    'icon' => 'fas fa-money-bill-wave',
                    'menus' => [
                        '/admin/payments',
                        '/admin/finance/cash-book',
                        '/admin/finance/reconciliation',
                        '/admin/finance/collections',
                        '/admin/banking',
                        '/admin/bank-import',
                    ]
                ],
                'emi' => [
                    'name' => 'EMI & Collections',
                    'icon' => 'fas fa-calendar-check',
                    'menus' => [
                        '/admin/finance/penalties',
                        '/admin/finance/emi-auto-pay',
                        '/admin/tools/emi-calculator',
                        '/admin/sales/rera',
                    ]
                ],
                'invoices' => [
                    'name' => 'Invoices & Billing',
                    'icon' => 'fas fa-file-invoice',
                    'menus' => [
                        '/admin/invoices',
                        '/admin/gst',
                        '/admin/efiling',
                        '/admin/efiling/tds',
                        '/admin/efiling/gst',
                        '/admin/efiling/calendar',
                        '/admin/tax-efiling',
                    ]
                ],
                'expenses' => [
                    'name' => 'Expenses & Vendors',
                    'icon' => 'fas fa-receipt',
                    'menus' => [
                        '/admin/expense',
                        '/admin/finance/vendors',
                        '/admin/company-loans',
                        '/admin/company-loans/offers',
                    ]
                ],
            ]
        ],
        'legal_compliance' => [
            'name' => 'Legal & Compliance Hub',
            'icon' => 'fas fa-gavel',
            'description' => 'Agreements, e-Sign, NOC, Land Records, Stamp Duties',
            'color' => '#dc3545', // danger
            'roles' => [
                'legal_advisor', 'hr_director', 'hr_manager',
                'employee_legal_executive', 'employee_legal_advisor',
                'compliance_officer', // if exists
            ],
            'sections' => [
                'agreements' => [
                    'name' => 'Agreements & e-Sign',
                    'icon' => 'fas fa-file-signature',
                    'menus' => [
                        '/admin/agreements',
                        '/admin/legal/dashboard',
                        '/admin/legal/ai-composer',
                        '/admin/legal/ai-prompts',
                        '/admin/tools/esign',
                        '/admin/legal/templates',
                        '/admin/legal/clauses',
                        '/admin/legal/categories',
                    ]
                ],
                'noc_registry' => [
                    'name' => 'NOC & Registry',
                    'icon' => 'fas fa-certificate',
                    'menus' => [
                        '/admin/noc-registry',
                        '/admin/registry',
                        '/admin/possession',
                        '/admin/land/records',
                    ]
                ],
                'compliance' => [
                    'name' => 'Compliance & Documents',
                    'icon' => 'fas fa-shield-alt',
                    'menus' => [
                        '/admin/compliance-scorecard',
                        '/admin/security-test',
                        '/admin/legal/deadlines',
                        '/admin/rera-compliance',
                        '/admin/tools/stamp-duty',
                        '/admin/settings/payment',
                    ]
                ],
            ]
        ],
        'associate_mlm' => [
            'name' => 'Associate & MLM Hub',
            'icon' => 'fas fa-sitemap',
            'description' => 'Associates, Downline Tree, Commission Approvals, Payouts',
            'color' => '#6f42c1', // purple
            'roles' => [
                'super_admin', 'admin', 'manager',
                'ceo', 'cmo', 'coo',
                'associate', 'senior_associate', 'associate_team_lead',
                'agent', 'senior_agent',
                'franchise_owner',
                'mlm_manager', // if exists
            ],
            'sections' => [
                'associates' => [
                    'name' => 'Associate Management',
                    'icon' => 'fas fa-users-cog',
                    'menus' => [
                        '/admin/mlm/associates',
                        '/admin/business/associates',
                        '/admin/associate-extensions',
                        '/admin/mlm/associate-ranks',
                    ]
                ],
                'network' => [
                    'name' => 'Network & Tree',
                    'icon' => 'fas fa-project-diagram',
                    'menus' => [
                        '/admin/mlm/genealogy',
                        '/admin/mlm-realestate/bookings',
                        '/admin/referrals',
                        '/admin/referrals/leaderboard',
                        '/admin/referrals/share-analytics',
                        '/admin/referrals/tiers',
                    ]
                ],
                'commissions' => [
                    'name' => 'Commissions & Payouts',
                    'icon' => 'fas fa-percentage',
                    'menus' => [
                        '/admin/commission',
                        '/admin/commission-plans',
                        '/admin/commission/recalculations',
                        '/admin/payouts',
                        '/admin/payout-batches',
                        '/admin/mlm/clawbacks',
                        '/admin/mlm/withdrawals',
                        '/admin/mlm/rewards',
                        '/admin/mlm-rewards/rank-criteria',
                        '/admin/mlm-rewards/upgrades',
                        '/admin/mlm-rewards/rewards',
                        '/admin/mlm-rewards/withdrawals',
                        '/admin/commission/telecaller/commissions',
                        '/admin/commission/calculations',
                    ]
                ],
                'mlm_settings' => [
                    'name' => 'MLM Settings',
                    'icon' => 'fas fa-cogs',
                    'menus' => [
                        '/admin/mlm-settings/levels',
                        '/admin/mlm-settings/rules',
                        '/admin/mlm-settings/evaluate',
                        '/admin/mlm-settings/rank-benefits',
                    ]
                ],
            ]
        ],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->menuService = new AdminMenuService();
    }

    /**
     * Get the appropriate hub for a user role
     */
    public function getHubForRole(string $role): ?array
    {
        foreach (self::HUBS as $hubKey => $hub) {
            if (in_array($role, $hub['roles'])) {
                return array_merge($hub, ['key' => $hubKey]);
            }
        }
        return null;
    }

    /**
     * Get all available hubs
     */
    public function getAllHubs(): array
    {
        $result = [];
        foreach (self::HUBS as $key => $hub) {
            $result[$key] = array_merge($hub, ['key' => $key]);
        }
        return $result;
    }

    /**
     * Get hub menu items filtered by user permissions
     */
    public function getHubMenuItems(string $hubKey, ?string $role = null, ?int $userId = null): array
    {
        $hub = self::HUBS[$hubKey] ?? null;
        if (!$hub) {
            return [];
        }

        $role = $role ?? RBACManager::getUserRole();
        $userId = $userId ?? ($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null);

        $sections = [];
        foreach ($hub['sections'] as $sectionKey => $section) {
            $allowedMenus = [];
            
            foreach ($section['menus'] as $menuUrl) {
                // Check if user has permission to view this menu
                if ($this->menuService->hasMenuAccessByUrl($menuUrl, $role, $userId)) {
                    $allowedMenus[] = $menuUrl;
                }
            }
            
            if (!empty($allowedMenus)) {
                $sections[$sectionKey] = array_merge($section, ['menus' => $allowedMenus]);
            }
        }

        return array_merge($hub, [
            'key' => $hubKey,
            'sections' => $sections
        ]);
    }

    /**
     * Get dashboard redirect URL for role
     */
    public function getDashboardForRole(string $role): string
    {
        $hub = $this->getHubForRole($role);
        if ($hub) {
            // Return the first menu URL from the hub as default dashboard
            foreach ($hub['sections'] as $section) {
                if (!empty($section['menus'])) {
                    return $section['menus'][0];
                }
            }
        }
        
        // Fallback dashboards by role category
        $category = RBACManager::getRoleCategory($role);
        return match ($category) {
            'Executive' => '/admin/dashboard/ceo',
            'Management', 'Departmental' => '/admin/erp',
            'Team Lead' => '/admin/backoffice',
            'Senior Staff', 'Staff' => '/employee/dashboard',
            'Telecalling' => '/employee/dashboard',
            'MLM', 'Agent' => '/associate/dashboard',
            'Franchise' => '/admin/erp',
            'Customer' => '/user/dashboard',
            default => '/admin/erp',
        };
    }

    /**
     * Render hub navigation for sidebar
     */
    public function renderHubNavigation(string $currentHub = ''): string
    {
        $role = RBACManager::getUserRole();
        $userHub = $this->getHubForRole($role);
        
        if (!$userHub) {
            return '';
        }
        
        $html = '<div class="hub-navigation mb-3">';
        $html .= '<div class="hub-header d-flex align-items-center justify-content-between mb-2">';
        $html .= '<span class="fw-bold text-' . $this->getColorClass($userHub['color']) . '">';
        $html .= '<i class="' . $userHub['icon'] . ' me-1"></i> ' . $userHub['name'];
        $html .= '</span>';
        $html .= '<small class="text-muted">' . $userHub['description'] . '</small>';
        $html .= '</div>';
        
        $html .= '<div class="hub-sections">';
        foreach ($userHub['sections'] as $sectionKey => $section) {
            if (empty($section['menus'])) continue;
            
            $html .= '<div class="hub-section mb-2">';
            $html .= '<button class="btn btn-sm btn-outline-' . $this->getColorClass($userHub['color']) . ' w-100 text-start d-flex align-items-center justify-content-between" 
                            type="button" data-bs-toggle="collapse" data-bs-target="#hubSection' . $sectionKey . '" aria-expanded="false">';
            $html .= '<span><i class="' . $section['icon'] . ' me-2"></i>' . $section['name'] . '</span>';
            $html .= '<i class="fas fa-chevron-down"></i>';
            $html .= '</button>';
            $html .= '<div class="collapse" id="hubSection' . $sectionKey . '">';
            $html .= '<div class="btn-group-vertical w-100 mt-2">';
            
            foreach ($section['menus'] as $menuUrl) {
                $menuItem = $this->getMenuItemByUrl($menuUrl);
                if ($menuItem) {
                    $active = ($currentHub === $menuUrl) ? 'active' : '';
                    $html .= '<a href="' . BASE_URL . $menuUrl . '" class="btn btn-sm btn-outline-' . $this->getColorClass($userHub['color']) . ' ' . $active . ' text-start">';
                    $html .= '<i class="' . ($menuItem['icon'] ?? 'fas fa-circle') . ' me-2"></i>' . htmlspecialchars($menuItem['name']);
                    $html .= '</a>';
                }
            }
            
            $html .= '</div></div></div>';
        }
        $html .= '</div></div>';
        
        return $html;
    }

    private function getMenuItemByUrl(string $url): ?array
    {
        try {
            return $this->db->fetchRow("SELECT * FROM admin_menu_items WHERE url = ? AND is_active = 1 LIMIT 1", [$url]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getColorClass(string $hexColor): string
    {
        $colorMap = [
            '#0d6efd' => 'primary',
            '#198754' => 'success',
            '#ffc107' => 'warning',
            '#dc3545' => 'danger',
            '#6f42c1' => 'purple',
        ];
        return $colorMap[$hexColor] ?? 'primary';
    }
}