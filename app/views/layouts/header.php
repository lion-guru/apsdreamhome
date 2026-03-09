<header class="premium-header fixed-top">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <?php
                // Use hardcoded values for now to avoid dependency issues
                $brand = 'APS Dream Home';
                $logo = '/assets/images/logo/apslogo.png';
                ?>
                <img src="<?php echo BASE_URL . $logo; ?>" alt="<?php echo htmlspecialchars($brand); ?>" class="logo me-2" style="height:32px;">
                <span class="brand-text"><?php echo htmlspecialchars($brand); ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php
                    // Hardcoded navigation for now
                    $nav_items = [
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'Properties', 'url' => '/properties'],
                        ['label' => 'About', 'url' => '/about'],
                        ['label' => 'Contact', 'url' => '/contact']
                    ];
                    foreach ($nav_items as $item): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL . rtrim('/' . ltrim($item['url'] ?? '#', '/'), ''); ?>">
                                <?php echo htmlspecialchars($item['label'] ?? ''); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>