<?php
/**
 * NavigationHelper
 *
 * Centralized navigation logic for the public-facing header.
 * Replaces navigation arrays previously embedded inside header.php
 * (lines ~142-355 of the monolithic view).  Exposes clean, testable
 * methods that the modular view components consume.
 *
 * Usage in views:
 *   <?php $nav = \App\Helpers\NavigationHelper::getInstance(); ?>
 *   <?php foreach ($nav->getDesktopNavItems() as $item): ?> …
 */

namespace App\Helpers;

require_once __DIR__ . '/TranslationHelper.php';

class NavigationHelper
{
    private static $instance = null;

    /** @var array|null Lazily populated site-settings cache */
    private $siteSettings = null;

    /** @var array|null Lazily populated projects/locations data */
    private $headerData = null;

    /** @var string Current request path (without BASE_URL prefix) */
    private $currentPath = '/';

    /** @var bool Whether the page is home */
    private $isHome = false;

    /** @var array Auth state */
    private $auth = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->detectCurrentPath();
        $this->detectAuthState();
    }

    /* ================================================================
     * SITE SETTINGS
     * ================================================================ */

    /**
     * Get a site-content settings value (cached in-process).
     */
    private function sc(string $key, string $default = ''): string
    {
        if ($this->siteSettings === null) {
            $this->siteSettings = [];
            try {
                $pdo = \App\Core\Database\Database::getInstance()->getPdo();
                $rows = $pdo->query(
                    "SELECT content_key, content_value FROM site_content WHERE section = 'settings' AND is_active = 1"
                )->fetchAll(\PDO::FETCH_KEY_PAIR);
                $this->siteSettings = $rows ?: [];
            } catch (\Exception $e) {
            }
        }
        return $this->siteSettings[$key] ?? $default;
    }

    /**
     * Public proxy used by views that still reference the
     * `$sc('key', 'default')` closure pattern.
     */
    public function getSetting(string $key, string $default = ''): string
    {
        return $this->sc($key, $default);
    }

    public function contactPhone(): string
    {
        return $this->sc('contact_phone', '+91 92771 21112');
    }

    public function contactWhatsApp(): string
    {
        return preg_replace('/[^0-9]/', '', $this->sc('contact_whatsapp', '91927712111'));
    }

    public function companyName(): string
    {
        return $this->sc('company_name', 'APS Dream Home');
    }

    /* ================================================================
     * CURRENT PATH / STATE
     * ================================================================ */

    private function detectCurrentPath(): void
    {
        $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base = (string) parse_url(BASE_URL, PHP_URL_PATH);
        $path = str_replace($base, '', $uri);
        $this->currentPath = ($path === '' || $path === false) ? '/' : $path;
        $this->isHome = ($this->currentPath === '/');
    }

    public function currentPath(): string
    {
        return $this->currentPath;
    }

    public function isHome(): bool
    {
        return $this->isHome;
    }

    public function isActive(string $url): bool
    {
        return ($this->currentPath === $url);
    }

    /**
     * Header CSS class: "hero-header" on home, otherwise plain.
     */
    public function headerClass(): string
    {
        return $this->isHome
            ? 'premium-header hero-header fixed-top'
            : 'premium-header fixed-top';
    }

    /* ================================================================
     * AUTH STATE
     * ================================================================ */

    private function detectAuthState(): void
    {
        $isCustomer = isset($_SESSION['user_id']) && $_SESSION['user_id'];
        $isAssociate = isset($_SESSION['associate_id']) && $_SESSION['associate_id'];
        $isAgent = isset($_SESSION['agent_id']) && $_SESSION['agent_id'];
        $isEmployee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'];
        $isAdmin = isset($_SESSION['admin_id']) && $_SESSION['admin_id'];

        $this->auth = [
            'is_logged_in'  => $isCustomer || $isAssociate || $isAgent || $isEmployee || $isAdmin,
            'is_customer'   => $isCustomer,
            'is_associate'  => $isAssociate,
            'is_agent'      => $isAgent,
            'is_employee'   => $isEmployee,
            'is_admin'      => $isAdmin,
        ];
    }

    public function isLoggedIn(): bool
    {
        return $this->auth['is_logged_in'];
    }

    public function auth(): array
    {
        return $this->auth;
    }

    public function userName(): string
    {
        if ($this->auth['is_associate'])    return $_SESSION['associate_name'] ?? 'Associate';
        if ($this->auth['is_agent'])        return $_SESSION['agent_name'] ?? 'Agent';
        if ($this->auth['is_admin'])        return $_SESSION['admin_name'] ?? 'Admin';
        if ($this->auth['is_employee'])     return $_SESSION['employee_name'] ?? 'Employee';
        if ($this->auth['is_customer'])     return $_SESSION['user_name'] ?? 'Customer';
        return '';
    }

    public function userRole(): string
    {
        if ($this->auth['is_associate']) return 'Associate';
        if ($this->auth['is_agent'])     return 'Agent';
        if ($this->auth['is_admin'])     return 'Admin';
        if ($this->auth['is_employee'])  return 'Employee';
        if ($this->auth['is_customer'])  return 'Customer';
        return '';
    }

    public function userIconClass(): string
    {
        if ($this->auth['is_associate']) return 'fa-handshake';
        if ($this->auth['is_agent'])     return 'fa-briefcase';
        if ($this->auth['is_admin'])     return 'fa-user-shield';
        if ($this->auth['is_employee'])  return 'fa-user-tie';
        return 'fa-user';
    }

    public function dashboardUrl(): string
    {
        if ($this->auth['is_associate']) return '/associate/dashboard';
        if ($this->auth['is_agent'])     return '/agent/dashboard';
        if ($this->auth['is_admin'])     return '/admin/dashboard';
        if ($this->auth['is_employee'])  return '/employee/dashboard';
        if ($this->auth['is_customer'])  return '/user/dashboard';
        return '/login';
    }

    public function logoutUrl(): string
    {
        if ($this->auth['is_associate']) return '/associate/logout';
        if ($this->auth['is_agent'])     return '/agent/logout';
        if ($this->auth['is_admin'])     return '/admin/logout';
        if ($this->auth['is_employee'])  return '/employee/logout';
        return '/logout';
    }

    public function roleColorClass(): string
    {
        return $this->auth['is_admin']
            ? 'bg-danger'
            : ($this->auth['is_associate'] ? 'bg-success' : 'bg-primary');
    }

    /**
     * Role-specific dropdown menu items for the user/profile menu.
     */
    public function getUserMenuItems(): array
    {
        if ($this->auth['is_associate']) {
            return [
                ['label' => __('dashboard'),            'url' => '/associate/dashboard',        'icon' => 'fa-tachometer-alt'],
                ['label' => __('post_property'),         'url' => '/associate/list-property',     'icon' => 'fa-plus-circle', 'highlight' => true],
                ['label' => __('my_network'),            'url' => '/associate/genealogy',         'icon' => 'fa-sitemap'],
                ['label' => __('my_leads'),              'url' => '/associate/leads',             'icon' => 'fa-users'],
                ['label' => __('my_properties'),          'url' => '/associate/properties',        'icon' => 'fa-building'],
                ['label' => __('commissions'),           'url' => '/associate/commissions',       'icon' => 'fa-money-bill-wave'],
                ['label' => __('my_profile'),             'url' => '/associate/profile',          'icon' => 'fa-user-cog'],
                ['label' => __('bank_details'),          'url' => '/associate/bank-details',     'icon' => 'fa-university'],
            ];
        }
        if ($this->auth['is_agent']) {
            return [
                ['label' => __('dashboard'),            'url' => '/agent/dashboard',            'icon' => 'fa-tachometer-alt'],
                ['label' => __('my_leads'),              'url' => '/agent/leads',                'icon' => 'fa-users'],
                ['label' => __('properties'),            'url' => '/agent/properties',           'icon' => 'fa-building'],
                ['label' => __('commissions'),           'url' => '/agent/commissions',          'icon' => 'fa-money-bill-wave'],
                ['label' => __('my_profile'),           'url' => '/agent/profile',              'icon' => 'fa-user-cog'],
            ];
        }
        if ($this->auth['is_admin']) {
            return [
                ['label' => __('dashboard'),            'url' => '/admin/dashboard',            'icon' => 'fa-tachometer-alt'],
                ['label' => __('leads'),                'url' => '/admin/leads',                'icon' => 'fa-users'],
                ['label' => __('properties'),           'url' => '/admin/properties',           'icon' => 'fa-building'],
                ['label' => __('god_mode'),             'url' => '/admin/godmode',             'icon' => 'fa-crown'],
                ['label' => __('my_profile'),           'url' => '/admin/profile',             'icon' => 'fa-user-cog'],
            ];
        }
        if ($this->auth['is_employee']) {
            return [
                ['label' => __('dashboard'),            'url' => '/employee/dashboard',          'icon' => 'fa-tachometer-alt'],
                ['label' => __('my_tasks'),             'url' => '/employee/tasks',             'icon' => 'fa-tasks'],
                ['label' => __('hr_attendance'),        'url' => '/employee/attendance',        'icon' => 'fa-clock'],
                ['label' => __('hr_performance'),       'url' => '/employee/performance-page',  'icon' => 'fa-chart-line'],
                ['label' => __('my_profile'),           'url' => '/employee/profile',           'icon' => 'fa-user-cog'],
            ];
        }
        // Customer (default)
        return [
            ['label' => __('dashboard'),            'url' => '/user/dashboard',             'icon' => 'fa-tachometer-alt'],
            ['label' => __('my_bookings'),          'url' => '/user/bookings',               'icon' => 'fa-file-contract'],
            ['label' => __('my_favorites'),         'url' => '/dashboard/favorites',         'icon' => 'fa-heart'],
            ['label' => __('post_property'),         'url' => '/list-property',              'icon' => 'fa-plus-circle', 'highlight' => true],
            ['label' => __('my_properties'),        'url' => '/user/properties',              'icon' => 'fa-building'],
            ['label' => __('my_inquiries'),         'url' => '/user/inquiries',             'icon' => 'fa-envelope'],
            ['label' => __('my_profile'),           'url' => '/user/profile',               'icon' => 'fa-user-cog'],
            ['label' => __('bank_details'),         'url' => '/user/bank-details',          'icon' => 'fa-university'],
        ];
    }

    /* ================================================================
     * PROJECTS / LOCATIONS DATA
     * ================================================================ */

    private function loadHeaderData(): array
    {
        if ($this->headerData !== null) {
            return $this->headerData;
        }

        $locations = [];
        $all = [];

        try {
            $db = \App\Core\Database\Database::getInstance()->getPdo();
            $sql = "SELECT c.id, c.name, c.slug, d.name as district, s.name as state
                    FROM colonies c
                    LEFT JOIN districts d ON c.district_id = d.id
                    LEFT JOIN states s ON d.state_id = s.id
                    WHERE c.is_active = 1
                    ORDER BY d.name, c.name";
            $projects = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($projects as $p) {
                $district = ucfirst(strtolower($p['district'] ?? 'Other'));
                $state    = ucfirst(strtolower($p['state'] ?? ''));
                $locKey   = strtolower($district);

                if (!isset($locations[$locKey])) {
                    $locations[$locKey] = [
                        'name'  => $district,
                        'count' => 0,
                        'state' => $state,
                    ];
                }
                $locations[$locKey]['count']++;

                $all[] = [
                    'id'       => $p['id'],
                    'name'     => $p['name'],
                    'slug'     => $p['slug'],
                    'district' => $district,
                ];
            }
        } catch (\PDOException $e) {
            // Fall through to fallback data below
        }

        if (empty($locations)) {
            // Fallback data when DB query fails or returns nothing
            $locations = [
                'gorakhpur' => ['name' => 'Gorakhpur', 'count' => 3, 'state' => 'Uttar Pradesh'],
                'lucknow'   => ['name' => 'Lucknow',   'count' => 1, 'state' => 'Uttar Pradesh'],
            ];
        }

        if (empty($all)) {
            $all = [
                ['id' => 1, 'name' => 'Suryoday Colony',    'slug' => 'suryoday-colony',       'district' => 'Gorakhpur'],
                ['id' => 2, 'name' => 'Raghunath Nagri',    'slug' => 'raghunath-nagri',       'district' => 'Gorakhpur'],
                ['id' => 3, 'name' => 'Braj Radha Nagri',   'slug' => 'braj-radha-nagri',      'district' => 'Gorakhpur'],
                ['id' => 4, 'name' => 'Budh Bihar Colony',  'slug' => 'budh-bihar-colony',     'district' => 'Gorakhpur'],
            ];
        }

        $this->headerData['locations'] = $locations;
        $this->headerData['projects']  = $all;
        return $this->headerData;
    }

    /**
     * @return array{name: string, count: int, state: string}[]
     */
    public function getProjectLocations(): array
    {
        return $this->loadHeaderData()['locations'];
    }

    /**
     * @return array{id: int, name: string, slug: string, district: string}[]
     */
    public function getAllProjects(): array
    {
        return $this->loadHeaderData()['projects'];
    }

    /* ================================================================
     * SUBMENUS
     * ================================================================ */

    public function getProjectsSubmenu(): array
    {
        $projects = $this->getAllProjects();
        $locations = $this->getProjectLocations();

        $submenu = [
            ['label' => __('nav_all_projects'), 'url' => '/projects', 'icon' => 'fas fa-th-large'],
        ];

        if (!empty($locations)) {
            $submenu[] = ['label' => __('nav_by_location'), 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true];
            foreach ($locations as $loc) {
                $submenu[] = [
                    'label' => $loc['name'],
                    'url'   => '/projects?location=' . urlencode(strtolower($loc['name'])),
                    'icon'  => 'fas fa-map-pin',
                    'badge' => (string) $loc['count'],
                ];
            }
        }

        if (!empty($projects)) {
            $submenu[] = ['label' => __('nav_colonies'), 'url' => '#', 'icon' => 'fas fa-building', 'disabled' => true];
            foreach (array_slice($projects, 0, 10) as $proj) {
                $slug = $proj['slug'] ?: preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($proj['name']));
                $submenu[] = [
                    'label' => $proj['name'],
                    'url'   => '/colony/' . $slug,
                    'icon'  => 'fas fa-home',
                ];
            }
        }

        return $submenu;
    }

    public function getPlotsSubmenu(): array
    {
        $projects = $this->getAllProjects();

        $submenu = [
            ['label' => __('browse_plots'),    'url' => '/plots/browse',                 'icon' => 'fas fa-search'],
            ['label' => __('nav_all_plots'),   'url' => '/plots',                        'icon' => 'fas fa-th-large'],
        ];

        if (!empty($projects)) {
            $submenu[] = ['label' => __('nav_browse_colony'), 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true];
            foreach (array_slice($projects, 0, 10) as $proj) {
                $slug = $proj['slug'] ?: preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($proj['name']));
                $submenu[] = [
                    'label' => $proj['name'],
                    'url'   => '/colony/' . $slug . '/plots',
                    'icon'  => 'fas fa-vector-square',
                ];
            }
        } else {
            // Fallback
            $submenu[] = ['label' => __('nav_browse_colony'), 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true];
            $submenu[] = ['label' => 'Suryoday Colony',       'url' => '/colony/suryoday-colony/plots',       'icon' => 'fas fa-home', 'badge' => '287'];
            $submenu[] = ['label' => 'Raghunath Nagri',       'url' => '/colony/raghunath-nagri/plots',       'icon' => 'fas fa-home', 'badge' => '130'];
            $submenu[] = ['label' => 'Braj Radha Nagri',      'url' => '/colony/braj-radha-nagri/plots',      'icon' => 'fas fa-city'];
            $submenu[] = ['label' => 'Kushinagar Colony',     'url' => '/colony/kushinagar-colony/plots',     'icon' => 'fas fa-home'];
            $submenu[] = ['label' => 'Budh Bihar Colony',     'url' => '/colony/budh-bihar-colony/plots',     'icon' => 'fas fa-home'];
        }

        return $submenu;
    }

    /* ================================================================
     * DESKTOP NAVIGATION ITEMS
     * ================================================================ */

    /**
     * Main desktop navigation items (visible on lg+ screens).
     * Returns a copy of the array previously hardcoded in header.php,
     * now sourced from this helper.
     */
    public function getDesktopNavItems(): array
    {
        return [
            ['label' => __('home'),            'url' => '/',  'icon' => 'fas fa-home'],
            [
                'label'    => __('properties'),
                'icon'     => 'fas fa-building',
                'submenu'  => [
                    ['label' => __('nav_all_properties'),  'url' => '/properties',                'icon' => 'fas fa-th-large'],
                    ['label' => __('nav_buy_properties'),  'url' => '/properties?listing=sale',   'icon' => 'fas fa-shopping-cart'],
                    ['label' => __('nav_rent_properties'), 'url' => '/properties?listing=rent',   'icon' => 'fas fa-key'],
                    ['label' => __('nav_residential'),     'url' => '/properties?type=residential', 'icon' => 'fas fa-home'],
                    ['label' => __('nav_commercial'),      'url' => '/properties?type=commercial', 'icon' => 'fas fa-building'],
                    ['label' => __('nav_plot_land'),       'url' => '/properties?type=plot',     'icon' => 'fas fa-vector-square'],
                    ['label' => __('featured_properties'), 'url' => '/featured-properties',        'icon' => 'fas fa-star'],
                    ['label' => __('live_auctions'),        'url' => '/auctions',                 'icon' => 'fas fa-gavel'],
                ],
            ],
            [
                'label'   => __('plots'),
                'icon'    => 'fas fa-vector-square',
                'submenu' => $this->getPlotsSubmenu(),
            ],
            [
                'label'   => __('projects'),
                'icon'    => 'fas fa-project-diagram',
                'submenu' => $this->getProjectsSubmenu(),
            ],
            [
                'label'       => __('services'),
                'icon'        => 'fas fa-concierge-bell',
                'align_right' => true,
                'submenu'     => [
                    ['label' => __('nav_all_services'),      'url' => '/services',                     'icon' => 'fas fa-concierge-bell'],
                    ['label' => __('nav_home_loan'),          'url' => '/financial-services',            'icon' => 'fas fa-hand-holding-usd'],
                    ['label' => __('nav_legal_services'),     'url' => '/legal/services',                'icon' => 'fas fa-gavel'],
                    ['label' => __('interior_design'),        'url' => '/interior-design',               'icon' => 'fas fa-couch'],
                    ['label' => __('service_construction'),   'url' => '/construction-services',         'icon' => 'fas fa-hard-hat'],
                    ['label' => __('nav_resell_property'),    'url' => '/resell',                      'icon' => 'fas fa-handshake'],
                    ['label' => __('nav_documents'),           'url' => '/documents',                     'icon' => 'fas fa-folder-open'],
                ],
            ],
            [
                'label'       => __('about_us'),
                'icon'        => 'fas fa-info-circle',
                'align_right' => true,
                'submenu'     => [
                    ['label' => __('about_us'),          'url' => '/about',                  'icon' => 'fas fa-info-circle'],
                    ['label' => __('nav_our_team'),      'url' => '/team',                   'icon' => 'fas fa-users'],
                    ['label' => __('nav_gallery'),       'url' => '/gallery',                'icon' => 'fas fa-images'],
                    ['label' => __('nav_careers'),       'url' => '/careers',                'icon' => 'fas fa-briefcase'],
                    ['label' => __('nav_news'),           'url' => '/news',                   'icon' => 'fas fa-newspaper'],
                    ['label' => __('nav_testimonials'),  'url' => '/testimonials',           'icon' => 'fas fa-comment-alt'],
                    ['label' => __('nav_blog'),           'url' => '/blog',                   'icon' => 'fas fa-blog'],
                    ['label' => __('nav_faqs'),           'url' => '/faqs',                   'icon' => 'fas fa-question-circle'],
                ],
            ],
            ['label' => __('contact_us'),  'url' => '/contact',   'icon' => 'fas fa-phone'],
            ['label' => 'Tools Hub',       'url' => '/tools-hub', 'icon' => 'fas fa-flask'],
            ['label' => __('nav_post_property'), 'url' => '/list-property', 'icon' => 'fas fa-plus-circle', 'highlight' => true],
        ];
    }

    /* ================================================================
     * MOBILE NAVBAR ITEMS
     * ================================================================ */

    /**
     * Mobile top-bar quick links (hamburger menu items).
     * This is what was previously $mobile_nav_items merged with $nav_items[16].
     */
    public function getMobileTopBarItems(): array
    {
        $desktopItems = $this->getDesktopNavItems();

        // Build a flat-ish mobile nav: top-level items + a few key extras
        $items = [];

        // Home
        $items[] = ['label' => __('home'), 'url' => '/', 'icon' => 'fas fa-home'];

        // Properties (with submenu)
        foreach ($desktopItems as $item) {
            if (isset($item['submenu'])) {
                $items[] = [
                    'label'   => $item['label'],
                    'url'     => $item['url'] ?? '#',
                    'icon'    => $item['icon'],
                    'submenu' => $item['submenu'],
                ];
            } else {
                $items[] = [
                    'label' => $item['label'],
                    'url'   => $item['url'],
                    'icon'  => $item['icon'],
                ];
            }
        }

        // Contact + Tools Hub
        $items[] = ['label' => __('contact_us'), 'url' => '/contact', 'icon' => 'fas fa-phone'];
        $items[] = ['label' => 'Tools Hub', 'url' => '/tools-hub', 'icon' => 'fas fa-flask'];

        return $items;
    }

    /* ================================================================
     * BOTTOM NAV ITEMS (mobile sticky)
     * ================================================================ */

    /**
     * Items for the mobile sticky bottom navigation.
     */
    public function getMobileBottomNavItems(): array
    {
        $items = [
            ['label' => 'Home',        'url' => '/',             'icon' => 'fas fa-home',        'key' => 'home'],
            ['label' => 'Properties',  'url' => '/properties',   'icon' => 'fas fa-building',    'key' => 'properties'],
            ['label' => 'Search',      'url' => '/search',       'icon' => 'fas fa-search',      'key' => 'search'],
        ];

        if ($this->auth['is_associate']) {
            $items[] = ['label' => 'Dashboard', 'url' => '/associate/dashboard',  'icon' => 'fas fa-tachometer-alt', 'key' => 'dashboard'];
            $items[] = ['label' => 'Profile',   'url' => '/associate/profile',    'icon' => 'fas fa-user',           'key' => 'profile'];
        } elseif ($this->auth['is_agent']) {
            $items[] = ['label' => 'Dashboard', 'url' => '/agent/dashboard',      'icon' => 'fas fa-tachometer-alt', 'key' => 'dashboard'];
            $items[] = ['label' => 'Profile',   'url' => '/agent/profile',        'icon' => 'fas fa-user',           'key' => 'profile'];
        } elseif ($this->auth['is_admin']) {
            $items[] = ['label' => 'Dashboard', 'url' => '/admin/dashboard',      'icon' => 'fas fa-tachometer-alt', 'key' => 'dashboard'];
            $items[] = ['label' => 'Profile',   'url' => '/admin/profile',        'icon' => 'fas fa-user',           'key' => 'profile'];
        } elseif ($this->auth['is_employee']) {
            $items[] = ['label' => 'Dashboard', 'url' => '/employee/dashboard',   'icon' => 'fas fa-tachometer-alt', 'key' => 'dashboard'];
            $items[] = ['label' => 'Profile',   'url' => '/employee/profile',     'icon' => 'fas fa-user',           'key' => 'profile'];
        } elseif ($this->auth['is_customer']) {
            $items[] = ['label' => 'Dashboard', 'url' => '/user/dashboard',       'icon' => 'fas fa-tachometer-alt', 'key' => 'dashboard'];
            $items[] = ['label' => 'Profile',   'url' => '/user/profile',         'icon' => 'fas fa-user',           'key' => 'profile'];
        } else {
            $items[] = ['label' => 'Login',     'url' => '/login',              'icon' => 'fas fa-sign-in-alt',    'key' => 'login'];
            $items[] = ['label' => 'About',     'url' => '/about',              'icon' => 'fas fa-info-circle',      'key' => 'about'];
        }

        return $items;
    }

    /* ================================================================
     * GA4
     * ================================================================ */

    public function ga4Id(): string
    {
        $id = $_ENV['GA4_MEASUREMENT_ID'] ?? getenv('GA4_MEASUREMENT_ID') ?: 'G-PLACEHOLDER';
        return is_string($id) ? trim($id) : 'G-PLACEHOLDER';
    }

    public function isGa4Enabled(): bool
    {
        return $this->ga4Id() !== '';
    }
}
