<?php
if (!function_exists('navUrl')) {
    function navUrl($url) {
        $url = $url ?? '#';
        if ($url === '#' || $url === '' || $url === null) return '#';
        return BASE_URL . rtrim('/' . ltrim($url, '/'), '');
    }
}
?>

<style>
    @media (min-width: 1200px) {
        /* Keep everything super compact so it fits even on 1600px with many items */
        #navbarNavDesktop .nav-link {
            padding-right: 0.35rem !important;
            padding-left: 0.35rem !important;
            font-size: 0.85rem !important;
            white-space: nowrap;
        }
        #navbarNavDesktop .btn-sm {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.8rem !important;
            white-space: nowrap;
        }
        .navbar-brand img.logo {
            max-width: 140px !important;
            height: auto;
        }
        .nav-item.ms-1 { margin-left: 0.15rem !important; }
        .nav-item.ms-2 { margin-left: 0.25rem !important; }
        .nav-item.ms-3 { margin-left: 0.4rem !important; }
    }
</style>
<nav class="navbar navbar-expand-xl align-items-center">
    <div class="container-fluid px-3 px-xl-5 d-flex align-items-center">

        <!-- Logo - Always on the left -->
        <a class="navbar-brand d-flex align-items-center me-0" href="<?php echo BASE_URL; ?>">
            <?php $logo = $nav->getSetting('company_logo', '/assets/images/logo/apslogonew.jpg');
                   if ($logo && $logo[0] !== '/') $logo = '/' . $logo; ?>
            <img src="<?php echo navUrl($logo); ?>"
                 alt="APS Dream Home"
                 class="logo"
                 loading="eager"
                 fetchpriority="high">
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
                             href="#"
                                id="navDrop<?= (int)$index ?>"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false"
                               aria-haspopup="true">
                                <i class="<?php echo $item['icon']; ?> me-1"></i> <?php echo __($item['label']); ?>
                            </a>

                            <!-- Dropdown menu: right-aligned on align_right items -->
                            <ul class="dropdown-menu dropdown-menu-end <?php echo $alignRight ? 'dropdown-menu-end' : 'dropdown-menu-start'; ?>"
                                aria-labelledby="navDrop<?= (int)$index ?>">

                                <!-- Mega-menu for Properties -->
                                <?php if ($item['label'] === 'Properties' || $item['label'] === 'Nav Properties'): ?>
                                    <li class="mega-menu px-0 py-0">
                                        <div class="d-flex gap-0">
                                            <div class="mega-col flex-grow-1">
                                                <h6><?php echo __('Browse By Type'); ?></h6>
                                                <?php
                                                $subItems = isset($item['submenu']) ? $item['submenu'] : [];
                                                foreach ($subItems as $sub):
                                                    if (isset($sub['disabled'])) continue;
                                                    $iconColor = match(true) {
                                                        str_contains($sub['icon'] ?? '', 'house') => 'mega-icon-teal',
                                                        str_contains($sub['icon'] ?? '', 'building') => 'mega-icon-blue',
                                                        str_contains($sub['icon'] ?? '', 'city') => 'mega-icon-purple',
                                                        default => 'mega-icon-teal'
                                                    };
                                                ?>
                                                    <a class="mega-item" href="<?php echo navUrl($sub['url']); ?>">
                                                        <i class="<?php echo e($sub['icon']); ?> <?php echo $iconColor; ?>"></i>
                                                        <span><?php echo __($sub['label']); ?></span>
                                                    </a>
                                                 <?php endforeach; ?>
                                             </div>
                                             <div class="mega-col flex-grow-1">
                                                 <h6><?php echo __('Featured'); ?></h6>
                                                 <a class="mega-item" href="<?php echo BASE_URL; ?>/featured-properties">
                                                     <i class="fas fa-star mega-icon-amber"></i>
                                                     <span><?php echo __('Featured'); ?></span>
                                                 </a>
                                                    <a class="mega-item" href="<?php echo BASE_URL; ?>/properties?status=new">
                                                        <i class="fas fa-sparkles mega-icon-teal"></i>
                                                        <span><?php echo __('New Launch'); ?></span>
                                                    </a>
                                                    <a class="mega-item" href="<?php echo BASE_URL; ?>/properties?status=verified">
                                                        <i class="fas fa-shield-check mega-icon-blue"></i>
                                                        <span><?php echo __('Verified'); ?></span>
                                                    </a>

                                                    <h6 class="mt-2"><?php echo __('Price Range'); ?></h6>
                                                    <a class="mega-item" href="<?php echo BASE_URL; ?>/properties?price=0-50">
                                                        <i class="fas fa-indian-rupee-sign mega-icon-teal"></i>
                                                        <span>0 - 50L</span>
                                                    </a>
                                                    <a class="mega-item" href="<?php echo BASE_URL; ?>/properties?price=50-100">
                                                        <i class="fas fa-indian-rupee-sign mega-icon-blue"></i>
                                                        <span>50L - 1Cr</span>
                                                    </a>
                                                    <a class="mega-item" href="<?php echo BASE_URL; ?>/properties?price=100+">
                                                        <i class="fas fa-indian-rupee-sign mega-icon-purple"></i>
                                                        <span>1Cr+</span>
                                                    </a>
                                            </div>
                                        </div>
                                        <div class="mega-featured">
                                            <h6><i class="fas fa-lightbulb me-1"></i> <?php echo __('Quick Tip'); ?></h6>
                                            <p><?php echo __('Use filters to narrow down by location, budget, and amenities. Save your search for daily alerts.'); ?></p>
                                        </div>
                                    </li>

                                <!-- Mega-menu for Plots -->
                                <?php elseif ($item['label'] === 'Plots' || $item['label'] === 'Nav Plots'): ?>
                                    <li class="mega-menu px-0 py-0">
                                        <div class="d-flex gap-0">
                                            <div class="mega-col flex-grow-1">
                                                <h6><?php echo __('Browse'); ?></h6>
                                                <?php
                                                $plotsMenu = $nav->getPlotsSubmenu();
                                                foreach ($plotsMenu as $sub):
                                                    if (isset($sub['disabled'])) continue;
                                                    $badge = $sub['badge'] ?? null;
                                                    $iconColor = match(true) {
                                                        str_contains($sub['icon'] ?? '', 'map') => 'mega-icon-teal',
                                                        str_contains($sub['icon'] ?? '', 'grid') => 'mega-icon-blue',
                                                        str_contains($sub['icon'] ?? '', 'filter') => 'mega-icon-purple',
                                                        default => 'mega-icon-teal'
                                                    };
                                                ?>
                                                     <a class="mega-item" href="<?= e(navUrl($sub['url'])) ?>">
                                                         <i class="<?= e($sub['icon']) ?> <?= e($iconColor) ?>"></i>
                                                         <span><?= __($sub['label']) ?></span>
                                                         <?php if ($badge): ?>
                                                             <span class="mega-badge"><?= e($badge) ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="mega-col flex-grow-1">
                                                <h6><?php echo __('Popular Colonies'); ?></h6>
                                                <?php
                                                $projects = $nav->getAllProjects();
                                                $projIcons = ['mega-icon-teal', 'mega-icon-blue', 'mega-icon-amber', 'mega-icon-purple', 'mega-icon-teal'];
                                                foreach ($projects as $idx => $proj):
                                                    $slug = $proj['slug'] ?: preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($proj['name']));
                                                    $iconClass = $projIcons[$idx % count($projIcons)];
                                                ?>
                                                    <a class="mega-item" href="<?= BASE_URL ?>/colony/<?= e($slug) ?>/plots">
                                                        <i class="fas fa-vector-square <?= e($iconClass) ?>"></i>
                                                        <span><?= htmlspecialchars($proj['name'] ?? '') ?></span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </li>

                                <!-- Mega-menu for Projects -->
                                <?php elseif ($item['label'] === 'Projects' || $item['label'] === 'Nav Projects'): ?>
                                    <li class="mega-menu px-0 py-0">
                                        <div class="d-flex gap-0">
                                            <div class="mega-col flex-grow-1">
                                                <h6><?php echo __('By Location'); ?></h6>
                                                <?php
                                                $locIcons = ['mega-icon-teal', 'mega-icon-blue', 'mega-icon-purple', 'mega-icon-amber'];
                                                foreach ($nav->getProjectLocations() as $idx => $loc):
                                                    $iconClass = $locIcons[(int)$idx % count($locIcons)];
                                                ?>
                                                     <a class="mega-item"
                                                       href="<?= BASE_URL ?>/projects?location=<?= e(urlencode(strtolower($loc['name']))) ?>">
                                                         <i class="fas fa-map-pin <?= e($iconClass) ?>"></i>
                                                         <span><?= htmlspecialchars($loc['name'] ?? '') ?></span>
                                                         <span class="mega-badge"><?= e($loc['count']) ?></span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="mega-col flex-grow-1">
                                                <h6><?php echo __('Quick Links'); ?></h6>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>/projects">
                                                    <i class="fas fa-th-large mega-icon-teal"></i>
                                                    <span><?php echo __('All Projects'); ?></span>
                                                </a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>/colony-pipeline">
                                                    <i class="fas fa-diagram-project mega-icon-blue"></i>
                                                    <span><?php echo __('Upcoming'); ?></span>
                                                </a>
                                                <a class="mega-item" href="<?php echo BASE_URL; ?>/properties?status=new">
                                                    <i class="fas fa-rocket mega-icon-amber"></i>
                                                    <span><?php echo __('New Launch'); ?></span>
                                                </a>
                                            </div>
                                        </div>
                                    </li>

                                <!-- Standard submenus -->
                                <?php else: ?>
                                    <?php foreach ($item['submenu'] as $sub): ?>
                                        <?php if (isset($sub['disabled'])): ?>
                                            <li><span class="dropdown-item-text text-muted"><?php echo __($sub['label']); ?></span></li>
                                        <?php else: ?>
                                            <a class="dropdown-item" href="<?= e(navUrl($sub['url'])) ?>">
                                                <?php if (isset($sub['icon'])): ?><i class="<?= e($sub['icon']) ?> me-2"></i><?php endif; ?>
                                                <?php echo __($sub['label']); ?>
                                                <?php if (isset($sub['badge'])): ?>
                                                    <span class="badge bg-primary ms-2 rounded-pill"><?php echo e($sub['badge']); ?></span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </ul>
                        <?php else: ?>
                            <a class="nav-link <?php echo $isActive ? 'active' : ''; ?> <?php echo ($item['highlight'] ?? false) ? 'text-primary fw-bold' : ''; ?>"
                                href="<?php echo navUrl($item['url'] ?? '#'); ?>"
                               <?php echo ($item['highlight'] ?? false) ? 'class="style-66454"' : ''; ?>>
                                <?php if (isset($item['icon'])): ?><i class="<?php echo e($item['icon']); ?> me-1"></i><?php endif; ?>
                                <?php echo __($item['label']); ?>
                            </a>
                        <?php endif; ?>

                    </li>
                <?php endforeach; ?>

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
                                    <span class="avatar-title bg-primary bg-gradient rounded-circle" class="style-43341">
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
                                       href="<?php echo navUrl($link['url'] ?? '#'); ?>">
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
                           class="style-60873">
                            <i class="fas fa-sign-in-alt me-1"></i> <?php echo __('login'); ?> / <?php echo __('register'); ?>
                        </a>
                    <?php endif; ?>
                </li>

                <!-- Language Switcher -->
                <li class="nav-item dropdown ms-1">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" title="<?= __('language') ?>" aria-label="<?= __('language') ?>">
                        <i class="fas fa-globe" aria-hidden="true"></i>
                        <span class="d-none"><?= ($GLOBALS['app_lang'] ?? 'en') === 'hi' ? 'हिन्दी' : 'English' ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/language/set/en"><span class="me-2">🇬🇧</span> English</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/language/set/hi"><span class="me-2">🇮🇳</span> Hindi</a></li>
                    </ul>
                </li>

                <!-- Quick Action: Call -->
                <li class="nav-item ms-1 btn-call">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+91 92771 21112')) ?>"
                       class="btn btn-call btn-sm px-2" aria-label="Call <?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        <span class="d-none ms-1"><?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?></span>
                    </a>
                </li>

                <!-- Quick Action: Compare -->
                <li class="nav-item ms-1 btn-compare">
                    <a href="<?php echo BASE_URL; ?>/compare"
                       class="btn btn-outline-info btn-sm px-2 position-relative" aria-label="<?= __('compare') ?>">
                        <i class="fas fa-balance-scale" aria-hidden="true"></i> 
                        <span class="d-none ms-1"><?= __('compare') ?></span>
                        <span id="compareBadge"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              class="style-62224" aria-hidden="true">0</span>
                    </a>
                </li>

                <!-- Dark Mode Toggle -->
                <li class="nav-item ms-2">
                    <button class="btn btn-sm btn-outline-secondary" id="darkModeToggle" onclick="toggleDarkMode()" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                        <i class="fas fa-moon" id="darkModeIcon"></i>
                    </button>
                </li>

                <!-- Quick Action: Admin (only when logged out) -->
                <?php if (!$nav->isLoggedIn()): ?>
                <li class="nav-item ms-1 btn-admin">
                    <a href="<?php echo BASE_URL; ?>/admin/login" class="btn btn-admin btn-sm px-2" aria-label="Admin panel">
                        <i class="fas fa-user-lock" aria-hidden="true"></i>
                        <span class="d-none ms-1"><?= __('admin') ?></span>
                    </a>
                </li>
                <?php endif; ?>


            </ul>
        </div>
    </div>
</nav>

<!-- Dark Mode Toggle Script for Frontend -->
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function toggleDarkMode() {
    var isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('aps-dark-mode', isDark ? '1' : '0');
    var icon = document.querySelector('#darkModeToggle i');
    if (icon) {
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    }
}
(function() {
    var saved = localStorage.getItem('aps-dark-mode');
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (saved === '1' || (saved === null && prefersDark)) {
        document.body.classList.add('dark-mode');
        var icon = document.querySelector('#darkModeToggle i');
        if (icon) icon.className = 'fas fa-sun';
    }
})();
</script>
