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
                    $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
                    $base_path = parse_url(BASE_URL, PHP_URL_PATH);
                    $current_path = str_replace($base_path, '', $current_path);
                    $current_path = $current_path ?: '/';

                    $nav_items = [
                        ['label' => 'Home', 'url' => '/', 'icon' => 'fas fa-home'],
                        ['label' => 'Properties', 'url' => '/properties', 'icon' => 'fas fa-building'],
                        ['label' => 'About', 'url' => '/about', 'icon' => 'fas fa-info-circle'],
                        ['label' => 'Contact', 'url' => '/contact', 'icon' => 'fas fa-phone']
                    ];
                    foreach ($nav_items as $item):
                        $active_class = ($current_path === $item['url']) ? 'active' : '';
                    ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_class; ?>" href="<?php echo BASE_URL . $item['url']; ?>">
                                <i class="<?php echo $item['icon']; ?> me-1"></i>
                                <?php echo htmlspecialchars($item['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
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