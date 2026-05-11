<?php $pageTitle = 'Farmers Dashboard'; ?>
<?php $stats = $stats ?? ['total_farmers' => 0, 'total_land' => 0, 'active_crops' => 0, 'total_revenue' => 0]; $recentFarmers = $recentFarmers ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item active">Farmers</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-tractor me-2"></i>Farmers Dashboard</h4><a href="<?= BASE_URL ?>farmers/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Farmer</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Total Farmers</small><h3 class="mb-0 mt-1"><?= number_format($stats['total_farmers'] ?? 0) ?></h3></div><div class="bg-success-subtle p-3 rounded"><i class="fas fa-users fa-2x text-success"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Land Owned</small><h3 class="mb-0 mt-1"><?= number_format($stats['total_land'] ?? 0) ?> <small>acres</small></h3></div><div class="bg-warning-subtle p-3 rounded"><i class="fas fa-tree fa-2x text-warning"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Active Crops</small><h3 class="mb-0 mt-1"><?= number_format($stats['active_crops'] ?? 0) ?></h3></div><div class="bg-info-subtle p-3 rounded"><i class="fas fa-seedling fa-2x text-info"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Revenue</small><h3 class="mb-0 mt-1">₹<?= number_format($stats['total_revenue'] ?? 0) ?></h3></div><div class="bg-primary-subtle p-3 rounded"><i class="fas fa-rupee-sign fa-2x text-primary"></i></div></div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Farmers</h6><a href="<?= BASE_URL ?>farmers/list" class="btn btn-outline-primary btn-sm">View All</a></div>
        <div class="card-body p-0">
            <?php if (empty($recentFarmers)): ?>
            <div class="text-center py-5"><i class="fas fa-tractor fa-3x text-muted mb-3"></i><p class="text-muted">No farmers registered yet</p><a href="<?= BASE_URL ?>farmers/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add First Farmer</a></div>
            <?php else: ?>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Name</th><th>Village</th><th>Land (acres)</th><th>Primary Crop</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody><?php foreach ($recentFarmers as $f): ?><tr>
                    <td><?= htmlspecialchars($f['name'] ?? '-') ?></td><td><?= htmlspecialchars($f['village'] ?? '-') ?></td><td><?= number_format($f['land_acres'] ?? 0, 2) ?></td><td><?= htmlspecialchars($f['primary_crop'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= ($f['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($f['status'] ?? '-') ?></span></td>
                    <td><a href="<?= BASE_URL ?>farmers/show/<?= $f['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                </tr><?php endforeach; ?></tbody></table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
