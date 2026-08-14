<?php

/**
 * Marketing Strategies - APS Dream Home Admin
 */
$page_title = 'Marketing Strategies';
$page_description = 'Manage marketing strategies and campaigns';

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-bullhorn me-2"></i>Marketing Strategies</h1>
            <p class="text-muted mb-0">Manage marketing strategies and campaigns</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/marketing/strategies/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Strategy
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-list fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Strategies</h6>
                            <h3 class="mb-0"><?= $total ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0"><?= $active ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded p-3">
                                <i class="fas fa-pause-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Inactive</h6>
                            <h3 class="mb-0"><?= $inactive ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Strategy Cards Grid -->
    <?php if (empty($strategies ?? [])): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                <h5>No Strategies Found</h5>
                <p class="text-muted mb-3">Create your first marketing strategy to get started.</p>
                <a href="<?= BASE_URL ?>/admin/marketing/strategies/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>New Strategy
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($strategies as $s): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <?php if (!empty($s['image_url'])): ?>
                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?= htmlspecialchars($s['title'] ?? '') ?>" class="style-24482">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" class="style-32569">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($s['title'] ?? '') ?></h5>
                                <?php if ($s['active'] ?? 0): ?>
                                    <span class="badge bg-success ms-2">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary ms-2">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <p class="card-text text-muted"><?= htmlspecialchars(mb_strimwidth($s['description'] ?? '', 0, 120, '...')) ?></p>
                            <p class="text-muted small mb-0">
                                <i class="far fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($s['created_at'] ?? 'now')) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/admin/marketing/strategies/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <form method="post" action="<?= BASE_URL ?>/admin/marketing/strategies/toggle/<?= $s['id'] ?>" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-sm <?= ($s['active'] ?? 0) ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                        <i class="fas <?= ($s['active'] ?? 0) ? 'fa-pause' : 'fa-play' ?> me-1"></i>
                                        <?= ($s['active'] ?? 0) ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
