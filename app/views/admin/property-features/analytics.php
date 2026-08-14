<?php $features = $features ?? []; $stats = $stats ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Property Features Analytics</h4>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?= $stats['total'] ?? count($features) ?></h5>
                <small>Total Features</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?= $stats['active'] ?? count(array_filter($features, fn($f) => ($f['status'] ?? 'active') === 'active')) ?></h5>
                <small>Active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?= $stats['most_used'] ?? ($features[0]['usage_count'] ?? 0) ?></h5>
                <small>Most Used</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-secondary text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?= $stats['least_used'] ?? (count($features) > 0 ? end($features)['usage_count'] ?? 0 : 0) ?></h5>
                <small>Least Used</small>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header aps-cp-card-header">Usage Breakdown</div>
            <div class="card-body aps-cp-card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Feature Name</th>
                                <th>Type</th>
                                <th>Usage Count</th>
                                <th>Properties Using</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($features)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No features data.</td></tr>
                            <?php else: ?>
                                <?php foreach ($features as $f): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($f['name'] ?? $f['feature_name'] ?? '-') ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($f['type'] ?? $f['feature_type'] ?? 'general') ?></span></td>
                                        <td><strong><?= $f['usage_count'] ?? $f['count'] ?? 0 ?></strong></td>
                                        <td><?= $f['properties_count'] ?? $f['properties_using'] ?? 0 ?></td>
                                        <td><?php $s = $f['status'] ?? 'active'; ?>
                                            <span class="badge bg-<?= $s === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($s) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header aps-cp-card-header">Usage Chart</div>
            <div class="card-body aps-cp-card-body">
                <div class="style-96132">
                    <div class="text-center">
                        <i class="fas fa-chart-bar fa-3x mb-2"></i>
                        <p class="mb-0 small">Chart placeholder</p>
                        <p class="mb-0 small">Integrate Chart.js here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
