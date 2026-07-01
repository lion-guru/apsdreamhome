<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i><?= ($title ?? 'Marketing Dashboard') ?></h4>
        <span class="badge bg-danger fs-6">Marketing</span>
    </div>
    <div class="row g-3 mb-4">
        <?php foreach (($widgets ?? []) as $widget): ?>
        <div class="col-md-3">
            <a href="<?= ($base ?? BASE_URL) . ltrim($widget['link'] ?? '#', '/') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-<?= ($widget['icon'] ?? 'chart-bar') ?> fa-2x text-danger mb-2"></i>
                        <h5 class="mb-0"><?= htmlspecialchars((string)($widget['count'] ?? 0)) ?></h5>
                        <small class="text-muted"><?= htmlspecialchars($widget['title'] ?? '') ?></small>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-ad me-2"></i>Campaign Performance</h6></div>
                <div class="card-body">
                    <?php if (!empty($analytics ?? [])): ?>
                    <canvas id="marketingChart" height="250"></canvas>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-ad fa-3x mb-3"></i><p>Campaign data will appear here once available.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= ($base ?? BASE_URL) ?>admin/marketing-campaigns" class="btn btn-outline-danger btn-sm"><i class="fas fa-bullhorn me-1"></i>Campaigns</a>
                        <a href="<?= ($base ?? BASE_URL) ?>admin/bulk-outreach" class="btn btn-outline-primary btn-sm"><i class="fas fa-paper-plane me-1"></i>Bulk Outreach</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
