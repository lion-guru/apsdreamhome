<?php
// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
?>
<header class="premium-header fixed-top">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <img src="https://via.placeholder.com/40x40/2c3e50/ffffff?text=APS"
                    alt="APS Dream Home" class="logo me-2" style="height:40px; border-radius:8px;">
                <div>
                    <div class="brand-text fw-bold text-primary">APS Dream Home</div>
                    <small class="text-muted d-block lh-1">Premium Real Estate</small>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php
                    // Enhanced navigation with active state
                    $current_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
                    $base_path = (string) parse_url(BASE_URL, PHP_URL_PATH);
                    $current_path = str_replace($base_path, '', $current_path);
                    $current_path = $current_path ?: '/';

                    $nav_items = [
                        ['label' => 'Home', 'url' => '/', 'icon' => 'fas fa-home'],
                        ['label' => 'Properties', 'url' => '/properties', 'icon' => 'fas fa-building'],
                        [
                            'label' => 'About Us', 'icon' => 'fas fa-info-circle', 'submenu' => [
                                ['label' => 'About', 'url' => '/about', 'icon' => 'fas fa-info-circle'],
                                ['label' => 'Our Team', 'url' => '/team', 'icon' => 'fas fa-users'],
                                ['label' => 'Careers', 'url' => '/careers', 'icon' => 'fas fa-briefcase'],
                                ['label' => 'Testimonials', 'url' => '/testimonials', 'icon' => 'fas fa-comment-alt'],
                            ]
                        ],
                        [
                            'label' => 'Services', 'icon' => 'fas fa-concierge-bell', 'submenu' => [
                                ['label' => 'All Services', 'url' => '/services', 'icon' => 'fas fa-concierge-bell'],
                                ['label' => 'Financial Services', 'url' => '/financial-services', 'icon' => 'fas fa-hand-holding-usd'],
                                ['label' => 'Interior Design', 'url' => '/interior-design', 'icon' => 'fas fa-couch'],
                                ['label' => 'Resell Property', 'url' => '/resell', 'icon' => 'fas fa-handshake'],
                            ]
                        ],
                        [
                            'label' => 'Resources', 'icon' => 'fas fa-folder-open', 'submenu' => [
                                ['label' => 'Blog', 'url' => '/blog', 'icon' => 'fas fa-blog'],
                                ['label' => 'News', 'url' => '/news', 'icon' => 'fas fa-newspaper'],
                                ['label' => 'Downloads', 'url' => '/downloads', 'icon' => 'fas fa-download'],
                                ['label' => 'FAQs', 'url' => '/faqs', 'icon' => 'fas fa-question-circle'],
                            ]
                        ],
                        ['label' => 'Contact', 'url' => '/contact', 'icon' => 'fas fa-phone']
                    ];

                    foreach ($nav_items as $item) {
                        if (isset($item['submenu'])) {
                            $is_active = array_reduce($item['submenu'], function ($carry, $sub_item) use ($current_path) {
                                return $carry || $current_path === $sub_item['url'];
                            }, false);
                            $active_class = $is_active ? 'active' : '';
                            echo '<li class="nav-item dropdown">';
                            echo '<a class="nav-link dropdown-toggle ' . $active_class . '" href="#" role="button" data-bs-toggle="dropdown">';
                            echo '<i class="' . $item['icon'] . ' me-1"></i>' . htmlspecialchars($item['label']);
                            echo '</a>';
                            echo '<ul class="dropdown-menu">';
                            foreach ($item['submenu'] as $sub_item) {
                                $active_class = ($current_path === $sub_item['url']) ? 'active' : '';
                                echo '<li><a class="dropdown-item ' . $active_class . '" href="' . BASE_URL . $sub_item['url'] . '"><i class="' . $sub_item['icon'] . ' me-2"></i>' . htmlspecialchars($sub_item['label']) . '</a></li>';
                            }
                            echo '</ul>';
                            echo '</li>';
                        } else {
                            $active_class = ($current_path === $item['url']) ? 'active' : '';
                            echo '<li class="nav-item">';
                            echo '<a class="nav-link ' . $active_class . '" href="' . BASE_URL . $item['url'] . '">';
                            echo '<i class="' . $item['icon'] . ' me-1"></i>' . htmlspecialchars($item['label']);
                            echo '</a>';
                            echo '</li>';
                        }
                    }
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="registerDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-plus me-1"></i>
                            Register
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
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login
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
                    <li class="nav-item ms-3">
                        <a href="<?php echo BASE_URL; ?>/admin/login" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-lock me-1"></i>
                            Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>