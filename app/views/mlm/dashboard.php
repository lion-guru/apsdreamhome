<?php $pageTitle = 'MLM Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mlm">MLM</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-sitemap me-2"></i>MLM Dashboard</h4>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-users"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalMembers ?? 0 ?></h3>
                    <p class="text-muted mb-0">Total Members</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-user-plus"></i></div>
                    <h3 class="fw-bold mb-1"><?= $activeMembers ?? 0 ?></h3>
                    <p class="text-muted mb-0">Active Members</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-layer-group"></i></div>
                    <h3 class="fw-bold mb-1"><?= $totalLevels ?? 0 ?></h3>
                    <p class="text-muted mb-0">Network Levels</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-coins"></i></div>
                    <h3 class="fw-bold mb-1">₹<?= number_format($totalCommission ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Commission</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-star me-2"></i>Top Performers</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($topPerformers)): ?>
                        <?php foreach ($topPerformers as $i => $p): ?>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-<?= $i === 0 ? 'warning' : ($i === 1 ? 'secondary' : 'info') ?> me-2">#<?= $i + 1 ?></span>
                            <div class="flex-grow-1"><?= htmlspecialchars($p['name'] ?? '') ?></div>
                            <strong>₹<?= number_format($p['earnings'] ?? 0) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-trophy fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No performers data yet</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Network Growth</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($growthData)): ?>
                        <?php foreach ($growthData as $g): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= htmlspecialchars($g['month'] ?? '') ?></span>
                            <strong>+<?= $g['new_members'] ?? 0 ?> members</strong>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-line fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No growth data available</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
