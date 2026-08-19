<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php
$page_title = 'Property Management';
$active_page = 'properties';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Property Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo BASE_URL; ?>/admin/properties/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Property
            </a>
            <a href="<?= BASE_URL ?>admin/properties" class="btn btn-warning ms-2 shadow-sm fw-bold">
                <i class="fas fa-robot me-1"></i> Fetch Web Listings
            </a>
            <a href="<?= BASE_URL ?>admin/properties/export?format=csv&type=properties" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="site_id" class="form-select">
                    <option value="">All Sites</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= $site['id'] ?>" <?= ($filters['site_id'] ?? '') == $site['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($site['site_name'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search properties..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="sold" <?= ($filters['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option>
                    <option value="rented" <?= ($filters['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Rented</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="apartment" <?= ($filters['type'] ?? '') === 'apartment' ? 'selected' : '' ?>>Apartment</option>
                    <option value="house" <?= ($filters['type'] ?? '') === 'house' ? 'selected' : '' ?>>House</option>
                    <option value="land" <?= ($filters['type'] ?? '') === 'land' ? 'selected' : '' ?>>Land</option>
                    <option value="commercial" <?= ($filters['type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Bar (hidden by default) -->
<div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display: none;">
    <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
        <span class="fw-semibold"><span id="selectedCount">0</span> selected</span>
        <select id="bulkStatus" class="form-select form-select-sm" style="width: auto; display: inline-block;">
            <option value="available">Available</option>
            <option value="sold">Sold</option>
            <option value="reserved">Reserved</option>
            <option value="under_maintenance">Under Maintenance</option>
        </select>
        <input type="text" id="bulkNotes" class="form-control form-control-sm" style="width: 200px; display: inline-block;" placeholder="Notes (optional)">
        <button type="button" class="btn btn-sm btn-warning" id="bulkApply">Apply Status</button>
    </div>
</div>

<!-- Properties Table -->
<div class="card aps-cp-card">
    <div class="card-body aps-cp-card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Property</th>
                        <th>Site</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Area</th>
                        <th>Bed/Bath</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-home fa-3x text-muted mb-3" class="style-82835"></i>
                                <h5 class="text-muted">No properties found</h5>
                                <p class="text-muted mb-3">Add your first property listing to start showcasing plots, apartments, and commercial spaces to potential buyers.</p>
                                <a href="<?= BASE_URL ?>/admin/properties/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Property
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                            <tr data-property-id="<?= $property['id'] ?>">
                                <td><input type="checkbox" class="form-check-input property-checkbox" value="<?= $property['id'] ?>"></td>
                                <td>
                                    <strong><?= htmlspecialchars($property['title'] ?? '') ?></strong>
                                    <?php if (!empty($property['description'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($property['description'], 0, 100)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($property['site_name'] ?? '') ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($property['site_location'] ?? '') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars(ucfirst($property['type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($property['price'] > 0): ?>
                                        ₹<?= number_format(floatval($property['price'] ?? 0), 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not Set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= number_format(floatval($property['area'] ?? 0), 2) ?> <?= htmlspecialchars($property['area_unit'] ?? 'sqft') ?>
                                </td>
                                <td>
                                    <?= $property['bedrooms'] ?>B / <?= $property['bathrooms'] ?>B
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'active' => 'success',
                                        'sold' => 'danger',
                                        'rented' => 'warning',
                                        'pending' => 'secondary'
                                    ];
                                    $color = $statusColors[$property['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= htmlspecialchars(ucfirst($property['status'])) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($property['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo BASE_URL; ?>/admin/properties/<?= $property['id'] ?>" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/properties/<?= $property['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($property['status'] === 'active'): ?>
                                            <button type="button" class="btn btn-outline-success" onclick="bookProperty(<?= $property['id'] ?>)" title="Book Property">
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
        <?php if (($total_pages ?? 0) > 1): ?>
            <nav aria-label="Property pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= ($total_pages ?? 1); $i++): ?>
                        <li class="page-item <?= $i === ($current_page ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&type=<?= urlencode($filters['type'] ?? '') ?>&site_id=<?= urlencode($filters['site_id'] ?? '') ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this property? This action cannot be undone.</p>
                <p class="text-danger"><strong>Note:</strong> You cannot delete a property that has existing bookings.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" action="<?= BASE_URL ?>/admin/properties/0/destroy" class="style-26772">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger">Delete Property</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function bookProperty(id) {
    window.location.href = '<?= BASE_URL ?>/admin/bookings/create?property_id=' + id;
}
function confirmDelete(id) {
    var form = document.getElementById('deleteForm');
    form.action = '<?= BASE_URL ?>/admin/properties/' + id + '/destroy';
    var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Bulk Actions
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.property-checkbox');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countEl = document.getElementById('selectedCount');
    const bulkStatus = document.getElementById('bulkStatus');
    const bulkNotes = document.getElementById('bulkNotes');
    const bulkApply = document.getElementById('bulkApply');

    if (!selectAll || !bulkBar) return;

    function getSelected() {
        return [...checkboxes].filter(cb => cb.checked).map(cb => parseInt(cb.value));
    }

    function updateUI() {
        const count = getSelected().length;
        if (countEl) countEl.textContent = count;
        bulkBar.style.display = count > 0 ? 'block' : 'none';
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateUI();
    });
    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));

    bulkApply.addEventListener('click', function() {
        const ids = getSelected();
        const status = bulkStatus.value;
        const notes = bulkNotes.value;
        if (!ids.length) return showToast('No properties selected', 'info');
        apsConfirm('Update status of ' + ids.length + ' propert(ies) to ' + status + '?').then(function(ok) {
            if (!ok) return;

        showLoader();
        fetch('<?= BASE_URL ?>/admin/properties/bulk-update', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'},
            body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>&property_ids[]=' + ids.join('&property_ids[]=') + '&status=' + encodeURIComponent(status) + '&notes=' + encodeURIComponent(notes)
        });
        }).then(r => r.json()).then(d => {
            if (d.success) location.reload();
            .catch(err => console.error('Request failed:', err));
            else showToast(d.error || 'Failed', 'danger');
        ).finally(() => hideLoader());
    });
});
</script>

