<?php
$page_title = $page_title ?? 'Development Costs';
$colony = $colony ?? [];
$costs = $costs ?? [];
$summary = $summary ?? [];
$by_type = $byType ?? $by_type ?? [];
$cid = (int)($colony['id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-wallet me-2"></i>Dev Costs — <?= htmlspecialchars($colony['name'] ?? '') ?></h4>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4>₹<?= number_format($summary['total_amount'] ?? 0, 0) ?></h4><small class="text-muted">Total (+₹<?= number_format($summary['total_gst'] ?? 0, 0) ?> GST)</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4 class="text-success">₹<?= number_format($summary['total_paid'] ?? 0, 0) ?></h4><small class="text-muted">Paid</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4 class="text-danger">₹<?= number_format($summary['total_balance'] ?? 0, 0) ?></h4><small class="text-muted">Balance</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= (int)($summary['cost_count'] ?? 0) ?></h4><small class="text-muted">Entries (<?= count($by_type) ?> types)</small></div></div></div>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Add Cost</div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/costs/store" class="row g-2">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="col-md-3"><input type="text" name="cost_type" class="form-control" placeholder="Cost type *" required></div>
            <div class="col-md-3"><input type="text" name="vendor_name" class="form-control" placeholder="Vendor"></div>
            <div class="col-md-2"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount *" required></div>
            <div class="col-md-2"><input type="number" step="0.01" name="gst_amount" class="form-control" placeholder="GST" value="0"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Add</button></div>
            <div class="col-12"><input type="text" name="work_description" class="form-control" placeholder="Work description"></div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Type</th><th>Vendor</th><th>Description</th><th class="text-end">Amount</th><th class="text-end">GST</th><th class="text-end">Paid</th><th class="text-end">Balance</th><th>Status</th></tr></thead>
        <tbody>
            <?php if (empty($costs)): ?><tr><td colspan="8" class="text-center text-muted py-3">No costs recorded</td></tr><?php endif; ?>
            <?php foreach ($costs as $c): ?><tr><td><?= htmlspecialchars($c['cost_type'] ?? '') ?></td><td><?= htmlspecialchars($c['vendor_name_lookup'] ?? $c['vendor_name'] ?? '') ?></td><td><?= htmlspecialchars(substr($c['work_description'] ?? '', 0, 60)) ?></td><td class="text-end">₹<?= number_format($c['amount'] ?? 0, 0) ?></td><td class="text-end">₹<?= number_format($c['gst_amount'] ?? 0, 0) ?></td><td class="text-end text-success">₹<?= number_format($c['paid_amount'] ?? 0, 0) ?></td><td class="text-end text-danger">₹<?= number_format($c['balance_amount'] ?? 0, 0) ?></td><td><?= htmlspecialchars($c['payment_status'] ?? $c['status'] ?? '') ?></td></tr><?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
