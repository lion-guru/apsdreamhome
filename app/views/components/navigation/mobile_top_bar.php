<?php
/**
 * Mobile Top Bar Component
 *
 * Rendered by: header.php (visible only on mobile/tablet < xl)
 * Provides: Logo + hamburger toggle + user quick-link on small screens.
 *
 * Variables available:
 *   @var NavigationHelper $nav
 */
?>

<div class="d-xl-none mobile-top-bar">
    <div class="container-fluid px-3 py-2 d-flex align-items-center justify-content-between"
         style="background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); height: 62px;">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center m-0" href="<?php echo BASE_URL; ?>" style="flex: 0 0 auto;">
            <?php $brand = $nav->companyName(); ?>
            <?php $logo = $nav->getSetting('company_logo', '/assets/images/logo/apslogonew.jpg');
                   if ($logo && $logo[0] !== '/') $logo = '/' . $logo; ?>
            <img src="<?php echo BASE_URL . htmlspecialchars($logo); ?>"
                 alt="<?php echo htmlspecialchars($brand); ?>"
                 class="logo"
                 style="height: 26px; width: auto; max-width: 90px;"
                 loading="eager"
                 fetchpriority="high">
            <span class="brand-text d-inline ms-1 fw-bold"
                  style="color: #1a1a1a; font-size: 0.9rem;">
                <?php echo htmlspecialchars($brand); ?>
            </span>
        </a>

        <!-- Hamburger toggle -->
        <button type="button"
                class="navbar-toggler d-xl-none"
                id="mobileMenuToggle"
                aria-label="Toggle navigation"
                aria-expanded="false"
                aria-controls="mobileDrawer"
                onclick="toggleDrawer(event)">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</div>
