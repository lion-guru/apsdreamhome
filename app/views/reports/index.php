<?php $pageTitle = 'All Reports'; ?>
<?php $reports = $reports ?? []; $categories = $categories ?? ['sales' => 'Sales', 'properties' => 'Properties', 'financial' => 'Financial', 'user_activity' => 'User Activity', 'associate' => 'Associate', 'customer' => 'Customer']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">All Reports</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-folder-open me-2"></i>All Reports</h4><a href="<?= BASE_URL ?>reports/generate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Generate New</a></div>
    <?php if (empty($reports)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-file-alt fa-3x text-muted mb-3"></i><h6 class="text-muted">No reports yet</h6><p class="text-muted small">Generate your first report to get started.</p><a href="<?= BASE_URL ?>reports/generate" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Generate Report</a></div></div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($reports as $r): ?>
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-<?= ($r['category'] ?? '') === 'sales' ? 'success' : (($r['category'] ?? '') === 'financial' ? 'warning' : (($r['category'] ?? '') === 'properties' ? 'info' : 'primary')) ?>"><?= htmlspecialchars($categories[$r['category'] ?? ''] ?? ucfirst($r['category'] ?? 'General')) ?></span>
                        <small class="text-muted"><?= htmlspecialchars($r['created_at'] ?? '') ?></small>
                    </div>
                    <h6 class="card-title"><?= htmlspecialchars($r['title'] ?? 'Untitled Report') ?></h6>
                    <p class="card-text small text-muted"><?= htmlspecialchars(mb_substr($r['description'] ?? '', 0, 80)) ?>...</p>
                    <a href="<?= BASE_URL ?>reports/view/<?= $r['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-eye me-1"></i>View Report</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
