<?php
$colonies = $colonies ?? [];
$states   = $states ?? [];
$districts = $districts ?? [];
$totalValue = $totalValue ?? 0;
$selectedState = $selectedState ?? null;
$selectedDistrict = $selectedDistrict ?? null;

$total    = count($colonies);
$active   = 0; $inactive = 0; $totalPlots = 0; $availPlots = 0;
foreach ($colonies as $c) {
    if (($c['is_active'] ?? 0)) $active++; else $inactive++;
    $totalPlots += (int)($c['total_plots'] ?? 0);
    $availPlots += (int)($c['available_plots'] ?? 0);
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-city text-primary me-2"></i>Colony Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">Colonies</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/colony-pipeline"      class="btn btn-outline-primary btn-sm"><i class="fas fa-sitemap me-1"></i>Pipeline</a>
        <a href="<?= BASE_URL ?>/admin/colony-feasibility"   class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-bar me-1"></i>Feasibility</a>
        <a href="<?= BASE_URL ?>/admin/legal-colony-pipeline" class="btn btn-outline-warning btn-sm"><i class="fas fa-gavel me-1"></i>Legal</a>
        <a href="<?= BASE_URL ?>/admin/plots"                 class="btn btn-outline-info btn-sm"><i class="fas fa-th me-1"></i>Plots</a>
        <a href="<?= BASE_URL ?>/admin/colonies/create"       class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Colony</a>
    </div>
</div>

<!-- State/District Filter Form -->
<form method="GET" class="row g-2 mb-4 align-items-end">
    <div class="col-md-3">
        <label class="form-label small mb-1">State</label>
        <select name="state" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All States</option>
            <?php foreach ($states as $state): ?>
                <option value="<?= htmlspecialchars($state) ?>" <?= $selectedState === $state ? 'selected' : '' ?>>
                    <?= htmlspecialchars($state) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small mb-1">District</label>
        <select name="district_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Districts</option>
            <?php foreach ($districts as $d): ?>
                <option value="<?= $d->id ?>" <?= $selectedDistrict == $d->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-1 d-none d-md-block"> </label>
        <a href="<?= BASE_URL ?>/admin/colonies" class="btn btn-outline-secondary btn-sm w-100">Clear Filters</a>
    </div>
</form>

<?php if ($msg = \App\Core\Session::flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($msg = \App\Core\Session::flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-city text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $total ?></div>
                <div class="text-muted small">Total Colonies</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $active ?></div>
                <div class="text-muted small">Active</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-th text-info"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= number_format($totalPlots) ?></div>
                <div class="text-muted small">Total Plots</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #20c997;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-home text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= number_format($availPlots) ?></div>
                <div class="text-muted small">Available Plots</div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-bold">All Colonies</h6>
            <div class="d-flex gap-2">
                <input type="text" id="colonySearch" class="form-control form-control-sm" placeholder="Search colonies..." style="width:200px;">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($colonies)): ?>
            <div class="text-center py-5">
                <i class="fas fa-city fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h5 class="text-muted">No colonies found</h5>
                <p class="text-muted small mb-3">Create your first colony to start managing plots and inventory.</p>
                <a href="<?= BASE_URL ?>/admin/colonies/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Colony
                </a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="coloniesTable">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-4">Colony Name</th>
                        <th>District / State</th>
                        <th class="text-center">Plots</th>
                        <th class="text-center">Available</th>
                        <th>Starting Price</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($colonies as $c): ?>
                    <tr class="colony-row">
                        <td class="px-4">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($c['name'] ?? '') ?></div>
                            <small class="text-muted font-monospace"><?= htmlspecialchars($c['slug'] ?? '') ?></small>
                        </td>
                        <td>
                            <div class="small"><?= htmlspecialchars($c['district_name'] ?? '') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($c['state_name'] ?? '') ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-15 text-primary fw-bold px-2"><?= (int)($c['total_plots'] ?? 0) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-15 text-success fw-bold px-2"><?= (int)($c['available_plots'] ?? 0) ?></span>
                        </td>
                        <td class="fw-semibold">₹<?= number_format($c['starting_price'] ?? 0) ?></td>
                        <td class="text-center">
                            <?php if ($c['is_active'] ?? 0): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/admin/colonies/<?= e($c['id']) ?>" class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/colonies/<?= e($c['id']) ?>/edit" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/colonies/<?= e($c['id']) ?>/plots" class="btn btn-outline-info" title="Plots">
                                    <i class="fas fa-map"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= e($c['id']) ?>" class="btn btn-outline-success" title="Pipeline">
                                    <i class="fas fa-sitemap"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($c['slug'] ?? '') ?>" class="btn btn-outline-secondary" target="_blank" title="Public Page">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var searchInput = document.getElementById('colonySearch');
    if (!searchInput) return;
    searchInput.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#coloniesTable .colony-row').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
})();
</script>
