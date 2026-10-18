<?php
$report = $report ?? [];
$year = $year ?? null;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Depreciation Report</h1>
        <a href="<?= BASE_URL ?>/admin/fixed-assets" class="btn btn-secondary">Back to Assets</a>
    </div>
    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Year</label><input type="number" name="year" class="form-control" value="<?= $year ?? '' ?>" placeholder="e.g., 2026"></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/fixed-assets/depreciation-report" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>
    <div class="card"><div class="card-body p-0">
        <?php if (empty($report)): ?>
        <div class="p-4 text-center text-muted">No depreciation records found</div>
        <?php else: ?>
        <table class="table mb-0">
            <thead><tr><th>Asset</th><th>Category</th><th>Method</th><th>Period</th><th>Opening</th><th>Depreciation</th><th>Closing</th><th>Posted</th></tr></thead>
            <tbody>
            <?php foreach ($report as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['asset_code'] . ' - ' . $r['asset_name']) ?></td>
                    <td><?= ucfirst($r['category']) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $r['depreciation_method'])) ?></td>
                    <td><?= $r['period_year'] ?>-<?= str_pad($r['period_month'], 2, '0', STR_PAD_LEFT) ?></td>
                    <td>₹<?= number_format($r['opening_book_value'], 2) ?></td>
                    <td>₹<?= number_format($r['depreciation_amount'], 2) ?></td>
                    <td>₹<?= number_format($r['closing_book_value'], 2) ?></td>
                    <td><?= $r['is_posted'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div></div>
</div>