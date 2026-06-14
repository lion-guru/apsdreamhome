<?php
// Helper function for HTML escaping if not defined
if (!function_exists('h')) {
    function h($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Load site settings if not already loaded
if (!isset($GLOBALS['_site_settings_cache'])) {
    $GLOBALS['_site_settings_cache'] = [];
    try {
        $scPdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]);
        $scRows = $scPdo->query("SELECT content_key, content_value FROM site_content WHERE section = 'settings' AND is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
        $GLOBALS['_site_settings_cache'] = $scRows;
    } catch (\Exception $e) {
    }
}
$sc = function ($key, $default = '') {
    return $GLOBALS['_site_settings_cache'][$key] ?? $default;
};

// Check authentication
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['associate_id']) || isset($_SESSION['agent_id']) || isset($_SESSION['employee_id']) || isset($_SESSION['admin_id']);
?>
<header class="premium-header fixed-top" id="mainHeader">
    <nav class="navbar navbar-expand-xl align-items-center" style="padding: 0.5rem 0;">
        <div class="container d-flex align-items-center">
            <!-- Logo - Always on the left -->
            <a class="navbar-brand d-flex align-items-center me-0" href="<?php echo BASE_URL; ?>" style="flex: 0 0 auto;">
                <?php $brand = $sc('company_name', 'APS Dream Home');
                $logo = $sc('company_logo', '/assets/images/logo/apslogonew.jpg'); ?>
                <img src="<?php echo BASE_URL . h($logo); ?>" alt="<?php echo h($brand); ?>" class="logo" style="height: 32px; width: auto; max-width: 110px;" loading="eager" fetchpriority="high">
                <?php if (isset($brand)): ?>
                    <span class="brand-text d-none d-md-inline ms-2 fw-bold" style="color: #1a1a1a; font-size: 1.1rem;"><?php echo h($brand); ?></span>
                <?php endif; ?>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation & Actions -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <?php $items = json_decode($site['nav_json'] ?? '[]', true) ?: [];
                    foreach ($items as $it): ?>
                        <?php if (($it['active'] ?? true)): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . rtrim('/' . ltrim($it['url'] ?? '#', '/'), ''); ?>"><?php echo h($it['label'] ?? ''); ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <!-- Action Buttons (Desktop) -->
                <ul class="navbar-nav ms-auto align-items-center d-none d-xl-flex">
                    <!-- Phone Button -->
                    <li class="nav-item ms-2">
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+91 92771 21112')); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-phone me-1"></i>
                            <span><?php echo htmlspecialchars($sc('contact_phone', '+91 92771 21112')); ?></span>
                        </a>
                    </li>

                    <!-- Compare Button -->
                    <li class="nav-item ms-2">
                        <a href="<?php echo BASE_URL; ?>/compare" class="btn btn-outline-info btn-sm position-relative">
                            <i class="fas fa-balance-scale"></i> Compare
                            <span id="compareBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;font-size:10px;">0</span>
                        </a>
                    </li>

                    <!-- Admin Login Button (when not logged in) -->
                    <?php if (!$isLoggedIn): ?>
                        <li class="nav-item ms-2">
                            <a href="<?php echo BASE_URL; ?>/admin/login" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-lock me-1"></i>
                                Admin Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<style>
    /* Fix navbar alignment - logo strictly on left */
    .premium-header .navbar {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap;
        padding: 0.5rem 0 !important;
    }

    .premium-header .container {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap;
        width: 100%;
        position: relative;
    }

    .premium-header .navbar-brand {
        flex: 0 0 auto !important;
        margin-right: auto !important;
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .premium-header .navbar-toggler {
        order: 2;
        flex: 0 0 auto;
    }

    .premium-header .navbar-collapse {
        flex: 0 1 auto;
        order: 3;
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

        .premium-header .navbar-brand span {
            font-size: 0.9rem !important;
        }

        .premium-header .navbar-collapse {
            width: 100%;
        }

        .premium-header .navbar-nav {
            width: 100%;
            flex-direction: column;
        }
    }

    @media (max-width: 1199px) {
        .premium-header .navbar-nav.d-none.d-xl-flex {
            display: none !important;
        }
    }
</style>