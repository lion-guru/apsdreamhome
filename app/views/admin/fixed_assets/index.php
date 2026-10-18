<?php
$assets = $assets ?? [];
$stats = $stats ?? ['total'=>0,'active'=>0,'total_cost'=>0,'total_book_value'=>0,'by_category'=>[]];
$filters = $filters ?? ['status'=>'','category'=>''];
$categories = $categories ?? [];
$statuses = $statuses ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Fixed Asset Register</h1>
        <a href="<?= BASE_URL ?>/admin/fixed-assets/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Asset</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total'] ?></h3><small>Total Assets</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= $stats['active'] ?></h3><small>Active</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_cost'] ?? 0) ?></h3><small>Original Cost</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3>₹<?= number_format($stats['total_book_value'] ?? 0) ?></h3><small>Book Value</small></div></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3"><label>Category</label><select name="category" class="form-select"><option value="">All</option><?php foreach ($categories as $c): ?><option value="<?= $c ?>" <?= $filters['category']===$c?'selected':'' ?>><?= ucfirst($c) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="">All</option><?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/fixed-assets" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Purchase Date</th><th>Cost</th><th>Book Value</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($assets)): ?><tr><td colspan="8" class="text-center text-muted py-4">No assets found</td></tr>
        <?php else: foreach ($assets as $a): ?>
            <tr>
                <td><code><?= htmlspecialchars($a['asset_code']) ?></code></td>
                <td><?= htmlspecialchars($a['asset_name']) ?></td>
                <td><?= ucfirst($a['category']) ?></td>
                <td><?= date('M Y', strtotime($a['purchase_date'])) ?></td>
                <td>₹<?= number_format($a['purchase_cost'], 2) ?></td>
                <td>₹<?= number_format($a['current_book_value'], 2) ?></td>
                <td><span class="badge bg-<?= $a['status']==='active'?'success':'secondary' ?>"><?= ucfirst($a['status']) ?></span></td>
                <td><a href="<?= BASE_URL ?>/admin/fixed-assets/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div></div>
</div>