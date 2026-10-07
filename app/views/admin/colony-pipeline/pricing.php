<?php
$page_title = $page_title ?? 'Colony Pricing';
$colony = $colony ?? [];
$plot_stats = $plot_stats ?? [];
$dev_costs = $dev_costs ?? [];
$total_dev_cost = $total_dev_cost ?? ['total' => 0];
$price_bands = $price_bands ?? [];
$block_list = $block_list ?? [];
$phase_list = $phase_list ?? [];
$pending_approvals = $pending_approvals ?? [];
$approval_history = $approval_history ?? [];
$recently_overridden = $recently_overridden ?? [];
$cid = (int)($colony['id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-tags me-2"></i>Pricing — <?= htmlspecialchars($colony['name'] ?? '') ?></h4>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= (int)($plot_stats['total'] ?? 0) ?></h4><small class="text-muted">Plots · avg ₹<?= number_format($plot_stats['avg_ppsf'] ?? 0, 0) ?>/sqft</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4>₹<?= number_format($plot_stats['min_price'] ?? 0, 0) ?> – ₹<?= number_format($plot_stats['max_price'] ?? 0, 0) ?></h4><small class="text-muted">Price Range</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4>₹<?= number_format($total_dev_cost['total'] ?? 0, 0) ?></h4><small class="text-muted">Dev Cost (incl GST)</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= count($pending_approvals) ?></h4><small class="text-muted">Pending Approvals</small></div></div></div>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Price Bands</div>
            <div class="card-body p-0"><table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Band</th><th class="text-center">Plots</th></tr></thead>
                <tbody>
                    <?php if (empty($price_bands)): ?><tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr><?php endif; ?>
                    <?php foreach ($price_bands as $b): ?><tr><td><?= htmlspecialchars($b['price_band'] ?? '') ?></td><td class="text-center"><?= (int)($b['plot_count'] ?? 0) ?></td></tr><?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Dev Cost by Type</div>
            <div class="card-body p-0"><table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Type</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    <?php if (empty($dev_costs)): ?><tr><td colspan="2" class="text-center text-muted py-3">No costs</td></tr><?php endif; ?>
                    <?php foreach ($dev_costs as $d): ?><tr><td><?= htmlspecialchars($d['cost_type'] ?? '') ?></td><td class="text-end">₹<?= number_format($d['total_amount'] ?? 0, 0) ?></td></tr><?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Recalculate Pricing</div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/pricing/calculate" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-calculator me-1"></i>Calculate Pricing</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/pricing/apply" class="d-inline" onsubmit="return confirm('Apply base price to all available plots?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="sub_action" value="apply_all">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Apply to Available</button>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Pending Approvals (<?= count($pending_approvals) ?>)</div>
            <div class="card-body p-0"><table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Plot</th><th class="text-end">Amount</th><th>By</th></tr></thead>
                <tbody>
                    <?php if (empty($pending_approvals)): ?><tr><td colspan="3" class="text-center text-muted py-3">None pending</td></tr><?php endif; ?>
                    <?php foreach ($pending_approvals as $p): ?><tr><td><?= htmlspecialchars($p['plot_number'] ?? '') ?> (<?= htmlspecialchars($p['block'] ?? '') ?>)</td><td class="text-end">₹<?= number_format($p['amount'] ?? $p['total_price'] ?? 0, 0) ?></td><td><?= htmlspecialchars($p['requested_by_name'] ?? '') ?></td></tr><?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
