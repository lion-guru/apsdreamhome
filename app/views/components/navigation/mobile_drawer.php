<?php
/**
 * Mobile Drawer Component
 *
 * Rendered by: header.php (visible only on mobile/tablet < xl)
 * Provides: Off-canvas side drawer with full navigation menu.
 *
 * Variables available:
 *   @var NavigationHelper $nav
 */
?>

<div class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
    <!-- Overlay -->
    <div class="mobile-drawer-overlay"
         onclick="toggleDrawer(null, 'close')"></div>

    <!-- Drawer Panel -->
    <div class="mobile-drawer-panel" id="mobileDrawerPanel">

        <!-- Drawer Header -->
        <div class="mobile-drawer-header d-flex align-items-center justify-content-between p-3"
             class="style-15495">

            <a class="navbar-brand d-flex align-items-center m-0 text-white"
               href="<?php echo BASE_URL; ?>">
                <?php $logo = $nav->getSetting('company_logo', '/assets/images/logo/apslogonew.jpg');
                       if ($logo && $logo[0] !== '/') $logo = '/' . $logo; ?>
                <img src="<?php echo BASE_URL . htmlspecialchars($logo ?? ''); ?>"
                     alt="<?php echo htmlspecialchars($nav->companyName()); ?>"
                     class="logo"
                     class="style-39414"
                     loading="eager">
                <span class="brand-text ms-2 fw-bold" class="style-6037">
                    <?php echo htmlspecialchars($nav->companyName()); ?>
                </span>
            </a>

            <!-- Close Button -->
            <button type="button"
                    class="btn-close btn-close-white"
                    id="mobileDrawerClose"
                    aria-label="Close navigation"
                    onclick="toggleDrawer(null, 'close')"></button>
        </div>

        <!-- Drawer Body -->
        <div class="mobile-drawer-body" class="style-93279">

            <!-- User Section -->
            <?php if ($nav->isLoggedIn()): ?>
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary bg-gradient rounded-circle" class="style-24506">
                                <?php
                                $name = $nav->userName();
                                echo strtoupper(substr($name, 0, 1));
                                ?>
                            </span>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0"><?php echo htmlspecialchars($nav->userName()); ?></h6>
                            <small class="text-muted"><?php echo __($nav->userRole()); ?></small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-3 border-bottom">
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>/login"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-sign-in-alt me-2"></i><?php echo __('login'); ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/register"
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-plus me-2"></i><?php echo __('register'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Navigation Tree -->
            <nav class="mobile-drawer-nav" class="style-94963">
                <?php
                /**
                 * Build a recursive accordion menu from a navigation array.
                 * Each item can have: label, url, icon, submenu, badge, disabled, highlight
                 */
                $renderAccordion = function(array $items, string $prefix = '') use (&$renderAccordion, $nav) {
                    echo '<ul class="mobile-nav-list list-unstyled mb-0">';
                    foreach ($items as $item):
                        $hasChildren = isset($item['submenu']);
                        $isDisabled  = isset($item['disabled']);
                        $itemKey     = $prefix . ($item['label'] ?? 'item');
                        $isActive    = !$hasChildren && $nav->isActive($item['url'] ?? '');
                        ?>
                        <li class="mobile-nav-item">
                            <?php if ($hasChildren): ?>
                                <?php
                                $isAccordion = $hasChildren && !$isDisabled;
                                $accordionId = 'accordion_' . md5($itemKey);
                                ?>
                                <?php if ($isAccordion): ?>
                                    <a href="#<?php echo $accordionId; ?>"
                                       class="mobile-nav-link d-flex align-items-center justify-content-between p-3 border-bottom"
                                       data-bs-toggle="collapse"
                                       aria-expanded="false"
                                       role="button">
                                        <span>
                                            <?php if (isset($item['icon'])): ?><i class="<?php echo $item['icon']; ?> me-2"></i><?php endif; ?>
                                            <?php echo __($item['label']); ?>
                                        </span>
                                        <i class="fas fa-chevron-down chevron-rotate"></i>
                                    </a>
                                    <div class="collapse" id="<?php echo $accordionId; ?>" data-bs-parent="#mobileDrawer">
                                        <?php echo $renderAccordion($item['submenu'], $itemKey . '_'); ?>
                                    </div>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL . $item['url']; ?>"
                                   class="mobile-nav-link d-flex align-items-center text-muted">
                                        <?php if (isset($item['icon'])): ?><i class="<?php echo $item['icon']; ?> me-2"></i><?php endif; ?>
                                        <?php echo __($item['label']); ?>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL . $item['url']; ?>"
                                   class="mobile-nav-link d-flex align-items-center p-3 border-bottom <?php echo $isActive ? 'active' : ''; ?> <?php echo isset($item['highlight']) && $item['highlight'] ? 'text-warning fw-bold' : ''; ?>">
                                    <?php if (isset($item['icon'])): ?><i class="<?php echo $item['icon']; ?> me-2"></i><?php endif; ?>
                                    <?php echo __($item['label']); ?>
                                    <?php if (isset($item['badge'])): ?>
                                        <span class="badge bg-primary ms-auto rounded-pill"><?php echo $item['badge']; ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php
                };

                // Render top-level items for the drawer.
                // We use desktop nav items, expanding submenus inline (not nested).
                // Then append the user menu if logged in.
                $drawerItems = $nav->getDesktopNavItems();
                echo $renderAccordion($drawerItems, '');
                ?>
            </nav>

            <!-- Quick Actions -->
            <div class="p-3 border-top">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/tools-hub"
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-flask me-2"></i><?php echo __('Tools Hub'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/contact"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-phone me-2"></i><?php echo __('contact_us'); ?>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
