<?php $pageTitle = 'Scheduled Reports'; ?>
<?php $scheduledReports = $scheduledReports ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">Scheduled Reports</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-clock me-2"></i>Scheduled Reports</h4><a href="<?= BASE_URL ?>reports/schedule" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Schedule New</a></div>
    <?php if (empty($scheduledReports)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-clock fa-3x text-muted mb-3"></i><h6 class="text-muted">No scheduled reports</h6><p class="text-muted small">Schedule a report to run automatically.</p><a href="<?= BASE_URL ?>reports/schedule" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Schedule Report</a></div></div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Report Name</th><th>Type</th><th>Frequency</th><th>Next Run</th><th>Last Run</th><th>Recipients</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody><?php foreach ($scheduledReports as $sr): ?>
                        <tr><td><?= htmlspecialchars($sr['title'] ?? $sr['name'] ?? '-') ?></td><td><?= htmlspecialchars(ucfirst($sr['type'] ?? '-')) ?></td><td><?= htmlspecialchars(ucfirst($sr['frequency'] ?? '-')) ?></td><td><?= htmlspecialchars($sr['next_run'] ?? '-') ?></td><td><?= htmlspecialchars($sr['last_run'] ?? '-') ?></td><td><?= htmlspecialchars($sr['recipients'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= ($sr['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($sr['status'] ?? 'unknown') ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>reports/view/<?= $sr['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Unscheduled this report?'))location.href='<?= BASE_URL ?>reports/unschedule/<?= $sr['id'] ?? 0 ?>'"><i class="fas fa-ban"></i></button>
                        </td></tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
