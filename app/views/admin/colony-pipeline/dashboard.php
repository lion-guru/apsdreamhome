<?php
$page_title = $page_title ?? 'Colony Development Pipeline';
$colonies = $colonies ?? [];
$stats = $stats ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-project-diagram me-2"></i><?= htmlspecialchars($page_title) ?></h4>
    <a href="<?= BASE_URL ?>/admin/legal-colony-pipeline" class="btn btn-outline-primary"><i class="fas fa-gavel me-1"></i>Legal Pipeline</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= (int)($stats['total_colonies'] ?? count($colonies)) ?></h3><small class="text-muted">Colonies</small></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= (int)($stats['total_plots'] ?? 0) ?></h3><small class="text-muted">Total Plots</small></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= (int)($stats['total_available'] ?? 0) ?></h3><small class="text-muted">Available</small></div></div></div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Colony</th><th>District</th><th class="text-center">Plots</th><th class="text-center">Avail / Booked / Sold</th><th class="text-end">Total Value</th><th class="text-center">Layout</th><th class="text-end">Dev Cost</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($colonies)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No colonies found</td></tr>
                    <?php else: ?>
                        <?php foreach ($colonies as $c): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($c['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['district_name'] ?? '') ?></td>
                                <td class="text-center"><?= (int)($c['total_plots'] ?? 0) ?></td>
                                <td class="text-center"><span class="text-success"><?= (int)($c['available_plots'] ?? 0) ?></span> / <span class="text-warning"><?= (int)($c['booked_plots'] ?? 0) ?></span> / <span class="text-primary"><?= (int)($c['sold_plots'] ?? 0) ?></span></td>
                                <td class="text-end">₹<?= number_format($c['total_value'] ?? 0, 2) ?></td>
                                <td class="text-center"><?= !empty($c['has_layout']) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                <td class="text-end">₹<?= number_format($c['total_dev_cost'] ?? 0, 2) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($c['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($c['id'] ?? 0) ?>/plots" class="btn btn-sm btn-outline-success" title="Plots"><i class="fas fa-th"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($c['id'] ?? 0) ?>/pricing" class="btn btn-sm btn-outline-warning" title="Pricing"><i class="fas fa-tags"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
