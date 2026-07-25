<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-tie me-2"></i><?= ($title ?? 'COO Dashboard') ?></h4>
        <div>
            <a href="<?= BASE_URL ?>/admin/ai/executive-assistant" class="btn btn-sm btn-info text-white me-2" title="AI Assistant">
                <i class="fas fa-robot me-1"></i>Ask AI
            </a>
            <span class="badge bg-info fs-6">COO</span>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <?php foreach (($widgets ?? []) as $widget): ?>
        <div class="col-md-3">
            <a href="<?= BASE_URL . '/' . ltrim($widget['link'] ?? '#', '/') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-<?= ($widget['icon'] ?? 'chart-bar') ?> fa-2x text-info mb-2"></i>
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
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Operations Overview</h6></div>
                <div class="card-body">
                    <?php if (!empty($analytics ?? [])): ?>
                    <canvas id="cooChart" height="250"></canvas>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-cogs fa-3x mb-3"></i><p>Operations data will appear here once available.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/admin/backoffice" class="btn btn-outline-info btn-sm"><i class="fas fa-clipboard-list me-1"></i>Backoffice</a>
                        <a href="<?= BASE_URL ?>/admin/reports" class="btn btn-outline-success btn-sm"><i class="fas fa-file-alt me-1"></i>Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
