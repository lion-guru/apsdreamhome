<header class="premium-header fixed-top">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <?php $brand = $site['brand_name'] ?? 'APS Dream Home'; $logo = $site['logo_url'] ?? '/assets/images/logo/apslogo.png'; ?>
                <img src="<?php echo BASE_URL . $logo; ?>" alt="<?php echo h($brand); ?>" class="logo me-2" style="height:32px;">
                <span class="brand-text"><?php echo h($brand); ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php $items = json_decode($site['nav_json'] ?? '[]', true) ?: []; foreach ($items as $it): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . rtrim('/' . ltrim($it['url'] ?? '#', '/'), ''); ?>"><?php echo h($it['label'] ?? ''); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
