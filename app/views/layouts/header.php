<?php
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';

// Load site settings from DB (cached in-process)
if (!isset($GLOBALS['_site_settings_cache'])) {
    $GLOBALS['_site_settings_cache'] = [];
    try {
        $scPdo = \App\Core\Database\Database::getInstance()->getPdo();
        $scRows = $scPdo->query("SELECT content_key, content_value FROM site_content WHERE section = 'settings' AND is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
        $GLOBALS['_site_settings_cache'] = $scRows;
    } catch (\Exception $e) { /* graceful fallback */
    }
}
$sc = function ($key, $default = '') {
    return $GLOBALS['_site_settings_cache'][$key] ?? $default;
};

// Google Analytics 4 (gtag.js) — id pulled from GA4_MEASUREMENT_ID env var.
// The placeholder 'G-PLACEHOLDER' is shown in the source for visibility so
// the wiring is obviously present; replace it with a real ID (G-XXXXXXXXXX)
// in .env to enable actual tracking. GA4 ignores unknown IDs at runtime.
$ga4_id = $_ENV['GA4_MEASUREMENT_ID'] ?? getenv('GA4_MEASUREMENT_ID') ?: 'G-PLACEHOLDER';
$ga4_id = is_string($ga4_id) ? trim($ga4_id) : 'G-PLACEHOLDER';
$ga4_enabled = ($ga4_id !== '');

// Emit the gtag loader at most once per request. This MUST happen for both
// self-rendering pages (where header.php is the first thing in the page) AND
// pages wrapped in layouts/base.php (where base.php has already started the
// <head>). The base.php layout does NOT include this snippet itself, so
// header.php is the canonical place.
if ($ga4_enabled && !isset($GLOBALS['_ga4_loader_emitted'])) {
    $GLOBALS['_ga4_loader_emitted'] = true;
}

// Ensure proper HTML document structure (gated to prevent double output)
if (!isset($GLOBALS['_html_doc_started'])) {
    $GLOBALS['_html_doc_started'] = true;
    $page_title = $page_title ?? 'APS Dream Home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/style.css?v=6" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/frontend.css?v=6" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/header.css?v=6" rel="stylesheet">
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/premium-theme.css?v=6" rel="stylesheet">
    <!-- Universal mobile-first responsive overrides -->
    <link href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>/assets/css/mobile-responsive.css" rel="stylesheet">

    <?php if ($ga4_enabled): ?>
    <!-- Google Analytics 4 -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>" async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4_id) ?>"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($ga4_id) ?>', {
        'anonymize_ip': true
    });
    </script>
    <?php endif; ?>

    <?php if (isset($seo) && is_array($seo)): ?>
    <!-- SEO Auto-Injected Meta Tags -->
    <title><?= htmlspecialchars($seo['title'] ?? 'APS Dream Home') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seo['keywords'] ?? '') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($seo['canonical'] ?? '') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($seo['og_title'] ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo['og_description'] ?? '') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seo['og_image'] ?? '') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seo['og_url'] ?? '') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($seo['og_type'] ?? 'website') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= htmlspecialchars($seo['twitter_card'] ?? 'summary_large_image') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($seo['twitter_title'] ?? '') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seo['twitter_description'] ?? '') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seo['twitter_image'] ?? '') ?>">

    <!-- JSON-LD Structured Data -->
    <?php if (!empty($seo['json_ld'])): ?>
    <script type="application/ld+json">
    <?= json_encode($seo['json_ld'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
    </script>
    <?php endif; ?>
    <?php endif; ?>
</head>

<body>
    <?php
} elseif ($ga4_enabled && !isset($GLOBALS['_ga4_loader_emitted_secondary'])) {
    // The base.php layout has already opened <head> but doesn't know about GA.
    // Inject the loader now (right after the </head> would be too late for an
    // async script; we emit it as soon as possible by piggy-backing on the
    // nav include, before <main>). It still loads before page_view fires in
    // the footer.
    $GLOBALS['_ga4_loader_emitted_secondary'] = true;
    ?>
    <!-- Google Analytics 4 -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>" async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4_id) ?>"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($ga4_id) ?>', {
        'anonymize_ip': true
    });
    </script>
    <?php
}
if (!defined('BASE_URL')) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Auto-detect base path: /public/index.php → strip /public suffix
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $basePath = preg_replace('#/public$#', '', $scriptDir);
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}

