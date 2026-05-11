<?php $pageTitle = 'Properties Report'; ?>
<?php $propertiesData = $propertiesData ?? []; $summary = $summary ?? ['total' => 0, 'available' => 0, 'sold' => 0, 'pending' => 0]; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">Properties Report</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-building me-2"></i>Properties Report</h4><a href="<?= BASE_URL ?>reports/generate?type=properties" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Export</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Total Properties</small><h4 class="mb-0"><?= number_format($summary['total'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Available</small><h4 class="text-success mb-0"><?= number_format($summary['available'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Sold</small><h4 class="text-danger mb-0"><?= number_format($summary['sold'] ?? 0) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><small class="text-muted">Pending</small><h4 class="text-warning mb-0"><?= number_format($summary['pending'] ?? 0) ?></h4></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Property Inventory</h6></div>
        <div class="card-body p-0">
            <?php if (empty($propertiesData)): ?>
            <div class="text-center py-4"><p class="text-muted mb-0">No property data available</p></div>
            <?php else: ?>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Property Name</th><th>Type</th><th>Location</th><th>Price</th><th>Status</th><th>Listed</th></tr></thead>
                <tbody><?php foreach ($propertiesData as $p): ?><tr><td><?= htmlspecialchars($p['name'] ?? $p['title'] ?? '-') ?></td><td><?= htmlspecialchars(ucfirst($p['type'] ?? '-')) ?></td><td><?= htmlspecialchars($p['location'] ?? $p['city'] ?? '-') ?></td><td>₹<?= number_format($p['price'] ?? 0) ?></td><td><span class="badge bg-<?= ($p['status'] ?? '') === 'available' ? 'success' : (($p['status'] ?? '') === 'sold' ? 'danger' : 'warning') ?>"><?= ucfirst($p['status'] ?? '-') ?></span></td><td><?= htmlspecialchars($p['created_at'] ?? '-') ?></td></tr><?php endforeach; ?></tbody></table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
