<?php
$logs = $logs ?? [];
$material_id = $material_id ?? null;
$colony_id = $colony_id ?? null;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Material Usage Log</h1>
        <a href="<?= BASE_URL ?>/admin/inventory" class="btn btn-secondary">Back to Inventory</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Material ID</label><input type="number" name="material_id" class="form-control" value="<?= $material_id ? htmlspecialchars($material_id) : '' ?>" placeholder="Material ID"></div>
            <div class="col-md-3"><label>Colony ID</label><input type="number" name="colony_id" class="form-control" value="<?= $colony_id ? htmlspecialchars($colony_id) : '' ?>" placeholder="Colony ID"></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/inventory/usage-log" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Date</th><th>Material</th><th>Category</th><th>Qty</th><th>Colony</th><th>Plot</th><th>Purpose</th><th>Used By</th><th>Notes</th></tr></thead>
        <tbody>
        <?php if (empty($logs)): ?><tr><td colspan="9" class="text-center text-muted py-4">No usage records found</td></tr>
        <?php else: foreach ($logs as $log): ?>
            <tr>
                <td><?= date('M d, Y', strtotime($log['usage_date'])) ?></td>
                <td><?= htmlspecialchars($log['material_name'] ?? '—') ?></td>
                <td><?= ucfirst($log['material_category'] ?? '') ?></td>
                <td><?= $log['quantity'] ?> <?= htmlspecialchars($log['unit'] ?? '') ?></td>
                <td><?= $log['colony_id'] ? 'C-' . $log['colony_id'] : '—' ?></td>
                <td><?= $log['plot_id'] ? 'P-' . $log['plot_id'] : '—' ?></td>
                <td><?= htmlspecialchars($log['purpose'] ?? '—') ?></td>
                <td><?= htmlspecialchars($log['used_by'] ?? '—') ?></td>
                <td><?= htmlspecialchars($log['notes'] ?? '') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div></div>
</div>