$projectLocations = [];
$allProjects = [];

// Icon constants
const ICON_TH_LARGE = 'fas fa-th-large';

// Load from hot-path cache (Redis first, file fallback) — 10 minute TTL
$hotPathCacheService = 'App\\Services\\Cache\\HotPathCacheService';
$loadHeaderProjects = function () {
    try {
        $db = \App\Core\Database\Database::getInstance()->getPdo();

        $sql = "SELECT c.id, c.name, c.slug, d.name as district, s.name as state
                FROM colonies c
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                WHERE c.is_active = 1
                ORDER BY d.name, c.name";
        $projects = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $locations = [];
        $all = [];
        foreach ($projects as $p) {
            $district = ucfirst(strtolower($p['district'] ?? 'Other'));
            $state = ucfirst(strtolower($p['state'] ?? ''));

            $locKey = strtolower($district);
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
        return ['locations' => $locations, 'projects' => $all];
    } catch (PDOException $e) {
        return ['locations' => [], 'projects' => []];
    }
};

if (class_exists($hotPathCacheService)) {
    $cachedProjects = $hotPathCacheService::getHeaderProjects($loadHeaderProjects);
} else {
    $cachedProjects = $loadHeaderProjects();
}

if (is_array($cachedProjects) && isset($cachedProjects['locations'], $cachedProjects['projects'])) {
    $projectLocations = $cachedProjects['locations'];
    $allProjects      = $cachedProjects['projects'];
}

$projectsSubmenu = [
    ['label' => __('nav_all_projects'), 'url' => '/projects', 'icon' => 'fas fa-th-large']
];

if (!empty($projectLocations)) {
    $projectsSubmenu[] = ['label' => __('nav_by_location'), 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true];
    foreach ($projectLocations as $loc) {
        $projectsSubmenu[] = [
            'label' => $loc['name'],
            'url' => '/projects?location=' . urlencode(strtolower($loc['name'])),
            'icon' => 'fas fa-map-pin',
            'badge' => (string)$loc['count']
        ];
    }
}

if (!empty($allProjects)) {
    $projectsSubmenu[] = ['label' => __('nav_colonies'), 'url' => '#', 'icon' => 'fas fa-building', 'disabled' => true];
    foreach (array_slice($allProjects, 0, 10) as $proj) {
        $slug = $proj['slug'] ?: preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($proj['name']));
        $projectsSubmenu[] = [
            'label' => $proj['name'],
            'url' => '/colony/' . $slug,
            'icon' => 'fas fa-home'
        ];
    }
}

// Build Plots submenu dynamically from colonies
$plotsSubmenu = [
    ['label' => __('browse_plots'), 'url' => '/plots/browse', 'icon' => 'fas fa-search'],
    ['label' => __('nav_all_plots'), 'url' => '/plots', 'icon' => 'fas fa-th-large']
];

if (!empty($allProjects)) {
    $plotsSubmenu[] = ['label' => __('nav_browse_colony'), 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true];
    foreach (array_slice($allProjects, 0, 10) as $proj) {
        $slug = $proj['slug'] ?: preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($proj['name']));
        $plotsSubmenu[] = [
            'label' => $proj['name'],
            'url' => '/colony/' . $slug . '/plots',
            'icon' => 'fas fa-vector-square'
        ];
    }
}

