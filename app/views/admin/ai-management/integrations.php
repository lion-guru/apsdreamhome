<?php
$page_title = $page_title ?? 'AI Integrations';
$integrations = $integrations ?? [];
$stats = $stats ?? ['total' => 0, 'active' => 0, 'inactive' => 0];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">AI Integrations</h1>
        <p class="text-muted mb-0">Manage third-party AI tool integrations</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                            <i class="fas fa-plug fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Integrations</h6>
                        <h3 class="mb-0"><?= $stats['total'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
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
                        <h3 class="mb-0"><?= $stats['active'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Inactive</h6>
                        <h3 class="mb-0"><?= $stats['inactive'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Integration Tools</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tool Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($integrations)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No integrations found</td></tr>
                    <?php else: ?>
                        <?php foreach ($integrations as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['tool_name'] ?? '') ?></strong></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['integration_type'] ?? '') ?></span></td>
                                <td>
                                    <span class="badge bg-<?= ($row['status'] ?? '') === 'active' ? 'success' : 'secondary' ?> integration-status-<?= $row['id'] ?>">
                                        <?= ucfirst($row['status'] ?? 'inactive') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-<?= ($row['status'] ?? '') === 'active' ? 'warning' : 'success' ?>" onclick="toggleIntegration(<?= $row['id'] ?>)">
                                        <i class="fas fa-<?= ($row['status'] ?? '') === 'active' ? 'pause' : 'play' ?>"></i>
                                        <?= ($row['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleIntegration(id) {
    if (!confirm('Toggle this integration status?')) return;
    fetch('<?= BASE_URL ?>/admin/ai-management/toggle-integration/' + id, { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                location.reload();
            } else {
                alert('Error: ' + (d.message || 'Unknown error'));
            }
        })
        .catch(() => alert('Request failed'));
}
</script>
