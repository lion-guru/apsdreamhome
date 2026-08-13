<?php
/**
 * Desktop Navbar Component
 *
 * Rendered by: header.php
 * Provides: Desktop navigation bar (lg+ screens) with mega-menu dropdowns.
 *
 * Variables available (from header.php scope):
 *   @var NavigationHelper $nav
 *   @var Closure $sc
 */
?>

<nav class="navbar navbar-expand-xl align-items-center" style="padding: 0.5rem 0;">
    <div class="container d-flex align-items-center">

        <!-- Logo - Always on the left -->
        <a class="navbar-brand d-flex align-items-center me-0" href="<?php echo BASE_URL; ?>" style="flex: 0 0 auto;">
            <?php $brand = $nav->companyName(); ?>
            <?php $logo = $nav->getSetting('company_logo', '/assets/images/logo/apslogonew.jpg'); ?>
            <img src="<?php echo BASE_URL . htmlspecialchars($logo); ?>"
                 alt="<?php echo htmlspecialchars($brand); ?>"
                 class="logo"
                 style="height: 32px; width: auto; max-width: 110px;"
                 loading="eager"
                 fetchpriority="high">
            <?php if (isset($brand)): ?>
                <span class="brand-text d-none d-md-inline ms-2 fw-bold"
                      style="color: #1a1a1a; font-size: 1.1rem;">
                    <?php echo htmlspecialchars($brand); ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- Mobile Toggle (visible only on mobile) -->
        <button type="button"
                class="navbar-toggler d-xl-none ms-auto"
                aria-label="Toggle navigation"
                aria-expanded="false"
                aria-controls="navbarNav"
                onclick="toggleDrawer(event)">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation & Actions -->
        <div class="collapse navbar-collapse d-none d-xl-block" id="navbarNavDesktop">
            <ul class="navbar-nav align-items-center">

                <?php foreach ($nav->getDesktopNavItems() as $index => $item): ?>
                    <?php
                    $hasChildren = isset($item['submenu']);
                    $isActive    = $hasChildren
                        ? false
                        : $nav->isActive($item['url'] ?? '');
                    $alignRight  = $item['align_right'] ?? false;
                    ?>

                    <?php if ($alignRight): ?>
                        <li class="nav-item dropdown ms-auto">
                    <?php else: ?>
                        <li class="nav-item dropdown">
                    <?php endif; ?>

                        <?php if ($hasChildren): ?>
                            <a class="nav-link dropdown-toggle <?php echo $isActive ? 'active' : ''; ?>"
                               href="<?php echo $item['url'] ?? '#'; ?>"
                               id="navDrop<?php echo $index; ?>"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false"
                               aria-haspopup="true">
                                <i class="<?php echo $item['icon']; ?> me-1"></i> <?php echo __($item['label']); ?>
                            </a>

                            <!-- Dropdown menu: right-aligned on align_right items -->
                            <ul class="dropdown-menu dropdown-menu-end <?php echo $alignRight ? 'dropdown-menu-end' : 'dropdown-menu-start'; ?>"
                                aria-labelledby="navDrop<?php echo $index; ?>">

                                <!-- Mega-menu for Properties -->
                                <?php if ($item['label'] === 'Properties' || $item['label'] === 'Nav Properties'): ?>
                                    <li class="px-3 py-2">
                                        <div class="row g-0">
                                            <div class="col-md-4">
                                                <h6 class="text-uppercase small text-muted mb-2"><?php _e('Browse By'); ?></h6>
                                                <?php
                                                $subItems = isset($item['submenu']) ? $item['submenu'] : [];
                                                foreach ($subItems as $sub):
                                                    if (isset($sub['disabled'])) continue;
                                                ?>
                                                    <a class="dropdown-item" href="<?php echo $sub['url']; ?>">
                                                        <i class="<?php echo $sub['icon']; ?> me-2"></i><?php echo __($sub['label']); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-uppercase small text-muted mb-2"><?php _e('Featured'); ?></h6>
                                                <a class="dropdown-item" href="/featured-properties"><i class="fas fa-star me-2"></i>Featured</a>
                                                <a class="dropdown-item" href="/properties?status=new"><i class="fas fa-sparkles me-2"></i>New Launch</a>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="text-uppercase small text-muted mb-2"><?php _e('Price Range'); ?></h6>
                                                <a class="dropdown-item" href="/properties?price=0-50"><i class="fas fa-indian-rupee-sign me-2"></i>0 - 50L</a>
                                                <a class="dropdown-item" href="/properties?price=50-100"><i class="fas fa-indian-rupee-sign me-2"></i>50L - 1Cr</a>
                                                <a class="dropdown-item" href="/properties?price=100+"><i class="fas fa-indian-rupee-sign me-2"></i>1Cr+</a>
                                            </div>
                                        </div>
                                    </li>

                                <!-- Mega-menu for Plots -->
                                <?php elseif ($item['label'] === 'Plots' || $item['label'] === 'Nav Plots'): ?>
                                    <li class="px-3 py-2">
                                        <div class="row g-0">
                                            <div class="col-md-6">
                                                <h6 class="text-uppercase small text-muted mb-2"><?php _e('Browse'); ?></h6>
                                                <?php
                                                $plotsMenu = $nav->getPlotsSubmenu();
                                                foreach ($plotsMenu as $sub):
                                                    if (isset($sub['disabled'])) {
                                                        continue;
                                                    }
                                                    $badge = $sub['badge'] ?? null;
                                                ?>
                                                    <a class="dropdown-item d-flex justify-content-between" href="<?php echo $sub['url']; ?>">
                                                        <span><i class="<?php echo $sub['icon']; ?> me-2"></i><?php echo __($sub['label']); ?></span>
                                                        <?php if ($badge): ?>
                                                            <span class="badge bg-primary rounded-pill"><?php echo $badge; ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-uppercase small text-muted mb-2"><?php _e('Popular Colonies'); ?></h6>
                                                <?php foreach ($nav->getAllProjects() as $proj): ?>
                                                    <?php $slug = $proj['slug'] ?: preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($proj['name'])); ?>
                                                    <a class="dropdown-item" href="/colony/<?php echo $slug; ?>/plots">
                                                        <i class="fas fa-vector-square me-2"></i><?php echo htmlspecialchars($proj['name']); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </li>

                                <!-- Mega-menu for Projects -->
                                <?php elseif ($item['label'] === 'Projects' || $item['label'] === 'Nav Projects'): ?>
                                    <li class="px-3 py-2">
                                        <div class="row g-0">
                                            <div class="col-12">
                                                <h6 class="text-uppercase small text-muted mb-2"><?php _e('By Location'); ?></h6>
                                                <?php foreach ($nav->getProjectLocations() as $loc): ?>
                                                    <a class="dropdown-item d-flex justify-content-between"
                                                       href="/projects?location=<?php echo urlencode(strtolower($loc['name'])); ?>">
                                                        <span><i class="fas fa-map-pin me-2"></i><?php echo htmlspecialchars($loc['name']); ?></span>
                                                        <span class="badge bg-secondary rounded-pill"><?php echo $loc['count']; ?></span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </li>

                                <!-- Standard submenus -->
                                <?php else: ?>
                                    <?php foreach ($item['submenu'] as $sub): ?>
                                        <?php if (isset($sub['disabled'])): ?>
                                            <li><span class="dropdown-item-text text-muted"><?php echo __($sub['label']); ?></span></li>
                                        <?php else: ?>
                                            <a class="dropdown-item" href="<?php echo $sub['url']; ?>">
                                                <?php if (isset($sub['icon'])): ?><i class="<?php echo $sub['icon']; ?> me-2"></i><?php endif; ?>
                                                <?php echo __($sub['label']); ?>
                                                <?php if (isset($sub['badge'])): ?>
                                                    <span class="badge bg-primary ms-2 rounded-pill"><?php echo $sub['badge']; ?></span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </ul>
                        <?php else: ?>
                            <a class="nav-link <?php echo $isActive ? 'active' : ''; ?> <?php echo ($item['highlight'] ?? false) ? 'text-primary fw-bold' : ''; ?>"
                               href="<?php echo $item['url'] ?? '#'; ?>"
                               <?php echo ($item['highlight'] ?? false) ? 'style="color:#dc3545 !important;"' : ''; ?>>
                                <?php if (isset($item['icon'])): ?><i class="<?php echo $item['icon']; ?> me-1"></i><?php endif; ?>
                                <?php echo __($item['label']); ?>
                            </a>
                        <?php endif; ?>

                    </li>
                <?php endforeach; ?>

                <!-- Auth Button / User Menu -->
                <li class="nav-item dropdown">
                    <?php if ($nav->isLoggedIn()): ?>
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           id="userDropdownDesktop"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-title bg-primary bg-gradient rounded-circle" style="width:32px;height:32px;font-size:14px;">
                                        <?php
                                        $name = $nav->userName();
                                        echo strtoupper(substr($name, 0, 1));
                                        ?>
                                    </span>
                                </div>
                                <span class="d-none d-md-inline ms-2"><?php echo htmlspecialchars($nav->userName()); ?></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownDesktop">
                            <?php foreach ($nav->getUserMenuItems() as $link): ?>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center"
                                       href="<?php echo $link['url']; ?>">
                                        <span>
                                            <i class="<?php echo $link['icon']; ?> me-2"></i><?php echo __($link['label']); ?>
                                        </span>
                                        <?php echo isset($link['highlight']) && $link['highlight'] ? '<span class="badge bg-warning text-dark ms-2">!</span>' : ''; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $nav->logoutUrl(); ?>">
                                    <i class="fas fa-right-from-bracket me-2"></i><?php echo __('logout'); ?>
                                </a>
                            </li>
                        </ul>
                    <?php else: ?>
                        <a class="nav-link btn btn-primary btn-sm text-white ms-3"
                           href="<?php echo BASE_URL; ?>/login"
                           style="border-radius: 6px;">
                            <i class="fas fa-sign-in-alt me-1"></i> <?php echo __('login'); ?> / <?php echo __('register'); ?>
                        </a>
                    <?php endif; ?>
                </li>

                <!-- Quick Action: Post Property -->
                <li class="nav-item d-none d-xl-inline-block">
                    <a class="nav-link btn btn-warning btn-sm text-dark ms-2"
                       href="<?php echo BASE_URL; ?>/list-property"
                       style="border-radius: 6px; font-weight: 600;">
                        <i class="fas fa-plus me-1"></i> <?php echo __('nav_post_property'); ?>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>
