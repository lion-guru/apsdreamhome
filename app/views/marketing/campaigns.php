<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Marketing Campaigns') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/marketing/campaigns/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Campaign</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select class="form-select form-select-sm" onchange="filterByStatus(this.value)">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="paused">Paused</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col text-end">
                    <span class="text-muted small"><?= count($campaigns ?? []) ?> campaigns</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Campaign</th><th>Type</th><th>Status</th><th>Budget</th><th>Spent</th><th>Leads</th><th>Conv.</th><th>Start</th><th>End</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($campaigns)): ?>
                            <?php foreach ($campaigns as $c): ?>
                                <tr class="campaign-row" data-status="<?= htmlspecialchars($c['status'] ?? '') ?>">
                                    <td><strong><?= htmlspecialchars($c['name'] ?? '') ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($c['type'] ?? '-') ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= ($c['status'] ?? 'draft') === 'active' ? 'success' : (($c['status'] ?? 'draft') === 'scheduled' ? 'info' : (($c['status'] ?? 'draft') === 'paused' ? 'warning' : 'secondary')) ?>">
                                            <?= htmlspecialchars($c['status'] ?? 'draft') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($c['budget'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($c['spent'] ?? '-') ?></td>
                                    <td><?= (int)($c['leads'] ?? 0) ?></td>
                                    <td><?= round((float)($c['conversion_rate'] ?? 0), 1) ?>%</td>
                                    <td><?= htmlspecialchars($c['start_date'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($c['end_date'] ?? '-') ?></td>
                                    <td>
                                        <a href="<?= $base ?? BASE_URL ?>/marketing/campaigns/edit?id=<?= (int)($c['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCampaign(<?= (int)($c['id'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center text-muted py-3">No campaigns found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    document.querySelectorAll('.campaign-row').forEach(row => {
        row.style.display = !status || row.dataset.status === status ? '' : 'none';
    });
}
function deleteCampaign(id) {
    if (!confirm('Delete this campaign?')) return;
    fetch('<?= $base ?? BASE_URL ?>/marketing/campaigns/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>