<?php
$page_title = 'Workspace Hubs';
$active_page = 'workspace-hubs';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-th-large me-2"></i>Workspace Hubs</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/erp" class="btn btn-outline-secondary">
            <i class="fas fa-tachometer-alt me-1"></i> ERP Dashboard
        </a>
    </div>
</div>

<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Role-Based Workspace Hubs</strong> — Each hub provides a focused, curated set of menu items tailored for specific job roles.
    Users are automatically directed to their assigned hub based on their role. Super Admin can access all hubs.
</div>

<div class="row g-4">
    <?php foreach ($hubs as $hubKey => $hub): ?>
        <div class="col-xl-4 col-lg-6">
            <div class="card aps-cp-card h-100 hub-card" style="border-left: 4px solid <?= $hub['color'] ?>;">
                <div class="card-body aps-cp-card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="hub-icon me-3" style="width: 60px; height: 60px; border-radius: 12px; background: <?= $hub['color'] ?>20; display: flex; align-items: center; justify-content: center;">
                            <i class="<?= $hub['icon'] ?> fa-2x" style="color: <?= $hub['color'] ?>;"></i>
                        </div>
                        <div>
                            <h5 class="mb-1"><?= $hub['name'] ?></h5>
                            <p class="text-muted small mb-0"><?= $hub['description'] ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong class="small text-muted">Target Roles:</strong>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <?php foreach ($hub['roles'] as $roleKey): ?>
                                <?php 
                                    $roleInfo = \App\Http\Middleware\RBACManager::getRoleInfo($roleKey);
                                    $badgeColor = 'secondary';
                                    if ($roleInfo) {
                                        $cat = $roleInfo['category'] ?? '';
                                        $badgeColor = match($cat) {
                                            'Executive' => 'danger',
                                            'Management', 'Departmental' => 'warning',
                                            'Team Lead' => 'success',
                                            'Senior Staff', 'Staff' => 'primary',
                                            'Telecalling' => 'purple',
                                            'MLM', 'Agent' => 'orange',
                                            'Franchise' => 'pink',
                                            'Customer' => 'success',
                                            'Lead' => 'indigo',
                                            'Guest' => 'dark',
                                            'Legacy' => 'light',
                                            default => 'secondary',
                                        };
                                    }
                                ?>
                                <span class="badge bg-<?= $badgeColor ?>"><?= $roleInfo['name'] ?? $roleKey ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="flex-grow-1">
                        <div class="small text-muted mb-2">Sections:</div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($hub['sections'] as $sectionKey => $section): ?>
                                <li class="mb-1 d-flex align-items-center">
                                    <i class="<?= $section['icon'] ?> me-2 text-muted" style="width: 16px;"></i>
                                    <span><?= $section['name'] ?></span>
                                    <span class="badge bg-light text-dark ms-auto"><?= count($section['menus']) ?> items</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/admin/workspace-hubs/<?= $hubKey ?>" class="btn w-100" style="background: <?= $hub['color'] ?>; border-color: <?= $hub['color'] ?>; color: white;">
                            <i class="fas fa-arrow-right me-1"></i> Open Hub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Add hover effects for hub cards
document.querySelectorAll('.hub-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-4px)';
        this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '';
    });
});
</script>

<style>
.hub-card {
    transition: all 0.3s ease;
}
.hub-icon {
    transition: all 0.3s ease;
}
.hub-card:hover .hub-icon {
    background: var(--bs-primary) !important;
    color: white !important;
}
</style>