if (empty($projectsSubmenu) || count($projectsSubmenu) === 1) {
    $projectsSubmenu = [
        ['label' => __('nav_all_projects'), 'url' => '/projects', 'icon' => 'fas fa-th-large'],
        ['label' => __('nav_by_location'), 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true],
        ['label' => 'Gorakhpur', 'url' => '/projects?location=gorakhpur', 'icon' => 'fas fa-map-pin', 'badge' => '3'],
        ['label' => 'Lucknow', 'url' => '/projects?location=lucknow', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
        ['label' => 'Kushinagar', 'url' => '/projects?location=kushinagar', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
        ['label' => 'Varanasi', 'url' => '/projects?location=varanasi', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
        ['label' => __('nav_colonies'), 'url' => '#', 'icon' => 'fas fa-building', 'disabled' => true],
        ['label' => 'Suryoday Colony', 'url' => '/colony/suryoday-colony', 'icon' => 'fas fa-home'],
        ['label' => 'Raghunath Nagri', 'url' => '/colony/raghunath-nagri', 'icon' => 'fas fa-building'],
        ['label' => 'Braj Radha Nagri', 'url' => '/colony/braj-radha-nagri', 'icon' => 'fas fa-city'],
    ];
}

// Determine if we are on the homepage for header styling
$current_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base_path = (string) parse_url(BASE_URL, PHP_URL_PATH);
$current_path = str_replace($base_path, '', $current_path);
$current_path = $current_path ?: '/';
$is_home = ($current_path === '/');
$header_class = $is_home ? 'premium-header hero-header fixed-top' : 'premium-header fixed-top';
?>
    <header class="<?= $header_class ?>" id="mainHeader">
        <nav class="navbar navbar-expand-xl">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                    <img src="<?= BASE_URL ?>/assets/images/logo/apslogonew.jpg" alt="APS Dream Home" class="logo"
                        style="height: 36px; width: auto; max-width: 100px;" loading="eager" fetchpriority="high" />
                </a>

                <!-- Quick Search Bar (Typeahead) -->
                <form class="d-none d-lg-flex align-items-center ms-3 me-2 quick-search-form" role="search"
                    id="quickSearchForm" onsubmit="return quickSearchSubmit(event)" autocomplete="off"
                    style="position: relative; min-width: 200px; max-width: 280px; flex: 0 0 auto;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="fas fa-search text-muted"></i></span>
                        <input type="search" class="form-control border-start-0" id="quickSearchInput"
                            placeholder="<?= __('search_placeholder') ?>" aria-label="Quick search"
                            style="border-left: 0;">
                    </div>
                    <div id="quickSearchResults" class="quick-search-dropdown shadow-lg" style="display: none;"></div>
                </form>

                <button class="navbar-toggler" type="button" id="navbarToggler" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav align-items-center" style="margin-left: 0;">
                        <?php
                        $nav_items = [
                            ['label' => __('home'), 'url' => '/', 'icon' => 'fas fa-home'],
                            [
                                'label' => __('properties'),
                                'icon' => 'fas fa-building',
                                'submenu' => [
                                    ['label' => __('nav_all_properties'), 'url' => '/properties', 'icon' => 'fas fa-th-large'],
                                    ['label' => __('nav_buy_properties'), 'url' => '/properties?listing=sale', 'icon' => 'fas fa-shopping-cart'],
                                    ['label' => __('nav_rent_properties'), 'url' => '/properties?listing=rent', 'icon' => 'fas fa-key'],
                                    ['label' => __('nav_residential'), 'url' => '/properties?type=residential', 'icon' => 'fas fa-home'],
                                    ['label' => __('nav_commercial'), 'url' => '/properties?type=commercial', 'icon' => 'fas fa-building'],
                                    ['label' => __('nav_plot_land'), 'url' => '/properties?type=plot', 'icon' => 'fas fa-vector-square'],
                                    ['label' => __('featured_properties'), 'url' => '/featured-properties', 'icon' => 'fas fa-star'],
                                    ['label' => __('live_auctions'), 'url' => '/auctions', 'icon' => 'fas fa-gavel'],
                                ]
                            ],
                            [
                                'label' => __('plots'),
                                'icon' => 'fas fa-vector-square',
                                'submenu' => $plotsSubmenu ?? [
    ['label' => __('browse_plots'), 'url' => '/plots/browse', 'icon' => 'fas fa-search'],
                                    ['label' => __('nav_all_plots'), 'url' => '/plots', 'icon' => 'fas fa-th-large'],
                                    ['label' => __('nav_by_colony'), 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true],
                                    ['label' => 'Suryoday Colony', 'url' => '/colony/suryoday-colony/plots', 'icon' => 'fas fa-home', 'badge' => '287'],
                                    ['label' => 'Raghunath Nagri', 'url' => '/colony/raghunath-nagri/plots', 'icon' => 'fas fa-home', 'badge' => '130'],
                                    ['label' => 'Braj Radha Nagri', 'url' => '/colony/braj-radha-nagri/plots', 'icon' => 'fas fa-city'],
                                    ['label' => 'Kushinagar Colony', 'url' => '/colony/kushinagar-colony/plots', 'icon' => 'fas fa-home'],
                                    ['label' => 'Budh Bihar Colony', 'url' => '/colony/budh-bihar-colony/plots', 'icon' => 'fas fa-home'],
                                ]
                            ],
                            [
                                'label' => __('projects'),
                                'icon' => 'fas fa-project-diagram',
                                'submenu' => $projectsSubmenu
                            ],
                            [
                                'label' => __('services'),
                                'icon' => 'fas fa-concierge-bell',
                                'submenu' => [
                                    ['label' => __('nav_all_services'), 'url' => '/services', 'icon' => 'fas fa-concierge-bell'],
                                    ['label' => __('nav_home_loan'), 'url' => '/financial-services', 'icon' => 'fas fa-hand-holding-usd'],
                                    ['label' => __('nav_legal_services'), 'url' => '/legal/services', 'icon' => 'fas fa-gavel'],
                                    ['label' => __('interior_design'), 'url' => '/interior-design', 'icon' => 'fas fa-couch'],
                                    ['label' => __('service_construction'), 'url' => '/construction-services', 'icon' => 'fas fa-hard-hat'],
                                    ['label' => __('nav_resell_property'), 'url' => '/resell', 'icon' => 'fas fa-handshake'],
                                    ['label' => __('nav_documents'), 'url' => '/documents', 'icon' => 'fas fa-folder-open'],
                                ]
                            ],
                            [
                                'label' => __('about_us'),
                                'icon' => 'fas fa-info-circle',
                                'submenu' => [
                                    ['label' => __('about_us'), 'url' => '/about', 'icon' => 'fas fa-info-circle'],
                                    ['label' => __('nav_our_team'), 'url' => '/team', 'icon' => 'fas fa-users'],
                                    ['label' => __('nav_gallery'), 'url' => '/gallery', 'icon' => 'fas fa-images'],
                                    ['label' => __('nav_careers'), 'url' => '/careers', 'icon' => 'fas fa-briefcase'],
                                    ['label' => __('nav_news', null, 'News'), 'url' => '/news', 'icon' => 'fas fa-newspaper'],
                                    ['label' => __('nav_testimonials'), 'url' => '/testimonials', 'icon' => 'fas fa-comment-alt'],
                                    ['label' => __('nav_blog'), 'url' => '/blog', 'icon' => 'fas fa-blog'],
                                    ['label' => __('nav_faqs'), 'url' => '/faqs', 'icon' => 'fas fa-question-circle'],
                                ]
                            ],
                            ['label' => __('contact_us'), 'url' => '/contact', 'icon' => 'fas fa-phone'],
                            ['label' => __('nav_post_property'), 'url' => '/list-property', 'icon' => 'fas fa-plus-circle', 'highlight' => true]
                        ];

                        foreach ($nav_items as $item) {
                            if (isset($item['submenu'])) {
                                $is_active = array_reduce($item['submenu'], function ($carry, $sub_item) use ($current_path) {
                                    return $carry || $current_path === $sub_item['url'];
                                }, false);
                                $active_class = $is_active ? 'active' : '';
                                echo '<li class="nav-item dropdown">';
                                echo '<a class="nav-link dropdown-toggle ' . $active_class . '" href="#" data-bs-toggle="dropdown">';
                                echo '<i class="' . $item['icon'] . ' me-1"></i>' . htmlspecialchars($item['label']);
                                echo '</a>';
                                echo '<ul class="dropdown-menu">';
                                foreach ($item['submenu'] as $sub_item) {
                                    if (isset($sub_item['disabled']) && $sub_item['disabled']) {
                                        echo '<li><span class="dropdown-header"><i class="' . $sub_item['icon'] . ' me-2"></i>' . htmlspecialchars($sub_item['label']) . '</span></li>';
                                    } else {
                                        $active_class = ($current_path === $sub_item['url']) ? 'active' : '';
                                        $badge = $sub_item['badge'] ?? '';
                                        // Ensure a visible separator before the badge
                                        $badge_html = $badge ? '&nbsp;<span class="badge bg-primary ms-2">' . $badge . '</span>' : '';
                                        echo '<li><a class="dropdown-item ' . $active_class . '" href="' . BASE_URL . $sub_item['url'] . '"><i class="' . $sub_item['icon'] . ' me-2"></i>' . htmlspecialchars($sub_item['label']) . $badge_html . '</a></li>';
                                    }
                                }
                                echo '</ul>';
                                echo '</li>';
                            } else {
                                $active_class = ($current_path === $item['url']) ? 'active' : '';
                                $highlight_class = (isset($item['highlight']) && $item['highlight']) ? 'highlight-link' : '';
                                echo '<li class="nav-item">';
                                echo '<a class="nav-link ' . $active_class . ' ' . $highlight_class . '" href="' . BASE_URL . $item['url'] . '">';
                                echo '<i class="' . $item['icon'] . ' me-1"></i>' . htmlspecialchars($item['label']);
                                echo '</a>';
                                echo '</li>';
                            }
                        }
                        ?>

                        <!-- Language Switcher -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button">
                                <i class="fas fa-globe me-1"></i> <?= strtoupper($_SESSION['user_language'] ?? 'en') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item <?= ($_SESSION['user_language'] ?? 'en') === 'en' ? 'active' : '' ?>"
                                        href="<?= BASE_URL ?>/language/set/en"><span class="me-2">English</span>
                                        English</a></li>
                                <li><a class="dropdown-item <?= ($_SESSION['user_language'] ?? 'en') === 'hi' ? 'active' : '' ?>"
                                        href="<?= BASE_URL ?>/language/set/hi"><span class="me-2">Hindi</span>
                                        à¤¹à¤¿à¤‚à¤¦à¥€</a></li>
                            </ul>
                        </li>
                        <?php
                        // Check which user type is logged in
                        $isCustomer = isset($_SESSION['user_id']) && $_SESSION['user_id'];
                        $isAssociate = isset($_SESSION['associate_id']) && $_SESSION['associate_id'];
                        $isAgent = isset($_SESSION['agent_id']) && $_SESSION['agent_id'];
                        $isEmployee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'];
                        $isAdmin = isset($_SESSION['admin_id']) && $_SESSION['admin_id'];
                        $isLoggedIn = $isCustomer || $isAssociate || $isAgent || $isEmployee || $isAdmin;

                        if ($isLoggedIn):
                            // Determine user info based on role
                            if ($isAssociate) {
                                $userName = $_SESSION['associate_name'] ?? 'Associate';
                                $userRole = 'Associate';
                                $userIcon = 'fa-handshake';
                                $dashboardUrl = '/associate/dashboard';
                                $menuItems = [
                                    ['label' => __('dashboard'), 'url' => '/associate/dashboard', 'icon' => 'fa-tachometer-alt'],
                                    ['label' => __('post_property'), 'url' => '/associate/list-property', 'icon' => 'fa-plus-circle', 'highlight' => true],
                                    ['label' => __('my_network'), 'url' => '/associate/genealogy', 'icon' => 'fa-sitemap'],
                                    ['label' => __('my_leads'), 'url' => '/associate/leads', 'icon' => 'fa-users'],
                                    ['label' => __('my_properties'), 'url' => '/associate/properties', 'icon' => 'fa-building'],
                                    ['label' => __('commissions'), 'url' => '/associate/commissions', 'icon' => 'fa-money-bill-wave'],
                                    ['label' => __('my_profile'), 'url' => '/associate/profile', 'icon' => 'fa-user-cog'],
                                    ['label' => __('bank_details'), 'url' => '/associate/bank-details', 'icon' => 'fa-university'],
                                ];
                                $logoutUrl = '/associate/logout';
                            } elseif ($isAgent) {
                                $userName = $_SESSION['agent_name'] ?? 'Agent';
                                $userRole = 'Agent';
                                $userIcon = 'fa-briefcase';
                                $dashboardUrl = '/agent/dashboard';
                                $menuItems = [
                                    ['label' => __('dashboard'), 'url' => '/agent/dashboard', 'icon' => 'fa-tachometer-alt'],
                                    ['label' => __('my_leads'), 'url' => '/agent/leads', 'icon' => 'fa-users'],
                                    ['label' => __('properties'), 'url' => '/agent/properties', 'icon' => 'fa-building'],
                                    ['label' => __('commissions'), 'url' => '/agent/commissions', 'icon' => 'fa-money-bill-wave'],
                                    ['label' => __('my_profile'), 'url' => '/agent/profile', 'icon' => 'fa-user-cog'],
                                ];
                                $logoutUrl = '/agent/logout';
                            } elseif ($isAdmin) {
                                $userName = $_SESSION['admin_name'] ?? 'Admin';
                                $userRole = 'Admin';
                                $userIcon = 'fa-user-shield';
                                $dashboardUrl = '/admin/dashboard';
                                $menuItems = [
                                    ['label' => __('dashboard'), 'url' => '/admin/dashboard', 'icon' => 'fa-tachometer-alt'],
                                    ['label' => __('leads'), 'url' => '/admin/leads', 'icon' => 'fa-users'],
                                    ['label' => __('properties'), 'url' => '/admin/properties', 'icon' => 'fa-building'],
                                    ['label' => __('god_mode'), 'url' => '/admin/godmode', 'icon' => 'fa-crown'],
                                    ['label' => __('my_profile'), 'url' => '/admin/profile', 'icon' => 'fa-user-cog'],
                                ];
                                $logoutUrl = '/admin/logout';
                            } elseif ($isEmployee) {
                                $userName = $_SESSION['employee_name'] ?? 'Employee';
                                $userRole = 'Employee';
                                $userIcon = 'fa-user-tie';
                                $dashboardUrl = '/employee/dashboard';
                                $menuItems = [
                                    ['label' => __('dashboard'), 'url' => '/employee/dashboard', 'icon' => 'fa-tachometer-alt'],
                                    ['label' => __('my_tasks'), 'url' => '/employee/tasks', 'icon' => 'fa-tasks'],
                                    ['label' => __('hr_attendance'), 'url' => '/employee/attendance', 'icon' => 'fa-clock'],
                                    ['label' => __('hr_performance'), 'url' => '/employee/performance-page', 'icon' => 'fa-chart-line'],
                                    ['label' => __('my_profile'), 'url' => '/employee/profile', 'icon' => 'fa-user-cog'],
                                ];
                                $logoutUrl = '/employee/logout';
                            } else {
                                // Customer (default)
                                $userName = $_SESSION['user_name'] ?? __('my_account');
                                $userRole = 'Customer';
                                $userIcon = 'fa-user';
                                $dashboardUrl = '/user/dashboard';
                                $menuItems = [
                                    ['label' => __('dashboard'), 'url' => '/user/dashboard', 'icon' => 'fa-tachometer-alt'],
                                    ['label' => __('my_bookings'), 'url' => '/user/bookings', 'icon' => 'fa-file-contract'],
                                    ['label' => __('my_favorites'), 'url' => '/dashboard/favorites', 'icon' => 'fa-heart'],
                                    ['label' => __('post_property'), 'url' => '/list-property', 'icon' => 'fa-plus-circle', 'highlight' => true],
                                    ['label' => __('my_properties'), 'url' => '/user/properties', 'icon' => 'fa-building'],
                                    ['label' => __('my_inquiries'), 'url' => '/user/inquiries', 'icon' => 'fa-envelope'],
                                    ['label' => __('my_profile'), 'url' => '/user/profile', 'icon' => 'fa-user-cog'],
                                    ['label' => __('bank_details'), 'url' => '/user/bank-details', 'icon' => 'fa-university'],
                                ];
                                $logoutUrl = '/user/logout';
                            }
                        ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>/user/notifications" class="nav-link position-relative"
                                title="<?= __('notifications') ?>">
                                <i class="fas fa-bell"></i>
                                <span id="headerNotifBadge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    style="font-size:9px;display:none;">0</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-link" href="#" data-bs-toggle="dropdown">
                                <i class="fas <?php echo $userIcon; ?> me-1"></i>
                                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($userName); ?></span>
                                <span
                                    class="badge bg-<?php echo $isAdmin ? 'danger' : ($isAssociate ? 'success' : 'primary'); ?> ms-1 d-none d-md-inline"><?php echo $userRole; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-header">
                                    <i
                                        class="fas <?php echo $userIcon; ?> me-2"></i><?php echo htmlspecialchars($userName); ?>
                                    <span
                                        class="badge bg-<?php echo $isAdmin ? 'danger' : ($isAssociate ? 'success' : 'primary'); ?> ms-1"><?php echo $userRole; ?></span>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <?php foreach ($menuItems as $item): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo BASE_URL . $item['url']; ?>">
                                        <i
                                            class="fas <?php echo $item['icon']; ?> me-2"></i><?php echo $item['label']; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo BASE_URL . $logoutUrl; ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i><?= __('logout') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-plus me-1"></i><?= __('register') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/register">
                                        <i class="fas fa-user me-2"></i><?= __('customer_registration') ?>
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/associate/register">
                                        <i class="fas fa-handshake me-2"></i><?= __('associate_registration') ?>
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/agent/register">
                                        <i class="fas fa-briefcase me-2"></i><?= __('agent_registration') ?>
                                    </a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-sign-in-alt me-1"></i><?= __('login') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/login">
                                        <i class="fas fa-user me-2"></i><?= __('customer_login') ?>
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/associate/login">
                                        <i class="fas fa-handshake me-2"></i><?= __('associate_login') ?>
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/agent/login">
                                        <i class="fas fa-briefcase me-2"></i><?= __('agent_login') ?>
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/farmer/login">
                                        <i class="fas fa-seedling me-2 text-success"></i><?= __('farmer_login') ?>
                                    </a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <li class="nav-item ms-2">
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+91 92771 21112')) ?>"
                                class="btn btn-call btn-sm">
                                <i class="fas fa-phone me-1"></i>
                                <span
                                    class="d-none d-lg-inline"><?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?></span>
                            </a>
                        </li>
                        <li class="nav-item ms-2 btn-compare">
                            <a href="<?php echo BASE_URL; ?>/compare"
                                class="btn btn-outline-info btn-sm position-relative">
                                <i class="fas fa-balance-scale"></i> <?= __('compare') ?>
                                <span id="compareBadge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    style="display:none;font-size:10px;">0</span>
                            </a>
                        </li>
                        <?php if (!$isLoggedIn): ?>
                        <li class="nav-item ms-2 btn-admin">
                            <a href="<?php echo BASE_URL; ?>/admin/login" class="btn btn-admin btn-sm">
                                <i class="fas fa-user-lock me-1"></i>
                                <span class="d-none d-lg-inline"><?= __('admin') ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>


    <!-- Header styles moved to /assets/css/header.css (single source of truth) -->

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    window.BASE_URL = '<?php echo BASE_URL; ?>';

    function updateHeaderNotifCount() {
        var b = document.getElementById('headerNotifBadge');
        if (!b) return;
        <?php
        $notifUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? $_SESSION['associate_id'] ?? $_SESSION['employee_id'] ?? null;
        if ($notifUserId): ?>
        fetch(BASE_URL + '/api/user/notifications/unread-count').then(function(r) {
            return r.json();
        }).then(function(d) {
            var c = d.count || 0;
            b.textContent = c;
            b.style.display = c > 0 ? 'inline' : 'none';
        }).catch(function() {});
        <?php endif; ?>
    }
    document.addEventListener('DOMContentLoaded', function() {
        var header = document.getElementById('mainHeader');
        if (!header) return;
        var toggler = document.getElementById('navbarToggler');
        var navCollapse = document.getElementById('navbarNav');
        if (!toggler || !navCollapse) return;

        // Create backdrop overlay for mobile menu
        var backdrop = document.createElement('div');
        backdrop.className = 'nav-backdrop';
        document.body.appendChild(backdrop);

        function openMenu() {
            navCollapse.classList.add('show');
            backdrop.classList.add('show');
            header.classList.add('menu-open');
            toggler.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            navCollapse.classList.remove('show');
            backdrop.classList.remove('show');
            header.classList.remove('menu-open');
            toggler.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        function isMobile() {
            return window.innerWidth <= 1199.98;
        }

        // Toggle mobile menu on hamburger click
        toggler.addEventListener('click', function() {
            if (navCollapse.classList.contains('show')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        // Close on backdrop click
        backdrop.addEventListener('click', closeMenu);

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navCollapse.classList.contains('show')) {
                closeMenu();
            }
        });

        // Prevent Bootstrap dropdown on mobile; use custom expand instead
        function setupDropdowns() {
            var mobile = isMobile();
            navCollapse.querySelectorAll('.dropdown-toggle').forEach(function(dt) {
                if (mobile) {
                    dt.removeAttribute('data-bs-toggle');
                } else {
                    dt.setAttribute('data-bs-toggle', 'dropdown');
                }
            });
        }
        setupDropdowns();

        // Re-evaluate on resize (cross breakpoint)
        var resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(setupDropdowns, 150);
        });

        // Click handler for mobile dropdown toggles
        navCollapse.addEventListener('click', function(e) {
            var dt = e.target.closest('.dropdown-toggle');
            if (!dt) return;
            // Only handle clicks when in mobile mode (no data-bs-toggle)
            if (dt.hasAttribute('data-bs-toggle')) return;
            e.preventDefault();
            e.stopPropagation();
            var menu = dt.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.classList.toggle('show-mobile');
            }
        });

        // Notification count polling
        updateHeaderNotifCount();
        setInterval(updateHeaderNotifCount, 30000);

        // Scroll effect
        var scrollTimer;
        window.addEventListener('scroll', function() {
            if (scrollTimer) cancelAnimationFrame(scrollTimer);
            scrollTimer = requestAnimationFrame(function() {
                if (window.scrollY > 50) {
                    header.classList.add('header-scrolled');
                    header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.1)';
                } else {
                    header.classList.remove('header-scrolled');
                    header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.06)';
                }
            });
        });

        // Sync aria-expanded for Bootstrap dropdowns (desktop only)
        header.querySelectorAll('.dropdown-toggle[data-bs-toggle="dropdown"]').forEach(function(dt) {
            dt.addEventListener('shown.bs.dropdown', function() {
                dt.setAttribute('aria-expanded', 'true');
            });
            dt.addEventListener('hidden.bs.dropdown', function() {
                dt.setAttribute('aria-expanded', 'false');
            });
        });

        // Close mobile menu on window resize above breakpoint
        window.addEventListener('resize', function() {
            if (!isMobile() && navCollapse.classList.contains('show')) {
                closeMenu();
            }
        });
    });
    </script>

    <!-- Quick Search Typeahead -->
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    (function() {
        const input = document.getElementById('quickSearchInput');
        const dropdown = document.getElementById('quickSearchResults');
        if (!input || !dropdown) return;

        let debounceTimer = null;
        let activeIndex = -1;
        let lastResults = [];

        function renderResults(results) {
            if (!results.length) {
                dropdown.innerHTML =
                    '<div class="quick-search-result text-muted"><i class="fas fa-info-circle"></i><span class="label">No matches found</span></div>';
                dropdown.style.display = 'block';
                return;
            }

            let html = '';
            results.forEach((r, i) => {
                const icon = r.type === 'property' ? 'fa-building' :
                    r.type === 'location' ? 'fa-map-marker-alt' :
                    'fa-tag';
                html += `<a href="${r.url}" class="quick-search-result" data-idx="${i}">
                <i class="fas ${icon}"></i>
                <span class="label">${escapeHtml(r.label)}</span>
                <span class="type-tag">${r.type}</span>
            </a>`;
            });
            html += `<div class="quick-search-footer">
            <a href="${BASE_URL}/properties?q=${encodeURIComponent(input.value)}" class="text-primary small text-decoration-none">
                <i class="fas fa-search me-1"></i>See all results for "${escapeHtml(input.value)}"
            </a>
        </div>`;
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';

            dropdown.querySelectorAll('.quick-search-result').forEach(el => {
                el.addEventListener('mouseenter', () => {
                    activeIndex = parseInt(el.dataset.idx);
                    updateActive();
                });
            });
        }

        function escapeHtml(s) {
            return String(s || '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function updateActive() {
            dropdown.querySelectorAll('.quick-search-result').forEach((el, i) => {
                el.classList.toggle('active', i === activeIndex);
            });
        }

        function search(q) {
            if (q.length < 2) {
                dropdown.style.display = 'none';
                return;
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetch(BASE_URL + '/api/saved-searches/autocomplete?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            lastResults = d.results;
                            renderResults(d.results);
                        }
                    })
                    .catch(() => {});
            }, 200);
        }

        input.addEventListener('input', e => {
            activeIndex = -1;
            search(e.target.value.trim());
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2 && lastResults.length) {
                dropdown.style.display = 'block';
            }
        });

        input.addEventListener('keydown', e => {
            const items = dropdown.querySelectorAll('.quick-search-result');
            if (e.key === 'ArrowDown' && items.length) {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                updateActive();
            } else if (e.key === 'ArrowUp' && items.length) {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                updateActive();
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && lastResults[activeIndex]) {
                    e.preventDefault();
                    window.location.href = lastResults[activeIndex].url;
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    })();

    function quickSearchSubmit(e) {
        e.preventDefault();
        const q = document.getElementById('quickSearchInput').value.trim();
        if (q) window.location.href = BASE_URL + '/properties?q=' + encodeURIComponent(q);
        return false;
    }
    </script>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>" src="<?php echo BASE_URL; ?>/js/visitor-tracking.js" defer></script>