<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
?>
<header class="premium-header fixed-top" id="mainHeader">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg" alt="APS Dream Home" class="logo">
                <span class="brand-text">APS Dream Home</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php
                    $current_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
                    $base_path = (string) parse_url(BASE_URL, PHP_URL_PATH);
                    $current_path = str_replace($base_path, '', $current_path);
                    $current_path = $current_path ?: '/';

                    $nav_items = [
                        ['label' => 'Home', 'url' => '/', 'icon' => 'fas fa-home'],
                        ['label' => 'Properties', 'url' => '/properties', 'icon' => 'fas fa-building'],
                        [
                            'label' => 'Projects', 
                            'icon' => 'fas fa-project-diagram',
                            'submenu' => [
                                ['label' => 'All Projects', 'url' => '/company/projects', 'icon' => 'fas fa-th-large'],
                                ['label' => '── By Location ──', 'url' => '#', 'icon' => 'fas fa-map-marker-alt', 'disabled' => true],
                                ['label' => 'Gorakhpur', 'url' => '/company/projects?location=gorakhpur', 'icon' => 'fas fa-map-pin', 'badge' => '3'],
                                ['label' => 'Lucknow', 'url' => '/company/projects?location=lucknow', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
                                ['label' => 'Kushinagar', 'url' => '/company/projects?location=kushinagar', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
                                ['label' => 'Varanasi', 'url' => '/company/projects?location=varanasi', 'icon' => 'fas fa-map-pin', 'badge' => '1'],
                                ['label' => '── Projects ──', 'url' => '#', 'icon' => 'fas fa-building', 'disabled' => true],
                                ['label' => 'Suryoday Colony', 'url' => '/projects/suryoday-colony', 'icon' => 'fas fa-home'],
                                ['label' => 'Raghunath Nagri', 'url' => '/projects/raghunath-nagri', 'icon' => 'fas fa-building'],
                                ['label' => 'Braj Radha Nagri', 'url' => '/projects/braj-radha-nagri', 'icon' => 'fas fa-city'],
                                ['label' => 'Budh Bihar Colony', 'url' => '/projects/budh-bihar-colony', 'icon' => 'fas fa-map-marker-alt'],
                                ['label' => 'Awadhpuri', 'url' => '/projects/awadhpuri', 'icon' => 'fas fa-landmark'],
                            ]
                        ],
                        [
                            'label' => 'Buy',
                            'icon' => 'fas fa-shopping-cart',
                            'submenu' => [
                                ['label' => 'Residential', 'url' => '/properties?type=residential', 'icon' => 'fas fa-home'],
                                ['label' => 'Commercial', 'url' => '/properties?type=commercial', 'icon' => 'fas fa-building'],
                                ['label' => 'Plot/Land', 'url' => '/properties?type=plot', 'icon' => 'fas fa-vector-square'],
                                ['label' => 'House/Villa', 'url' => '/properties?type=house', 'icon' => 'fas fa-house-user'],
                                ['label' => 'Flat/Apartment', 'url' => '/properties?type=flat', 'icon' => 'fas fa-city'],
                            ]
                        ],
                        [
                            'label' => 'Rent',
                            'icon' => 'fas fa-key',
                            'submenu' => [
                                ['label' => 'House/Apartment', 'url' => '/properties?listing=rent&type=residential', 'icon' => 'fas fa-home'],
                                ['label' => 'Shop/Office', 'url' => '/properties?listing=rent&type=commercial', 'icon' => 'fas fa-store'],
                            ]
                        ],
                        [
                            'label' => 'About Us',
                            'icon' => 'fas fa-info-circle',
                            'submenu' => [
                                ['label' => 'About', 'url' => '/about', 'icon' => 'fas fa-info-circle'],
                                ['label' => 'Our Team', 'url' => '/team', 'icon' => 'fas fa-users'],
                                ['label' => 'Careers', 'url' => '/careers', 'icon' => 'fas fa-briefcase'],
                                ['label' => 'Testimonials', 'url' => '/testimonials', 'icon' => 'fas fa-comment-alt'],
                            ]
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
                            ]
                        ],
                        [
                            'label' => 'Resources',
                            'icon' => 'fas fa-folder-open',
                            'submenu' => [
                                ['label' => 'Blog', 'url' => '/blog', 'icon' => 'fas fa-blog'],
                                ['label' => 'News', 'url' => '/news', 'icon' => 'fas fa-newspaper'],
                                ['label' => 'Downloads', 'url' => '/downloads', 'icon' => 'fas fa-download'],
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
                                    $badge_html = $badge ? '<span class="badge bg-primary ms-2">' . $badge . '</span>' : '';
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
                    
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-link" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'My Account'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/properties">
                                    <i class="fas fa-building me-2"></i>My Properties
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/inquiries">
                                    <i class="fas fa-envelope me-2"></i>My Inquiries
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/profile">
                                    <i class="fas fa-user-cog me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/user/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
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
                    <li class="nav-item ms-2">
                        <a href="<?php echo BASE_URL; ?>/admin/login" class="btn btn-admin btn-sm">
                            <i class="fas fa-user-lock me-1"></i>
                            <span class="d-none d-lg-inline">Admin</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
/* ===== PREMIUM HEADER CSS ===== */
.premium-header {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
}

.premium-header .navbar {
    padding: 10px 0;
}

.premium-header .logo {
    height: 42px;
    border-radius: 10px;
    margin-right: 12px;
    transition: transform 0.3s ease;
}

.premium-header .navbar-brand:hover .logo {
    transform: scale(1.05);
}

.premium-header .brand-text {
    font-size: 1.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.premium-header .nav-link {
    color: #4a5568 !important;
    font-weight: 500;
    font-size: 0.9rem;
    padding: 8px 14px;
    border-radius: 8px;
    transition: all 0.25s ease;
}

.premium-header .nav-link i {
    color: #667eea;
    margin-right: 6px;
    font-size: 0.85rem;
}

.premium-header .nav-link:hover {
    color: #667eea !important;
    background: rgba(102, 126, 234, 0.08);
}

.premium-header .nav-link:hover i {
    color: #667eea;
}

.premium-header .nav-link.active {
    color: #667eea !important;
    background: rgba(102, 126, 234, 0.12);
    font-weight: 600;
}

.premium-header .highlight-link {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
    color: #fff !important;
    font-weight: 600;
}

.premium-header .highlight-link i {
    color: #fff !important;
}

.premium-header .highlight-link:hover {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%) !important;
    transform: translateY(-1px);
}

.premium-header .user-link {
    color: #667eea !important;
    font-weight: 600;
}

.premium-header .dropdown-menu {
    border: none;
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
    padding: 8px;
    min-width: 220px;
    margin-top: 8px;
}

.premium-header .dropdown-item {
    border-radius: 8px;
    padding: 10px 14px;
    color: #4a5568;
    font-weight: 500;
    transition: all 0.2s ease;
    margin-bottom: 2px;
}

.premium-header .dropdown-item i {
    color: #667eea;
    width: 20px;
}

.premium-header .dropdown-item:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    color: #667eea;
    padding-left: 18px;
}

.premium-header .dropdown-item:hover i {
    color: #764ba2;
}

.premium-header .dropdown-header {
    padding: 10px 14px 6px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #a0aec0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.premium-header .btn-call {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
    border: none;
    padding: 8px 16px;
}

.premium-header .btn-call:hover {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
    color: #fff;
}

.premium-header .btn-admin {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    border: none;
    padding: 8px 16px;
}

.premium-header .btn-admin:hover {
    background: linear-gradient(135deg, #764ba2 0%, #6b46c1 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: #fff;
}

.premium-header .navbar-toggler {
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 10px 12px;
    border-radius: 10px;
}

.premium-header .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.9)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.badge.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    font-size: 0.7rem;
    padding: 2px 6px;
    border-radius: 10px;
}

/* Mobile Responsive */
@media (max-width: 991px) {
    .premium-header .navbar-collapse {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        padding: 15px;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .premium-header .navbar-nav {
        flex-direction: column;
        width: 100%;
        gap: 4px;
    }
    
    .premium-header .nav-item {
        width: 100%;
    }
    
    .premium-header .nav-link {
        width: 100%;
        justify-content: flex-start;
        padding: 12px 16px;
        border-radius: 10px;
    }
    
    .premium-header .dropdown-menu {
        position: static;
        box-shadow: none;
        border: none;
        padding: 0 0 0 15px;
        background: #f7fafc;
        margin-top: 5px;
        border-radius: 10px;
    }
    
    .premium-header .nav-item .btn {
        width: 100%;
        margin: 8px 0 0;
        text-align: center;
        justify-content: center;
    }
    
    .premium-header .brand-text {
        font-size: 1.1rem;
    }
    
    .premium-header .logo {
        height: 36px;
    }
}

/* Dropdown animation */
@media (min-width: 992px) {
    .premium-header .dropdown-menu {
        animation: fadeInDown 0.2s ease;
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
}
</style>

<script>
// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.1)';
    } else {
        header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.06)';
    }
});
</script>
