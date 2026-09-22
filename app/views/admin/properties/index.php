<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php
$page_title = 'Property Management';
$active_page = 'properties';
$properties = $properties ?? [];
$sites       = $sites ?? [];
$filters     = $filters ?? [];
$total_pages   = $total_pages ?? 1;
$current_page  = $current_page ?? 1;
$total_count   = $total_count ?? count($properties);

/* --- Quick stats from the list --- */
$statActive  = 0; $statSold = 0; $statRented = 0; $statPending = 0;
foreach ($properties as $p) {
    $s = strtolower($p['status'] ?? 'active');
    if ($s === 'active')   $statActive++;
    elseif ($s === 'sold') $statSold++;
    elseif ($s === 'rented') $statRented++;
    else $statPending++;
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold">
            <i class="fas fa-home text-primary me-2"></i>Property Management
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">Properties</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/plots" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-th me-1"></i>Plots
        </a>
        <a href="<?= BASE_URL ?>/admin/colonies" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-city me-1"></i>Colonies
        </a>
        <a href="<?= BASE_URL ?>/admin/noc-registry" class="btn btn-outline-warning btn-sm">
            <i class="fas fa-file-contract me-1"></i>NOC & Registry
        </a>
        <a href="<?= BASE_URL ?>/admin/properties/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Property
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0d6efd !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary">
                    <i class="fas fa-home fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;">Total</div>
                    <div class="fw-bold fs-4 text-dark"><?= number_format($total_count) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #198754 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success">
                    <i class="fas fa-check-circle fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;">Active</div>
                    <div class="fw-bold fs-4 text-dark"><?= $statActive ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #dc3545 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-danger bg-opacity-10 p-3 text-danger">
                    <i class="fas fa-handshake fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;">Sold</div>
                    <div class="fw-bold fs-4 text-dark"><?= $statSold ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #ffc107 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning">
                    <i class="fas fa-key fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size:.7rem;">Rented</div>
                    <div class="fw-bold fs-4 text-dark"><?= $statRented ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Property name, desc..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Site</label>
                <select name="site_id" class="form-select form-select-sm">
                    <option value="">All Sites</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= $site['id'] ?>" <?= ($filters['site_id'] ?? '') == $site['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($site['site_name'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active"   <?= ($filters['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="sold"     <?= ($filters['status'] ?? '') === 'sold'     ? 'selected' : '' ?>>Sold</option>
                    <option value="rented"   <?= ($filters['status'] ?? '') === 'rented'   ? 'selected' : '' ?>>Rented</option>
                    <option value="pending"  <?= ($filters['status'] ?? '') === 'pending'  ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="apartment"  <?= ($filters['type'] ?? '') === 'apartment'  ? 'selected' : '' ?>>Apartment</option>
                    <option value="house"      <?= ($filters['type'] ?? '') === 'house'      ? 'selected' : '' ?>>House</option>
                    <option value="land"       <?= ($filters['type'] ?? '') === 'land'       ? 'selected' : '' ?>>Land</option>
                    <option value="commercial" <?= ($filters['type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Search
                </button>
                <a href="<?= BASE_URL ?>/admin/properties" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
                <a href="<?= BASE_URL ?>/admin/properties/export?format=csv&type=properties" class="btn btn-sm btn-outline-secondary" title="Export CSV">
                    <i class="fas fa-file-csv"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display:none; border-radius:10px; border-left:4px solid #0d6efd;">
    <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
        <span class="fw-semibold text-primary"><i class="fas fa-check-square me-1"></i><span id="selectedCount">0</span> selected</span>
        <select id="bulkStatus" class="form-select form-select-sm" style="width:auto;">
            <option value="active">Active</option>
            <option value="sold">Sold</option>
            <option value="reserved">Reserved</option>
            <option value="under_maintenance">Under Maintenance</option>
        </select>
        <input type="text" id="bulkNotes" class="form-control form-control-sm" style="width:200px;" placeholder="Notes (optional)">
        <button type="button" class="btn btn-sm btn-warning fw-semibold" id="bulkApply">
            <i class="fas fa-bolt me-1"></i>Apply Status
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" id="bulkCancel">Clear</button>
    </div>
</div>

<!-- Properties Table -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th width="30" class="px-3"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Property</th>
                        <th>Site</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Area</th>
                        <th>Bed/Bath</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-home fa-3x text-muted mb-3 d-block opacity-50"></i>
                                    <h5 class="text-muted fw-semibold">No properties found</h5>
                                    <p class="text-muted small mb-3">Add your first property listing to start showcasing to potential buyers.</p>
                                    <a href="<?= BASE_URL ?>/admin/properties/create" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>Add Property
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                            <tr data-property-id="<?= $property['id'] ?>">
                                <td class="px-3">
                                    <input type="checkbox" class="form-check-input property-checkbox" value="<?= $property['id'] ?>">
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($property['title'] ?? '') ?></div>
                                    <?php if (!empty($property['description'])): ?>
                                        <small class="text-muted"><?= htmlspecialchars(substr($property['description'], 0, 80)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small"><?= htmlspecialchars($property['site_name'] ?? '—') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($property['site_location'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-15 text-info fw-semibold">
                                        <?= htmlspecialchars(ucfirst($property['type'] ?? '')) ?>
                                    </span>
                                </td>
                                <td class="fw-semibold">
                                    <?php if (($property['price'] ?? 0) > 0): ?>
                                        ₹<?= number_format(floatval($property['price']), 0) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Not Set</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <?= number_format(floatval($property['area'] ?? 0), 0) ?>
                                    <?= htmlspecialchars($property['area_unit'] ?? 'sqft') ?>
                                </td>
                                <td class="small text-muted">
                                    <?= ($property['bedrooms'] ?? 0) ?>B / <?= ($property['bathrooms'] ?? 0) ?>B
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'active'  => 'success',
                                        'sold'    => 'danger',
                                        'rented'  => 'warning',
                                        'pending' => 'secondary',
                                        'reserved' => 'info',
                                    ];
                                    $color = $statusColors[$property['status'] ?? ''] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= htmlspecialchars(ucfirst($property['status'] ?? '')) ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d M Y', strtotime($property['created_at'] ?? 'now')) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/properties/<?= $property['id'] ?>" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/properties/<?= $property['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (($property['status'] ?? '') === 'active'): ?>
                                            <button type="button" class="btn btn-outline-success" onclick="bookProperty(<?= $property['id'] ?>)" title="Book">
                                                <i class="fas fa-calendar-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $property['id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($total_pages ?? 1) > 1): ?>
            <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Showing page <?= $current_page ?> of <?= $total_pages ?></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= ($total_pages ?? 1); $i++): ?>
                            <li class="page-item <?= $i === ($current_page ?? 1) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&type=<?= urlencode($filters['type'] ?? '') ?>&site_id=<?= urlencode($filters['site_id'] ?? '') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to delete this property?</p>
                <p class="text-danger small mb-0"><strong>Note:</strong> Cannot delete if bookings exist.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function bookProperty(id) {
    window.location.href = '<?= BASE_URL ?>/admin/bookings/create?property_id=' + id;
}
function confirmDelete(id) {
    document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/properties/' + id + '/destroy';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    var selectAll  = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.property-checkbox');
    var bulkBar    = document.getElementById('bulkActionsBar');
    var countEl    = document.getElementById('selectedCount');
    var bulkStatus = document.getElementById('bulkStatus');
    var bulkNotes  = document.getElementById('bulkNotes');
    var bulkApply  = document.getElementById('bulkApply');
    var bulkCancel = document.getElementById('bulkCancel');

    if (!selectAll || !bulkBar) return;

    function getSelected() {
        return Array.from(checkboxes).filter(function(cb) { return cb.checked; }).map(function(cb) { return parseInt(cb.value); });
    }

    function updateUI() {
        var count = getSelected().length;
        if (countEl) countEl.textContent = count;
        bulkBar.style.display = count > 0 ? 'block' : 'none';
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
        updateUI();
    });
    checkboxes.forEach(function(cb) { cb.addEventListener('change', updateUI); });

    if (bulkCancel) {
        bulkCancel.addEventListener('click', function () {
            checkboxes.forEach(function(cb) { cb.checked = false; });
            selectAll.checked = false;
            updateUI();
        });
    }

    if (bulkApply) {
        bulkApply.addEventListener('click', function () {
            var ids    = getSelected();
            var status = bulkStatus.value;
            var notes  = bulkNotes ? bulkNotes.value : '';
            if (!ids.length) { showToast('No properties selected', 'info'); return; }

            if (typeof apsConfirm === 'function') {
                apsConfirm('Update status of ' + ids.length + ' propert(ies) to "' + status + '"?').then(function (ok) {
                    if (!ok) return;
                    doUpdate(ids, status, notes);
                });
            } else {
                if (!confirm('Update status of ' + ids.length + ' propert(ies) to "' + status + '"?')) return;
                doUpdate(ids, status, notes);
            }
        });
    }

    function doUpdate(ids, status, notes) {
        if (typeof showLoader === 'function') showLoader();
        var body = 'csrf_token=<?= rawurlencode($_SESSION['csrf_token'] ?? '') ?>'
            + '&property_ids[]=' + ids.join('&property_ids[]=')
            + '&status=' + encodeURIComponent(status)
            + '&notes='  + encodeURIComponent(notes);

        fetch('<?= BASE_URL ?>/admin/properties/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>'
            },
            body: body
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d && d.success) {
                location.reload();
            } else {
                if (typeof showToast === 'function') showToast(d.error || 'Bulk update failed', 'danger');
            }
        })
        .catch(function(err) {
            console.error('Bulk update error:', err);
            if (typeof showToast === 'function') showToast('Request failed', 'danger');
        })
        .finally(function() {
            if (typeof hideLoader === 'function') hideLoader();
        });
    }
});
</script>
