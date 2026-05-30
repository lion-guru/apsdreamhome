<?php
// Ensure proper HTML document structure (gated to prevent double output)
if (!isset($GLOBALS['_html_doc_started'])) {
    $GLOBALS['_html_doc_started'] = true;
    $page_title = $page_title ?? 'APS Dream Home';
    ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?></title>
</head>
<body>
<?php
}
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}

$cacheFile = defined('APP_ROOT') ? APP_ROOT . '/storage/cache/header_projects.cache' : null;
$cacheTtl = 300; // 5 minutes
$projectLocations = [];
$allProjects = [];

// Try loading from cache
if ($cacheFile && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    $cached = @unserialize(file_get_contents($cacheFile));
    if ($cached && isset($cached['locations'], $cached['projects'])) {
        $projectLocations = $cached['locations'];
        $allProjects = $cached['projects'];
    }
}

if (empty($allProjects)) {
    try {
        $db = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT c.id, c.name, c.slug, d.name as district, s.name as state
                FROM colonies c
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                WHERE c.is_active = 1
                ORDER BY d.name, c.name";
        $projects = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($projects as $p) {
            $district = ucfirst(strtolower($p['district'] ?? 'Other'));
            $state = ucfirst(strtolower($p['state'] ?? ''));

            $locKey = strtolower($district);
            if (!isset($projectLocations[$locKey])) {
                $projectLocations[$locKey] = [
                    'name' => $district,
                    'count' => 0,
                    'state' => $state
                ];
            }
            $projectLocations[$locKey]['count']++;

            $allProjects[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'district' => $district
            ];
        }
        // Save to cache
        if ($cacheFile) {
            @file_put_contents($cacheFile, serialize(['locations' => $projectLocations, 'projects' => $allProjects]));
        }
    } catch (PDOException $e) {
        $projectLocations = [];
        $allProjects = [];
    }
}

$projectsSubmenu = [
    ['label' => 'All Projects', 'url' => '/projects', 'icon' => 'fas fa-th-large']
];

if (!empty($projectLocations)) {
    $projectsSubmenu[] = ['label' => '── By Location ──', 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true];
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
    $projectsSubmenu[] = ['label' => '── Colonies ──', 'url' => '#', 'icon' => 'fas fa-building', 'disabled' => true];
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
    ['label' => 'All Plots', 'url' => '/plots', 'icon' => 'fas fa-th-large']
];

