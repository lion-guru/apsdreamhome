<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-laptop-code me-2"></i><?= ($title ?? 'IT Dashboard') ?></h4>
        <div>
            <a href="<?= BASE_URL ?>/admin/ai/executive-assistant" class="btn btn-sm btn-info text-white me-2" title="AI Assistant">
                <i class="fas fa-robot me-1"></i>Ask AI
            </a>
            <span class="badge bg-primary fs-6">IT</span>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <?php foreach (($widgets ?? []) as $widget): ?>
        <div class="col-md-3">
            <a href="<?= ($base ?? BASE_URL) . ltrim($widget['link'] ?? '#', '/') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-<?= ($widget['icon'] ?? 'chart-bar') ?> fa-2x text-primary mb-2"></i>
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
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-server me-2"></i>System Health</h6></div>
                <div class="card-body">
                    <?php if (!empty($analytics ?? [])): ?>
                    <canvas id="itChart" height="250"></canvas>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-server fa-3x mb-3"></i><p>System metrics will appear here once available.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= ($base ?? BASE_URL) ?>admin/cache" class="btn btn-outline-primary btn-sm"><i class="fas fa-database me-1"></i>Cache Management</a>
                        <a href="<?= ($base ?? BASE_URL) ?>admin/settings" class="btn btn-outline-secondary btn-sm"><i class="fas fa-cog me-1"></i>System Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
