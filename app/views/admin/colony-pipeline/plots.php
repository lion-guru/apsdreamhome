<?php
$page_title = $page_title ?? 'Colony Plots';
$colony = $colony ?? [];
$plots = $plots ?? [];
$plot_stats = $plot_stats ?? [];
$total_plots = $total_plots ?? count($plots);
$total_pages = $total_pages ?? 1;
$current_page = $current_page ?? 1;
$filters = $filters ?? ['status' => '', 'block' => ''];
$cid = (int)($colony['id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-th me-2"></i>Plots — <?= htmlspecialchars($colony['name'] ?? '') ?></h4>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= (int)($plot_stats['total'] ?? $total_plots) ?></h4><small class="text-muted">Total</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4 class="text-success"><?= (int)($plot_stats['available'] ?? 0) ?></h4><small class="text-muted">Available</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4>₹<?= number_format($plot_stats['total_value'] ?? 0, 0) ?></h4><small class="text-muted">Total Value</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4>₹<?= number_format($plot_stats['avg_ppsf'] ?? 0, 0) ?></h4><small class="text-muted">Avg ₹/sqft</small></div></div></div>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">All statuses</option><?php foreach (['available','booked','sold','hold','reserved'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><input type="text" name="block" class="form-control" placeholder="Block" value="<?= htmlspecialchars($filters['block'] ?? '') ?>"></div>
            <div class="col-md-4"><button type="submit" class="btn btn-outline-primary">Filter</button> <a href="?" class="btn btn-light border">Clear</a></div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Plot No</th><th>Block</th><th class="text-end">Area (sqft)</th><th class="text-end">₹/sqft</th><th class="text-end">Total Price</th><th>Status</th></tr></thead>
        <tbody>
            <?php if (empty($plots)): ?><tr><td colspan="6" class="text-center text-muted py-3">No plots found</td></tr><?php endif; ?>
            <?php foreach ($plots as $p): ?><tr><td class="fw-medium"><?= htmlspecialchars($p['plot_number'] ?? '') ?></td><td><?= htmlspecialchars($p['block'] ?? '') ?></td><td class="text-end"><?= htmlspecialchars($p['area_sqft'] ?? '') ?></td><td class="text-end">₹<?= number_format($p['price_per_sqft'] ?? 0, 0) ?></td><td class="text-end">₹<?= number_format($p['total_price'] ?? 0, 0) ?></td><td><span class="badge bg-<?= ($p['status'] ?? '') === 'available' ? 'success' : (($p['status'] ?? '') === 'sold' ? 'primary' : 'warning') ?>"><?= htmlspecialchars($p['status'] ?? '') ?></span></td></tr><?php endforeach; ?>
        </tbody>
    </table></div></div>
    <?php if ($total_pages > 1): ?><div class="card-footer bg-white"><nav><ul class="pagination pagination-sm mb-0 justify-content-center"><?php for ($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?= $i === (int)$current_page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filters['status'] ?? '') ?>&block=<?= urlencode($filters['block'] ?? '') ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav></div><?php endif; ?>
</div>
