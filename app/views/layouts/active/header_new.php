<?php
// Helper function for HTML escaping if not defined
if (!function_exists('h')) {
    function h($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<header class="premium-header fixed-top" id="mainHeader">
    <nav class="navbar navbar-expand-xl align-items-center" style="padding: 0.5rem 0;">
        <div class="container d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center me-auto" href="<?php echo BASE_URL; ?>">
                <?php $brand = $site['brand_name'] ?? 'APS Dream Home';
                $logo = $site['logo_url'] ?? '/assets/images/logo/apslogonew.jpg'; ?>
                <img src="<?php echo BASE_URL . h($logo); ?>" alt="<?php echo h($brand); ?>" class="logo" style="height: 32px; width: auto; max-width: 110px;" loading="eager" fetchpriority="high">
                <?php if (isset($brand)): ?>
                    <span class="brand-text d-none d-md-inline ms-2"><?php echo h($brand); ?></span>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse flex-grow-0" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <?php $items = json_decode($site['nav_json'] ?? '[]', true) ?: [];
                    foreach ($items as $it): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . rtrim('/' . ltrim($it['url'] ?? '#', '/'), ''); ?>"><?php echo h($it['label'] ?? ''); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<style>
    /* Fix navbar alignment for premium header */
    .premium-header .navbar {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap;
    }

    .premium-header .container {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap;
        width: 100%;
    }

    .premium-header .navbar-brand {
        flex: 0 0 auto;
        margin: 0 !important;
        padding: 0 !important;
    }

    .premium-header .navbar-collapse {
        flex: 0 0 auto;
    }

    .premium-header .navbar-nav {
        flex: 0 0 auto;
    }

    /* Main content padding */
    #main-content {
        padding-top: 80px !important;
    }

    @media (max-width: 768px) {
        #main-content {
            padding-top: 70px !important;
        }

        .premium-header .navbar-brand img {
            height: 28px !important;
            max-width: 100px !important;
        }
    }
</style>