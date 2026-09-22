<?php
$plotList      = $plots ?? [];
$total         = count($plotList);
$availableCount = 0; $bookedCount = 0; $soldCount = 0; $holdCount = 0;
foreach ($plotList as $pl) {
    $st = strtolower($pl['status'] ?? 'available');
    if ($st === 'available')               $availableCount++;
    elseif ($st === 'booked')              $bookedCount++;
    elseif ($st === 'sold')               $soldCount++;
    elseif ($st === 'hold' || $st === 'reserved') $holdCount++;
}
$totalValue = array_sum(array_column($plotList, 'total_price'));
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-th text-primary me-2"></i>Plots Inventory</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/colony-pipeline">Colony Pipeline</a></li>
                <li class="breadcrumb-item active">Plots</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/plots/map"          class="btn btn-outline-info btn-sm"><i class="fas fa-map me-1"></i>Map View</a>
        <a href="<?= BASE_URL ?>/admin/plots/aging-report" class="btn btn-outline-secondary btn-sm"><i class="fas fa-hourglass-half me-1"></i>Aging</a>
        <a href="<?= BASE_URL ?>/admin/plots/batch-pricing" class="btn btn-outline-warning btn-sm"><i class="fas fa-tags me-1"></i>Batch Pricing</a>
        <a href="<?= BASE_URL ?>/admin/plots/categories"   class="btn btn-outline-secondary btn-sm"><i class="fas fa-layer-group me-1"></i>Categories</a>
        <a href="<?= BASE_URL ?>/admin/plots/create"       class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Plot</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:50px;height:50px;">
                    <i class="fas fa-th text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= number_format($total) ?></div>
                <div class="text-muted small fw-semibold">Total Plots</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:50px;height:50px;">
                    <i class="fas fa-check text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= number_format($availableCount) ?></div>
                <div class="text-muted small fw-semibold">Available</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #ffc107;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:50px;height:50px;">
                    <i class="fas fa-calendar-check text-warning"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= number_format($bookedCount) ?></div>
                <div class="text-muted small fw-semibold">Booked</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #dc3545;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:50px;height:50px;">
                    <i class="fas fa-handshake text-danger"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= number_format($soldCount) ?></div>
                <div class="text-muted small fw-semibold">Sold</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:50px;height:50px;">
                    <i class="fas fa-rupee-sign text-info"></i>
                </div>
                <div class="fw-bold fs-4 text-dark">₹<?= number_format($totalValue / 10000000, 1) ?>Cr</div>
                <div class="text-muted small fw-semibold">Total Value</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="d-flex gap-2 flex-wrap mb-4">
    <a href="<?= BASE_URL ?>/admin/colony-pipeline" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-sitemap me-1"></i>Colony Pipeline
    </a>
    <a href="<?= BASE_URL ?>/admin/colonies" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-city me-1"></i>Colonies
    </a>
    <a href="<?= BASE_URL ?>/admin/noc-registry" class="btn btn-sm btn-outline-warning">
        <i class="fas fa-file-contract me-1"></i>NOC & Registry
    </a>
    <a href="<?= BASE_URL ?>/admin/bookings" class="btn btn-sm btn-outline-success">
        <i class="fas fa-calendar-plus me-1"></i>Bookings
    </a>
    <a href="<?= BASE_URL ?>/admin/mlm-realestate/bookings" class="btn btn-sm btn-outline-info">
        <i class="fas fa-network-wired me-1"></i>MLM Bookings
    </a>
</div>

<!-- Plots Table Card -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="mb-0 fw-bold">All Plots</h6>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-secondary plot-filter-btn active" data-status="all">All <span class="badge bg-white text-secondary ms-1"><?= $total ?></span></button>
            <button type="button" class="btn btn-outline-secondary plot-filter-btn" data-status="available">Available <span class="badge bg-success ms-1"><?= $availableCount ?></span></button>
            <button type="button" class="btn btn-outline-secondary plot-filter-btn" data-status="booked">Booked <span class="badge bg-warning text-dark ms-1"><?= $bookedCount ?></span></button>
            <button type="button" class="btn btn-outline-secondary plot-filter-btn" data-status="sold">Sold <span class="badge bg-danger ms-1"><?= $soldCount ?></span></button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($plots)): ?>
            <div class="text-center py-5">
                <i class="fas fa-th-large fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h5 class="text-muted fw-semibold">No plots found</h5>
                <p class="text-muted small mb-3">Set up a colony and generate plots from the Colony Pipeline.</p>
                <a href="<?= BASE_URL ?>/admin/colony-pipeline" class="btn btn-sm btn-primary me-2">
                    <i class="fas fa-sitemap me-1"></i>Colony Pipeline
                </a>
                <a href="<?= BASE_URL ?>/admin/plots/create" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i>Add Plot Manually
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="px-4">Plot No.</th>
                            <th>Colony</th>
                            <th>Size (sqft)</th>
                            <th>Price</th>
                            <th>Rate/sqft</th>
                            <th>Status</th>
                            <th>Owner / Buyer</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plots as $plot): ?>
                        <tr data-plot-status="<?= htmlspecialchars(strtolower($plot['status'] ?? 'available')) ?>">
                            <td class="px-4">
                                <span class="fw-bold text-primary"><?= htmlspecialchars($plot['plot_number'] ?? $plot['id']) ?></span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($plot['colony_name'] ?? '—') ?></div>
                            </td>
                            <td class="fw-semibold"><?= number_format($plot['area_sqft'] ?? 0) ?></td>
                            <td class="fw-bold">₹<?= number_format($plot['total_price'] ?? $plot['price'] ?? 0) ?></td>
                            <td class="small text-muted">
                                <?php $sqft = (float)($plot['area_sqft'] ?? 0); $price = (float)($plot['total_price'] ?? $plot['price'] ?? 0); ?>
                                <?= $sqft > 0 ? '₹' . number_format($price / $sqft, 0) : '—' ?>
                            </td>
                            <td>
                                <?php
                                $st = strtolower($plot['status'] ?? 'available');
                                $sc = ['available'=>'success','booked'=>'warning','sold'=>'danger','hold'=>'info','reserved'=>'info'];
                                $stColor = $sc[$st] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $stColor ?>"><?= ucfirst($plot['status'] ?? 'available') ?></span>
                            </td>
                            <td class="small"><?= htmlspecialchars($plot['customer_name'] ?? '—') ?></td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/plots/<?= $plot['id'] ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/plots/<?= $plot['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($st === 'available'): ?>
                                    <a href="<?= BASE_URL ?>/admin/bookings/create?plot_id=<?= $plot['id'] ?>" class="btn btn-outline-success" title="Book Plot">
                                        <i class="fas fa-calendar-plus"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="plotFilterEmpty" style="display:none;">
                            <td colspan="8" class="text-center py-4 text-muted">No plots match this filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var buttons = document.querySelectorAll('.plot-filter-btn');
    if (!buttons.length) return;
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var status = btn.getAttribute('data-status');
            buttons.forEach(function (b) {
                var isActive = b === btn;
                b.classList.toggle('active', isActive);
                b.classList.toggle('btn-secondary', isActive);
                b.classList.toggle('btn-outline-secondary', !isActive);
            });
            var visible = 0;
            document.querySelectorAll('tr[data-plot-status]').forEach(function (row) {
                var show = status === 'all' || row.getAttribute('data-plot-status') === status;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            var emptyRow = document.getElementById('plotFilterEmpty');
            if (emptyRow) emptyRow.style.display = visible === 0 ? '' : 'none';
        });
    });
})();
</script>