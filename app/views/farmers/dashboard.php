<?php $pageTitle = 'Farmers Dashboard'; ?>
<?php $statistics = $statistics ?? ['total_farmers' => 0, 'unique_states' => 0, 'unique_districts' => 0, 'farmers_with_state' => 0]; $farmers = $farmers ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item active">Farmers</li></ol></nav>
    <div class="d-flex justify-content-between align-items-center mb-4"><h4 class="mb-0"><i class="fas fa-tractor me-2"></i>Farmers Dashboard</h4><a href="<?= BASE_URL ?>farmers/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Farmer</a></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Total Farmers</small><h3 class="mb-0 mt-1"><?= number_format($statistics['total_farmers'] ?? 0) ?></h3></div><div class="bg-success-subtle p-3 rounded"><i class="fas fa-users fa-2x text-success"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">States</small><h3 class="mb-0 mt-1"><?= number_format($statistics['unique_states'] ?? 0) ?></h3></div><div class="bg-warning-subtle p-3 rounded"><i class="fas fa-map-marker-alt fa-2x text-warning"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Districts</small><h3 class="mb-0 mt-1"><?= number_format($statistics['unique_districts'] ?? 0) ?></h3></div><div class="bg-info-subtle p-3 rounded"><i class="fas fa-city fa-2x text-info"></i></div></div></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body aps-cp-card-body"><div class="d-flex justify-content-between"><div><small class="text-muted text-uppercase fw-bold">Registered</small><h3 class="mb-0 mt-1"><?= number_format($statistics['farmers_with_state'] ?? 0) ?></h3></div><div class="bg-primary-subtle p-3 rounded"><i class="fas fa-tractor fa-2x text-primary"></i></div></div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Farmers</h6><a href="<?= BASE_URL ?>farmers/list" class="btn btn-outline-primary btn-sm">View All</a></div>
        <div class="card-body p-0">
            <?php if (empty($farmers)): ?>
            <div class="text-center py-5"><i class="fas fa-tractor fa-3x text-muted mb-3"></i><p class="text-muted">No farmers registered yet</p><a href="<?= BASE_URL ?>farmers/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add First Farmer</a></div>
            <?php else: ?>
            <div class="table-responsive"><div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive"><thead class="table-light"><tr><th>Name</th><th>Phone</th><th>Area (sqft)</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody><?php foreach ($farmers as $f): ?><tr>
                    <td><?= htmlspecialchars($f['name'] ?? '-') ?><br><small class="text-muted"><?= htmlspecialchars($f['state_name'] ?? '') ?></small></td><td><?= htmlspecialchars($f['phone'] ?? '-') ?></td><td><?= number_format($f['total_area'] ?? 0, 2) ?></td>
                    <td><span class="badge bg-<?= ($f['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($f['status'] ?? '-') ?></span></td>
                    <td><a href="<?= BASE_URL ?>farmers/<?= $f['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                </tr><?php endforeach; ?></tbody></table></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
