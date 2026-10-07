<?php
$page_title = $page_title ?? 'Colony Detail';
$colony = $colony ?? [];
$plot_stats = $plotStats ?? $plot_stats ?? [];
$dev_cost = $devCost ?? $dev_cost ?? [];
$layout = $layout ?? null;
$blocks = $blocks ?? [];
$milestones = $milestones ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-city me-2"></i><?= htmlspecialchars($colony['name'] ?? 'Colony') ?></h4>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= (int)($plot_stats['total'] ?? 0) ?></h3><small class="text-muted">Total Plots (<?= (int)($plot_stats['available'] ?? 0) ?> avail)</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3>₹<?= number_format($plot_stats['total_value'] ?? 0, 0) ?></h3><small class="text-muted">Inventory Value</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3>₹<?= number_format($dev_cost['total_cost'] ?? 0, 0) ?></h3><small class="text-muted">Dev Cost (₹<?= number_format($dev_cost['total_paid'] ?? 0, 0) ?> paid)</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h5><?= htmlspecialchars(($colony['district_name'] ?? '') . ($colony['state_name'] ?? '')) ?></h5><small class="text-muted">Layout: <?= $layout ? 'Yes' : 'No' ?></small></div></div></div>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Blocks</div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Block</th><th class="text-center">Plots</th><th class="text-center">Available</th></tr></thead>
                <tbody>
                    <?php if (empty($blocks)): ?><tr><td colspan="3" class="text-center text-muted py-3">No blocks</td></tr><?php endif; ?>
                    <?php foreach ($blocks as $b): ?><tr><td><?= htmlspecialchars($b['block'] ?? '') ?></td><td class="text-center"><?= (int)($b['plot_count'] ?? 0) ?></td><td class="text-center"><?= (int)($b['available'] ?? 0) ?></td></tr><?php endforeach; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Milestones</div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Category</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if (empty($milestones)): ?><tr><td colspan="3" class="text-center text-muted py-3">No milestones</td></tr><?php endif; ?>
                    <?php foreach ($milestones as $m): ?><tr><td><?= htmlspecialchars($m['category'] ?? $m['title'] ?? '') ?></td><td><?= htmlspecialchars($m['status'] ?? '') ?></td><td><?= htmlspecialchars($m['created_at'] ?? '') ?></td></tr><?php endforeach; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>
</div>
<div class="mt-3 d-flex gap-2">
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/plots" class="btn btn-outline-success"><i class="fas fa-th me-1"></i>Plots</a>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout" class="btn btn-outline-primary"><i class="fas fa-drafting-compass me-1"></i>Layout</a>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/pricing" class="btn btn-outline-warning"><i class="fas fa-tags me-1"></i>Pricing</a>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/costs" class="btn btn-outline-info"><i class="fas fa-wallet me-1"></i>Costs</a>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/map" class="btn btn-outline-secondary"><i class="fas fa-map me-1"></i>Map</a>
</div>
