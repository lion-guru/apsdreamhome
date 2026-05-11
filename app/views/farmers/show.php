<?php $pageTitle = 'Farmer Details'; ?>
<?php $farmer = $farmer ?? null; $crops = $crops ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>farmers/dashboard">Farmers</a></li><li class="breadcrumb-item active">Farmer Details</li></ol></nav>
    <?php if (!$farmer): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">Farmer not found</h6><a href="<?= BASE_URL ?>farmers/list" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to List</a></div></div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1"><?= htmlspecialchars($farmer['name'] ?? '-') ?></h4><small class="text-muted">Farmer since <?= htmlspecialchars($farmer['created_at'] ?? 'N/A') ?></small></div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>farmers/edit/<?= $farmer['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="<?= BASE_URL ?>farmers/list" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Personal Info</h6></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><th>Name</th><td><?= htmlspecialchars($farmer['name'] ?? '-') ?></td></tr>
                        <tr><th>Phone</th><td><?= htmlspecialchars($farmer['phone'] ?? '-') ?></td></tr>
                        <tr><th>Village</th><td><?= htmlspecialchars($farmer['village'] ?? '-') ?></td></tr>
                        <tr><th>Land Owned</th><td><?= number_format($farmer['land_acres'] ?? 0, 2) ?> acres</td></tr>
                        <tr><th>Primary Crop</th><td><?= htmlspecialchars($farmer['primary_crop'] ?? 'N/A') ?></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-<?= ($farmer['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($farmer['status'] ?? '-') ?></span></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-seedling me-2"></i>Crop History</h6></div>
                <div class="card-body">
                    <?php if (empty($crops)): ?>
                    <p class="text-muted text-center py-3 mb-0">No crop history recorded yet</p>
                    <?php else: ?>
                    <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Crop</th><th>Season</th><th>Yield</th><th>Year</th></tr></thead>
                        <tbody><?php foreach ($crops as $c): ?><tr><td><?= htmlspecialchars($c['name'] ?? '-') ?></td><td><?= htmlspecialchars($c['season'] ?? '-') ?></td><td><?= htmlspecialchars($c['yield'] ?? '-') ?></td><td><?= htmlspecialchars($c['year'] ?? '-') ?></td></tr><?php endforeach; ?></tbody></table></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($farmer['notes'])): ?>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h6></div>
        <div class="card-body"><p class="mb-0"><?= nl2br(htmlspecialchars($farmer['notes'])) ?></p></div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