if (!empty($allProjects)) {
    $plotsSubmenu[] = ['label' => '── Browse by Colony ──', 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true];
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
        ['label' => 'All Projects', 'url' => '/projects', 'icon' => 'fas fa-th-large'],
        ['label' => '── By Location ──', 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true],
        ['label' => 'Gorakhpur', 'url' => '/projects?location=gorakhpur', 'icon' => 'fas fa-map-pin', 'badge' => '3'],
        ['label' => 'Lucknow', 'url' => '/projects?location=lucknow', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
        ['label' => 'Kushinagar', 'url' => '/projects?location=kushinagar', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
        ['label' => 'Varanasi', 'url' => '/projects?location=varanasi', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
        ['label' => '── Colonies ──', 'url' => '#', 'icon' => 'fas fa-building', 'disabled' => true],
        ['label' => 'Suryoday Colony', 'url' => '/colony/suryoday-colony', 'icon' => 'fas fa-home'],
        ['label' => 'Raghunath Nagri', 'url' => '/colony/raghunath-nagri', 'icon' => 'fas fa-building'],
        ['label' => 'Braj Radha Nagri', 'url' => '/colony/braj-radha-nagri', 'icon' => 'fas fa-city'],
    ];
}
?>
<header class="premium-header fixed-top" id="mainHeader">
    <nav class="navbar navbar-expand-xl">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg" alt="APS Dream Home" class="logo" style="height: 40px; width: auto; max-width: 130px;">
            </a>

            <button class="navbar-toggler" type="button" id="navbarToggler" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav align-items-center" style="margin-left: auto;">
                    <?php
                    $current_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
                    $base_path = (string) parse_url(BASE_URL, PHP_URL_PATH);
                    $current_path = str_replace($base_path, '', $current_path);
                    $current_path = $current_path ?: '/';

                    $nav_items = [
                        ['label' => 'Home', 'url' => '/', 'icon' => 'fas fa-home'],
                        [
                            'label' => 'Properties',
                            'icon' => 'fas fa-building',
                            'submenu' => [
                                ['label' => 'All Properties', 'url' => '/properties', 'icon' => 'fas fa-th-large'],
                                ['label' => 'Buy Properties', 'url' => '/properties?listing=sale', 'icon' => 'fas fa-shopping-cart'],
                                ['label' => 'Rent Properties', 'url' => '/properties?listing=rent', 'icon' => 'fas fa-key'],
                                ['label' => 'Residential', 'url' => '/properties?type=residential', 'icon' => 'fas fa-home'],
                                ['label' => 'Commercial', 'url' => '/properties?type=commercial', 'icon' => 'fas fa-building'],
                                ['label' => 'Plot/Land', 'url' => '/properties?type=plot', 'icon' => 'fas fa-vector-square'],
                            ]
                        ],
                        [
                            'label' => 'Plots',
                            'icon' => 'fas fa-vector-square',
                            'submenu' => $plotsSubmenu ?? [
                                ['label' => 'All Plots', 'url' => '/plots', 'icon' => 'fas fa-th-large'],
                                ['label' => '── By Colony ──', 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true],
                                ['label' => 'Suryoday Colony', 'url' => '/colony/suryoday-colony/plots', 'icon' => 'fas fa-home', 'badge' => '287'],
                                ['label' => 'Raghunath Nagri', 'url' => '/colony/raghunath-nagri/plots', 'icon' => 'fas fa-home', 'badge' => '130'],
                                ['label' => 'Braj Radha Nagri', 'url' => '/colony/braj-radha-nagri/plots', 'icon' => 'fas fa-city'],
                                ['label' => 'Kushinagar Colony', 'url' => '/colony/kushinagar-colony/plots', 'icon' => 'fas fa-home'],
                                ['label' => 'Budh Bihar Colony', 'url' => '/colony/budh-bihar-colony/plots', 'icon' => 'fas fa-home'],
                            ]
                        ],
                        [
                            'label' => 'Projects',
                            'icon' => 'fas fa-project-diagram',
                            'submenu' => $projectsSubmenu
                        ],
                        [
                            'label' => 'Services',
                            'icon' => 'fas fa-concierge-bell',
                            'submenu' => [
                                ['label' => 'All Services', 'url' => '/services', 'icon' => 'fas fa-concierge-bell'],
                                ['label' => 'Home Loan', 'url' => '/financial-services', 'icon' => 'fas fa-hand-holding-usd'],
                                ['label' => 'Legal Services', 'url' => '/legal-services', 'icon' => 'fas fa-gavel'],
                                ['label' => 'Interior Design', 'url' => '/interior-design', 'icon' => 'fas fa-couch'],
                                ['label' => 'Resell Property', 'url' => '/resell', 'icon' => 'fas fa-handshake'],
                                ['label' => 'Documents', 'url' => '/documents', 'icon' => 'fas fa-folder-open'],
                            ]
                        ],
                        [
                            'label' => 'About',
                            'icon' => 'fas fa-info-circle',
                            'submenu' => [
                                ['label' => 'About Us', 'url' => '/about', 'icon' => 'fas fa-info-circle'],
                                ['label' => 'Our Team', 'url' => '/team', 'icon' => 'fas fa-users'],
                                ['label' => 'Careers', 'url' => '/careers', 'icon' => 'fas fa-briefcase'],
                                ['label' => 'Testimonials', 'url' => '/testimonials', 'icon' => 'fas fa-comment-alt'],
                                ['label' => 'Blog', 'url' => '/blog', 'icon' => 'fas fa-blog'],
                                ['label' => 'FAQs', 'url' => '/faqs', 'icon' => 'fas fa-question-circle'],
                            ]
                        ],
                        ['label' => 'Contact', 'url' => '/contact', 'icon' => 'fas fa-phone'],
                        ['label' => 'Post Property FREE', 'url' => '/list-property', 'icon' => 'fas fa-plus-circle', 'highlight' => true]
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
                            <li><a class="dropdown-item <?= ($_SESSION['user_language'] ?? 'en') === 'en' ? 'active' : '' ?>" href="<?= BASE_URL ?>/language/set/en"><span class="me-2">🇬🇧</span> English</a></li>
                            <li><a class="dropdown-item <?= ($_SESSION['user_language'] ?? 'en') === 'hi' ? 'active' : '' ?>" href="<?= BASE_URL ?>/language/set/hi"><span class="me-2">🇮🇳</span> हिंदी</a></li>
                        </ul>
                    </li>
                    <?php
                    // Check which user type is logged in
                    $isCustomer = isset($_SESSION['user_id']) && $_SESSION['user_id'];
                    $isAssociate = isset($_SESSION['associate_id']) && $_SESSION['associate_id'];
                    $isAgent = isset($_SESSION['agent_id']) && $_SESSION['agent_id'];
                    $isEmployee = isset($_SESSION['employee_id']) && $_SESSION['employee_id'];
                    $isAdmin = isset($_SESSION['admin_user_id']) && $_SESSION['admin_user_id'];
                    $isLoggedIn = $isCustomer || $isAssociate || $isAgent || $isEmployee || $isAdmin;

                    if ($isLoggedIn):
                        // Determine user info based on role
                        if ($isAssociate) {
                            $userName = $_SESSION['associate_name'] ?? 'Associate';
                            $userRole = 'Associate';
                            $userIcon = 'fa-handshake';
                            $dashboardUrl = '/associate/dashboard';
                            $menuItems = [
                                ['label' => 'Dashboard', 'url' => '/associate/dashboard', 'icon' => 'fa-tachometer-alt'],
                                ['label' => 'Post Property', 'url' => '/associate/list-property', 'icon' => 'fa-plus-circle', 'highlight' => true],
                                ['label' => 'My Network', 'url' => '/associate/genealogy', 'icon' => 'fa-sitemap'],
                                ['label' => 'My Leads', 'url' => '/associate/leads', 'icon' => 'fa-users'],
                                ['label' => 'My Properties', 'url' => '/associate/properties', 'icon' => 'fa-building'],
                                ['label' => 'Commissions', 'url' => '/associate/commissions', 'icon' => 'fa-money-bill-wave'],
                                ['label' => 'My Profile', 'url' => '/associate/profile', 'icon' => 'fa-user-cog'],
                                ['label' => 'Bank Details', 'url' => '/associate/bank-details', 'icon' => 'fa-university'],
                            ];
                            $logoutUrl = '/associate/logout';
                        } elseif ($isAgent) {
                            $userName = $_SESSION['agent_name'] ?? 'Agent';
                            $userRole = 'Agent';
                            $userIcon = 'fa-briefcase';
                            $dashboardUrl = '/agent/dashboard';
                            $menuItems = [
                                ['label' => 'Dashboard', 'url' => '/agent/dashboard', 'icon' => 'fa-tachometer-alt'],
                                ['label' => 'My Leads', 'url' => '/agent/leads', 'icon' => 'fa-users'],
                                ['label' => 'Properties', 'url' => '/agent/properties', 'icon' => 'fa-building'],
                                ['label' => 'Commissions', 'url' => '/agent/commissions', 'icon' => 'fa-money-bill-wave'],
                                ['label' => 'My Profile', 'url' => '/agent/profile', 'icon' => 'fa-user-cog'],
                            ];
                            $logoutUrl = '/agent/logout';
                        } elseif ($isEmployee) {
                            $userName = $_SESSION['employee_name'] ?? 'Employee';
                            $userRole = 'Employee';
                            $userIcon = 'fa-user-tie';
                            $dashboardUrl = '/employee/dashboard';
                            $menuItems = [
                                ['label' => 'Dashboard', 'url' => '/employee/dashboard', 'icon' => 'fa-tachometer-alt'],
                                ['label' => 'My Tasks', 'url' => '/employee/tasks', 'icon' => 'fa-tasks'],
                                ['label' => 'Attendance', 'url' => '/employee/attendance', 'icon' => 'fa-clock'],
                                ['label' => 'Performance', 'url' => '/employee/performance', 'icon' => 'fa-chart-line'],
                                ['label' => 'My Profile', 'url' => '/employee/profile', 'icon' => 'fa-user-cog'],
                            ];
                            $logoutUrl = '/employee/logout';
                        } elseif ($isAdmin) {
                            $userName = $_SESSION['admin_name'] ?? 'Admin';
                            $userRole = 'Admin';
                            $userIcon = 'fa-user-shield';
                            $dashboardUrl = '/admin/dashboard';
                            $menuItems = [
                                ['label' => 'Admin Dashboard', 'url' => '/admin/dashboard', 'icon' => 'fa-tachometer-alt'],
                                ['label' => 'Leads', 'url' => '/admin/leads', 'icon' => 'fa-users'],
                                ['label' => 'Properties', 'url' => '/admin/properties', 'icon' => 'fa-building'],
                                ['label' => 'God Mode', 'url' => '/admin/godmode', 'icon' => 'fa-crown'],
                                ['label' => 'My Profile', 'url' => '/admin/profile', 'icon' => 'fa-user-cog'],
                            ];
                            $logoutUrl = '/admin/logout';
                        } else {
                            // Customer (default)
                            $userName = $_SESSION['user_name'] ?? 'My Account';
                            $userRole = 'Customer';
                            $userIcon = 'fa-user';
                            $dashboardUrl = '/user/dashboard';
                            $menuItems = [
                                ['label' => 'Dashboard', 'url' => '/user/dashboard', 'icon' => 'fa-tachometer-alt'],
                                ['label' => 'My Bookings', 'url' => '/user/bookings', 'icon' => 'fa-file-contract'],
                                ['label' => 'My Favorites', 'url' => '/dashboard/favorites', 'icon' => 'fa-heart'],
                                ['label' => 'Post Property', 'url' => '/list-property', 'icon' => 'fa-plus-circle', 'highlight' => true],
                                ['label' => 'My Properties', 'url' => '/user/properties', 'icon' => 'fa-building'],
                                ['label' => 'My Inquiries', 'url' => '/user/inquiries', 'icon' => 'fa-envelope'],
                                ['label' => 'My Profile', 'url' => '/user/profile', 'icon' => 'fa-user-cog'],
                                ['label' => 'Bank Details', 'url' => '/user/bank-details', 'icon' => 'fa-university'],
                            ];
                            $logoutUrl = '/user/logout';
                        }
                    ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-link" href="#" data-bs-toggle="dropdown">
                                <i class="fas <?php echo $userIcon; ?> me-1"></i>
                                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($userName); ?></span>
                                <span class="badge bg-<?php echo $isAdmin ? 'danger' : ($isAssociate ? 'success' : 'primary'); ?> ms-1 d-none d-md-inline"><?php echo $userRole; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-header">
                                    <i class="fas <?php echo $userIcon; ?> me-2"></i><?php echo htmlspecialchars($userName); ?>
                                    <span class="badge bg-<?php echo $isAdmin ? 'danger' : ($isAssociate ? 'success' : 'primary'); ?> ms-1"><?php echo $userRole; ?></span>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <?php foreach ($menuItems as $item): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo BASE_URL . $item['url']; ?>">
                                            <i class="fas <?php echo $item['icon']; ?> me-2"></i><?php echo $item['label']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo BASE_URL . $logoutUrl; ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/register">
                                        <i class="fas fa-user me-2"></i>Customer Registration
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/associate/register">
                                        <i class="fas fa-handshake me-2"></i>Associate Registration
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/agent/register">
                                        <i class="fas fa-briefcase me-2"></i>Agent Registration
                                    </a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/login">
                                        <i class="fas fa-user me-2"></i>Customer Login
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/associate/login">
                                        <i class="fas fa-handshake me-2"></i>Associate Login
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/agent/login">
                                        <i class="fas fa-briefcase me-2"></i>Agent Login
                                    </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item ms-2">
                        <a href="tel:+919277121112" class="btn btn-call btn-sm">
                            <i class="fas fa-phone me-1"></i>
                            <span class="d-none d-lg-inline">+91 92771 21112</span>
                        </a>
                    </li>
                    <li class="nav-item ms-2 btn-compare">
                        <a href="<?php echo BASE_URL; ?>/compare" class="btn btn-outline-info btn-sm position-relative">
                            <i class="fas fa-balance-scale"></i> Compare
                            <span id="compareBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;font-size:10px;">0</span>
                        </a>
                    </li>
                    <?php if (!$isLoggedIn): ?>
                        <li class="nav-item ms-2 btn-admin">
                            <a href="<?php echo BASE_URL; ?>/admin/login" class="btn btn-admin btn-sm">
                                <i class="fas fa-user-lock me-1"></i>
                                <span class="d-none d-lg-inline">Admin</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Ad Banner -->
<?php
try {
    $adService = new \App\Services\AdManagerService();
    echo $adService->renderSlot('header_banner');
    unset($adService);
} catch (\Exception $e) {
    // Ad service unavailable
}
?>

<link href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/header.css" rel="stylesheet">
<style>
    /* Premium Header Styling */
    .premium-header {
        background: #ffffff;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }
    .premium-header::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, #4f46e5, #7c3aed, #a855f7, #4f46e5);
        background-size: 300% 100%;
        animation: gradientSlide 4s ease infinite;
        pointer-events: none;
    }
    @keyframes gradientSlide {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    .premium-header.header-scrolled {
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    /* Desktop nav links */
    .premium-header .navbar-nav .nav-link {
        font-weight: 500;
        font-size: 14px;
        padding: 24px 12px !important;
        color: #1e293b;
        position: relative;
        transition: color 0.2s;
    }
    .premium-header .navbar-nav .nav-link::after {
        content: '';
        position: absolute;
        bottom: 12px;
        left: 50%;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #4f46e5, #7c3aed);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 2px;
    }
    .premium-header .navbar-nav .nav-link:hover::after,
    .premium-header .navbar-nav .nav-link.active::after {
        width: 60%;
    }
    .premium-header .navbar-nav .nav-link:hover { color: #4f46e5; }
    .premium-header .navbar-nav .nav-link.active { color: #4f46e5; }

    /* Premium dropdown menus */
    .premium-header .dropdown-menu {
        border: none;
        border-radius: 12px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.12), 0 4px 12px rgba(0,0,0,0.06);
        padding: 8px;
        margin-top: 8px;
        background: #fff;
        animation: dropdownIn 0.2s ease;
        min-width: 200px;
    }
    .premium-header .dropdown-menu .dropdown-item {
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        transition: all 0.15s;
    }
    .premium-header .dropdown-menu .dropdown-item:hover {
        background: #f1f5f9;
        color: #4f46e5;
        transform: translateX(4px);
    }
    .premium-header .dropdown-menu .dropdown-item i { width: 20px; color: #4f46e5; }
    .premium-header .dropdown-menu .dropdown-header { font-size: 11px; color: #94a3b8; padding: 6px 14px; letter-spacing: 0.5px; }
    @keyframes dropdownIn {
        from { opacity: 0; transform: translateY(-8px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Call & Admin buttons */
    .btn-call {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border: none;
        color: #fff !important;
        border-radius: 24px;
        padding: 8px 18px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s;
        box-shadow: 0 2px 12px rgba(34,197,94,0.3);
    }
    .btn-call:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(34,197,94,0.4); }
    .btn-admin {
        background: linear-gradient(135deg, #1e293b, #334155);
        border: none;
        color: #fff !important;
        border-radius: 24px;
        padding: 8px 16px;
        font-weight: 500;
        font-size: 13px;
        transition: all 0.3s;
    }
    .btn-admin:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,0.2); }

    /* Highlighted nav item (Post Property FREE) */
    .premium-header .nav-link[style*="background"] {
        border-radius: 24px !important;
        margin: 12px 0 !important;
    }

    /* Mobile menu enhancements */
    .navbar-toggler { border: none; padding: 8px; transition: transform .3s; position: relative; z-index: 9999; }
    .navbar-toggler[aria-expanded="true"] { transform: rotate(90deg); }
    .navbar-toggler-icon { background-image: none; display: flex; align-items: center; justify-content: center; }
    .navbar-toggler-icon::before { content: '\f0c9'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 1.3rem; color: #4f46e5; }
    .navbar-toggler[aria-expanded="true"] .navbar-toggler-icon::before { content: '\f00d'; }
    
    /* Mobile backdrop overlay */
    .nav-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.5); z-index: 9997; opacity: 0; transition: opacity .3s; }
    .nav-backdrop.show { display: block; opacity: 1; }
    .premium-header.menu-open { z-index: 9999; }
    .premium-header.menu-open .navbar-toggler { z-index: 10000; }
    
    @media (max-width: 1199.98px) {
        .premium-header .navbar-collapse {
            position: fixed; top: 0; left: 0; width: 85%; max-width: 320px; height: 100vh;
            background: #fff; z-index: 9998; padding: 20px 16px; overflow-y: auto;
            transform: translateX(-100%); transition: transform .3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 4px 0 30px rgba(0,0,0,.15);
            display: block !important;
        }
        .premium-header .navbar-collapse.show { transform: translateX(0); }
        .premium-header .navbar-nav { margin-left: 0 !important; flex-direction: column; width: 100%; }
        .premium-header .navbar-nav .nav-item { width: 100%; }
        .premium-header .navbar-nav .nav-link { padding: 12px 10px; border-radius: 8px; font-size: 14px; }
        .premium-header .navbar-nav .nav-link::after { display: none; }
        .premium-header .navbar-nav .dropdown-menu {
            position: static !important; border: none; box-shadow: none;
            padding-left: 15px; background: #f8fafc; border-radius: 8px;
            margin: 4px 0; display: block !important; max-height: 0; overflow: hidden;
            transition: max-height .3s ease; padding-top: 0; padding-bottom: 0;
            animation: none;
        }
        .premium-header .navbar-nav .dropdown-menu.show-mobile { max-height: 2000px; padding-top: 8px; padding-bottom: 8px; }
        .premium-header .navbar-nav .dropdown-menu .dropdown-item { padding: 10px 12px; border-radius: 6px; }
        .premium-header .navbar-nav .dropdown-menu .dropdown-item:hover { background: #e2e8f0; transform: none; }
        .premium-header .ms-2 { margin-left: 0 !important; margin-top: 8px; }
        #compareBadge, .btn-compare, .btn-admin { display: none !important; }
        /* Add brand to top of mobile menu */
        .premium-header .navbar-collapse::before {
            content: 'APS Dream Home';
            display: block;
            font-weight: 700;
            font-size: 18px;
            color: #4f46e5;
            padding: 12px 0 16px;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            letter-spacing: -0.3px;
        }
    }
    @media (min-width: 768px) and (max-width: 1199.98px) {
        .premium-header .navbar-collapse { width: 60%; max-width: 340px; }
        .premium-header .navbar-brand img { height: 38px; }
    }
    @media (max-width: 400px) {
        .premium-header .navbar-brand img { height: 32px; }
        .premium-header .navbar-brand span { font-size: 14px; }
        .btn-call { font-size: 12px; padding: 4px 8px; }
    }
    main { padding-top: var(--header-height, 80px); }
</style>

<script>
    window.BASE_URL = '<?php echo BASE_URL; ?>';

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
        function isMobile() { return window.innerWidth <= 1199.98; }

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
            dt.addEventListener('shown.bs.dropdown', function() { dt.setAttribute('aria-expanded', 'true'); });
            dt.addEventListener('hidden.bs.dropdown', function() { dt.setAttribute('aria-expanded', 'false'); });
        });

        // Close mobile menu on window resize above breakpoint
        window.addEventListener('resize', function() {
            if (!isMobile() && navCollapse.classList.contains('show')) {
                closeMenu();
            }
        });
    });
</script>

<script src="<?php echo BASE_URL; ?>/js/visitor-tracking.js" defer></script>