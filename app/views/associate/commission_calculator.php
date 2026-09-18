<?php
$page_title = $page_title ?? __('assoc_calc_title', [], 'Commission Calculator - APS Dream Home');
$result = $result ?? null;
$ranks = $ranks ?? [];
$csrf_token = $csrf_token ?? '';
$old = $_POST ?? [];
?>
<div class="container-fluid px-4">
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-calculator me-2 text-primary"></i><?= __('assoc_calc_heading', [], 'Estimate Your Commission') ?></h5></div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/associate/tools/commission">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="mb-3"><label class="form-label fw-bold"><?= __('assoc_calc_sale_amount', [], 'Sale Amount (Rs.)') ?></label><input type="number" name="sale_amount" class="form-control" min="1" step="any" required value="<?= htmlspecialchars($old['sale_amount'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label fw-bold"><?= __('assoc_calc_plot_area', [], 'Plot Area (sqft)') ?></label><input type="number" name="plot_area" class="form-control" min="0" step="any" value="<?= htmlspecialchars($old['plot_area'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label fw-bold"><?= __('assoc_calc_plot_price', [], 'Plot Price (Rs.)') ?></label><input type="number" name="plot_price" class="form-control" min="0" step="any" value="<?= htmlspecialchars($old['plot_price'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label fw-bold"><?= __('assoc_calc_rank', [], 'My Rank') ?></label>
                            <select name="my_rank" class="form-select">
                                <?php foreach ($ranks as $r): ?>
                                    <option value="<?= htmlspecialchars($r['rank_slug']) ?>" <?= (($old['my_rank'] ?? 'associate') === $r['rank_slug']) ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $r['rank_slug']))) ?> (<?= (float)($r['rate'] ?? 0) ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-bolt me-1"></i><?= __('assoc_calc_btn', [], 'Calculate') ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <?php if ($result): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-success"></i><?= __('assoc_calc_result', [], 'Estimated Breakdown') ?> <small class="text-muted">(<?= __('assoc_calc_rate_used', [], 'Rate used') ?>: <?= (float)($result['my_rate'] ?? 0) ?>%)</small></h5></div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3"><div class="border rounded-3 p-3"><div class="text-muted small"><?= __('assoc_calc_direct', [], 'Direct') ?></div><div class="fw-bold fs-5">Rs.<?= number_format($result['direct_commission'] ?? 0, 2) ?></div></div></div>
                        <div class="col-6 col-md-3"><div class="border rounded-3 p-3"><div class="text-muted small"><?= __('assoc_calc_override', [], 'Override (est.)') ?></div><div class="fw-bold fs-5">Rs.<?= number_format($result['team_override_est'] ?? 0, 2) ?></div></div></div>
                        <div class="col-6 col-md-3"><div class="border rounded-3 p-3"><div class="text-muted small"><?= __('assoc_calc_escrow', [], 'Escrow') ?></div><div class="fw-bold fs-5">Rs.<?= number_format($result['escrow_est'] ?? 0, 2) ?></div></div></div>
                        <div class="col-6 col-md-3"><div class="border rounded-3 p-3 bg-light"><div class="text-muted small"><?= __('assoc_calc_total', [], 'Total (est.)') ?></div><div class="fw-bold fs-5 text-success">Rs.<?= number_format($result['total_estimated'] ?? 0, 2) ?></div></div></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="fas fa-calculator fa-3x mb-3 opacity-50"></i><p class="mb-0"><?= __('assoc_calc_hint', [], 'Fill the form and press Calculate to see your estimated commission.') ?></p></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
