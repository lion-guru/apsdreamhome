<?php $pageTitle = 'View Report'; ?>
<?php $report = $report ?? null; $data = $data ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports">All Reports</a></li><li class="breadcrumb-item active">View Report</li></ol></nav>
    <?php if (!$report): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">Report not found</h6><p class="text-muted small">The requested report does not exist or has been deleted.</p><a href="<?= BASE_URL ?>reports" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Reports</a></div></div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1"><?= htmlspecialchars($report['title'] ?? 'Report') ?></h4><small class="text-muted"><?= htmlspecialchars($report['created_at'] ?? '') ?> | Type: <?= htmlspecialchars(ucfirst($report['type'] ?? $report['category'] ?? 'General')) ?></small></div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>reports" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <button class="btn btn-outline-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
            <a href="<?= BASE_URL ?>reports/download/<?= $report['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Download</a>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <p><?= nl2br(htmlspecialchars($report['description'] ?? '')) ?></p>
            <hr>
            <?php if (empty($data)): ?>
            <div class="text-center py-4"><i class="fas fa-database fa-2x text-muted mb-2"></i><p class="text-muted">No data available for this report</p></div>
            <?php else: ?>
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-bordered table-hover table-responsive">
                    <thead class="table-light">
                        <tr><?php foreach (array_keys($data[0] ?? []) as $col): ?><th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?></th><?php endforeach; ?></tr>
                    </thead>
                    <tbody><?php foreach ($data as $row): ?>
                        <tr><?php foreach ($row as $cell): ?><td><?= htmlspecialchars($cell ?? '-') ?></td><?php endforeach; ?></tr>
                    <?php endforeach; ?></tbody>
                </table></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
