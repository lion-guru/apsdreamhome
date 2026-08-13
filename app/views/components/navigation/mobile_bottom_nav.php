<?php
/**
 * Mobile Bottom Navigation Component
 *
 * Rendered by: footer.php (replaces the inline nav in footer.php lines 170-205)
 * Provides: Sticky bottom bar with 5-6 key icon tabs — app-like UX on mobile.
 *
 * Variables available:
 *   @var NavigationHelper $nav
 */
?>

<div class="mobile-bottom-nav d-xl-none" id="mobileBottomNav">
    <div class="container-fluid px-0">
        <div class="row g-0 align-items-center">
            <?php foreach ($nav->getMobileBottomNavItems() as $item): ?>
                <?php $active = $nav->isActive($item['url']); ?>
                <div class="col text-center">
                <a href="<?php echo BASE_URL . $item['url']; ?>"
                   class="nav-item d-flex flex-column align-items-center justify-content-center py-2 <?php echo $active ? 'active' : ''; ?>"
                   data-nav-item="<?php echo $item['key']; ?>">
                        <i class="<?php echo $item['icon']; ?>"></i>
                        <span><?php echo __($item['label']); ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
