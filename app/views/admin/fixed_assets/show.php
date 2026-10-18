<?php
$asset = $asset ?? [];
$schedule = $schedule ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($asset['asset_name'] ?? 'Asset') ?></h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/fixed-assets/edit/<?= $asset['id'] ?? '' ?>" class="btn btn-outline-secondary"><i class="fas fa-edit me-2"></i>Edit</a>
            <a href="<?= BASE_URL ?>/admin/fixed-assets" class="btn btn-secondary ms-2">Back</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h6>Asset Code</h6><p class="mb-0 fw-bold"><?= htmlspecialchars($asset['asset_code'] ?? '') ?></p></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h6>Purchase Cost</h6><p class="mb-0 fw-bold">₹<?= number_format($asset['purchase_cost'] ?? 0, 2) ?></p></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h6>Current Book Value</h6><p class="mb-0 fw-bold">₹<?= number_format($asset['current_book_value'] ?? 0, 2) ?></p></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h6>Depreciation Method</h6><p class="mb-0 fw-bold"><?= ucfirst(str_replace('_', ' ', $asset['depreciation_method'] ?? '')) ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Category</h6><p class="mb-0"><?= ucfirst($asset['category'] ?? '') ?></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Status</h6><p class="mb-0"><span class="badge bg-<?= ($asset['status'] ?? '')==='active'?'success':'secondary' ?>"><?= ucfirst($asset['status'] ?? '') ?></span></p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Useful Life</h6><p class="mb-0"><?= $asset['useful_life_years'] ?? 0 ?> years</p></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Salvage Value</h6><p class="mb-0">₹<?= number_format($asset['salvage_value'] ?? 0, 2) ?></p></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Depreciation Schedule</strong>
                    <?php if (empty($schedule)): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/fixed-assets/generate-schedule/<?= $asset['id'] ?? '' ?>" class="d-inline" onsubmit="return confirm('Generate full depreciation schedule?');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-calculator me-1"></i>Generate Schedule</button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($schedule)): ?>
                    <div class="p-4 text-center text-muted">No depreciation schedule generated yet</div>
                    <?php else: ?>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Period</th><th>Opening</th><th>Depreciation</th><th>Closing</th><th>Cumulative</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($schedule, 0, 24) as $s): ?>
                            <tr>
                                <td><?= $s['period_year'] ?>-<?= str_pad($s['period_month'], 2, '0', STR_PAD_LEFT) ?></td>
                                <td>₹<?= number_format($s['opening_book_value'], 2) ?></td>
                                <td>₹<?= number_format($s['depreciation_amount'], 2) ?></td>
                                <td>₹<?= number_format($s['closing_book_value'], 2) ?></td>
                                <td>₹<?= number_format($s['cumulative_depreciation'], 2) ?></td>
                                <td><?php if ($s['is_posted']): ?><span class="badge bg-success">Posted</span><?php else: ?><form method="POST" action="<?= BASE_URL ?>/admin/fixed-assets/post-depreciation/<?= $asset['id'] ?? '' ?>" class="d-inline"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="period_year" value="<?= $s['period_year'] ?>"><input type="hidden" name="period_month" value="<?= $s['period_month'] ?>"><button type="submit" class="btn btn-sm btn-outline-primary">Post</button></form><?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($schedule) > 24): ?><tr><td colspan="6" class="text-center text-muted">... and <?= count($schedule) - 24 ?> more periods</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Asset Details</strong></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td class="fw-bold">Serial Number</td><td><?= htmlspecialchars($asset['serial_number'] ?? '—') ?></td></tr>
                        <tr><td class="fw-bold">Purchase Date</td><td><?= date('M d, Y', strtotime($asset['purchase_date'] ?? '')) ?></td></tr>
                        <tr><td class="fw-bold">Location</td><td><?= htmlspecialchars($asset['location'] ?? '—') ?></td></tr>
                        <tr><td class="fw-bold">Department</td><td><?= htmlspecialchars($asset['department_name'] ?? '—') ?></td></tr>
                        <tr><td class="fw-bold">Assigned To</td><td><?= htmlspecialchars($asset['assigned_to_name'] ?? '—') ?></td></tr>
                        <tr><td class="fw-bold">Vendor</td><td><?= htmlspecialchars($asset['vendor_name'] ?? '—') ?></td></tr>
                        <tr><td class="fw-bold">Invoice</td><td><?= htmlspecialchars($asset['invoice_number'] ?? '—') ?></td></tr>
                        <tr><td class="fw-bold">Warranty Expiry</td><td><?= $asset['warranty_expiry'] ? date('M d, Y', strtotime($asset['warranty_expiry'])) : '—' ?></td></tr>
                        <tr><td class="fw-bold">Depreciation Rate</td><td><?= number_format($asset['depreciation_rate'] ?? 0, 2) ?>% p.a.</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>