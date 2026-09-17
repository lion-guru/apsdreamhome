<?php
$page_title = $hub['name'];
$active_page = 'workspace-hubs';
$hubColor = $hub['color'] ?? '#0d6efd';
$hubKey = $hub['key'] ?? '';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1">
            <i class="<?= $hub['icon'] ?? 'fas fa-th-large' ?> me-2" style="color: <?= $hubColor ?>;"></i>
            <?= $hub['name'] ?>
        </h1>
        <p class="text-muted mb-0"><?= $hub['description'] ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/workspace-hubs" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> All Hubs
        </a>
        <a href="<?= BASE_URL ?>/admin/erp" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt me-1"></i> ERP Dashboard
        </a>
    </div>
</div>

<!-- Hub Navigation Tabs -->
<ul class="nav nav-tabs mb-4 hub-tabs" id="hubSectionTabs" role="tablist">
    <?php $tabIndex = 0; foreach ($hub['sections'] as $sectionKey => $section): ?>
        <?php if (!empty($section['menus'])): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $tabIndex === 0 ? 'active' : '' ?>" 
                        data-bs-toggle="tab" 
                        data-bs-target="#hub-<?= $sectionKey ?>" 
                        role="tab" 
                        style="border-color: <?= $hubColor ?>; color: <?= $hubColor ?>;"
                        aria-selected="<?= $tabIndex === 0 ? 'true' : 'false' ?>">
                    <i class="<?= $section['icon'] ?> me-1"></i>
                    <?= $section['name'] ?>
                    <span class="badge bg-light text-dark ms-1" style="border: 1px solid <?= $hubColor ?>;"><?= count($section['menus']) ?></span>
                </button>
            </li>
        <?php endif; ?>
        <?php $tabIndex++; endforeach; ?>
</ul>

<!-- Tab Panes -->
<div class="tab-content" id="hubSectionTabsContent">
    <?php $tabIndex = 0; foreach ($hub['sections'] as $sectionKey => $section): ?>
        <?php if (!empty($section['menus'])): ?>
            <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : '' ?>" 
                 id="hub-<?= $sectionKey ?>" 
                 role="tabpanel">
                
                <div class="row g-3">
                    <?php foreach ($section['menus'] as $menuUrl): ?>
                        <?php 
                            $menuItem = null;
                            try {
                                $db = \App\Core\Database\Database::getInstance();
                                $menuItem = $db->fetchRow("SELECT * FROM admin_menu_items WHERE url = ? AND is_active = 1 LIMIT 1", [$menuUrl]);
                            } catch (\Throwable $e) {}
                        ?>
                        <div class="col-xl-4 col-lg-6">
                            <a href="<?= BASE_URL . $menuUrl ?>" class="card aps-cp-card h-100 hub-menu-card text-decoration-none" 
                               style="border-left: 3px solid <?= $hubColor ?>; transition: all 0.2s ease;">
                                <div class="card-body aps-cp-card-body d-flex flex-column">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="menu-icon me-3" style="width: 48px; height: 48px; border-radius: 10px; background: <?= $hubColor ?>15; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="<?= $menuItem['icon'] ?? 'fas fa-circle' ?> fa-lg" style="color: <?= $hubColor ?>;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold" style="color: #1e293b;"><?= htmlspecialchars($menuItem['name'] ?? basename($menuUrl)) ?></h6>
                                            <p class="text-muted small mb-0"><code><?= htmlspecialchars($menuUrl) ?></code></p>
                                        </div>
                                    </div>
                                    <div class="mt-auto pt-2 border-top">
                                        <span class="btn btn-sm" style="background: <?= $hubColor ?>; border-color: <?= $hubColor ?>; color: white;">
                                            <i class="fas fa-external-link-alt me-1"></i> Open
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($section['menus'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No accessible menu items</h5>
                        <p class="text-muted">Your role permissions don't grant access to any items in this section.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php $tabIndex++; endforeach; ?>
</div>

<script>
// Add hover effects for menu cards
document.querySelectorAll('.hub-menu-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px)';
        this.style.boxShadow = '0 6px 20px rgba(0,0,0,0.12)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '';
    });
});

// Persist active tab
const hubTabs = document.querySelectorAll('#hubSectionTabs button[data-bs-toggle="tab"]');
hubTabs.forEach(tab => {
    tab.addEventListener('shown.bs.tab', function(e) {
        localStorage.setItem('activeHubTab', e.target.getAttribute('data-bs-target'));
    });
});

// Restore active tab on load
const savedTab = localStorage.getItem('activeHubTab');
if (savedTab) {
    const tabEl = document.querySelector(`#hubSectionTabs button[data-bs-target="${savedTab}"]`);
    if (tabEl) {
        new bootstrap.Tab(tabEl).show();
    }
}
</script>

<style>
.hub-tabs .nav-link {
    border-radius: 8px 8px 0 0 !important;
    font-weight: 500;
}
.hub-tabs .nav-link.active {
    background: <?= $hubColor ?>;
    color: white !important;
    border-color: <?= $hubColor ?> <?= $hubColor ?> transparent <?= $hubColor ?> !important;
}
.hub-tabs .nav-link:hover:not(.active) {
    background: <?= $hubColor ?>10;
    border-color: <?= $hubColor ?> <?= $hubColor ?> transparent <?= $hubColor ?> !important;
}
.hub-menu-card:hover {
    border-left-width: 6px !important;
}
.menu-icon {
    transition: all 0.2s ease;
}
.hub-menu-card:hover .menu-icon {
    background: <?= $hubColor ?> !important;
    color: white !important;
}
</style>