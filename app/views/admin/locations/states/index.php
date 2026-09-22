<?php
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-map-marked-alt"></i> States Management</h2>
                <div>
                    <a href="<?= BASE_URL ?>/admin/locations/states/create" class="btn btn-primary me-2">
                        <i class="fas fa-plus"></i> Add State
                    </a>
                    <a href="<?= BASE_URL ?>/admin/locations/states/export" class="btn btn-outline-success me-2">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload"></i> Import CSV
                    </button>
                    <a href="<?= BASE_URL ?>/admin/locations/districts" class="btn btn-secondary">
                        <i class="fas fa-city"></i> Districts
                    </a>
                </div>
            </div>
            
            <!-- Search & Filter Form -->
            <div class="card mb-4">
                <div class="card-body aps-cp-card-body">
                    <form method="GET" action="<?= BASE_URL ?>/admin/locations/states" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by name or code...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All</option>
                                <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i> Filter</button>
                            <a href="<?= BASE_URL ?>/admin/locations/states" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body aps-cp-card-body">
                            <h5 class="card-title">Total States</h5>
                            <h3><?= $total ?? count($states) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body aps-cp-card-body">
                            <h5 class="card-title">Total Districts</h5>
                            <h3><?= array_sum(array_column($states, 'district_count')) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body aps-cp-card-body">
                            <h5 class="card-title">Total Colonies</h5>
                            <h3>
                                <?php 
                                $totalColonies = 0;
                                foreach ($states as $state) {
                                    $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE d.state_id = ? AND c.is_active = 1");
                                    $stmt->execute([$state['id']]);
                                    $totalColonies += $stmt->fetch()['count'];
                                }
                                echo $totalColonies;
                                ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Actions Form -->
            <form method="POST" action="<?= BASE_URL ?>/admin/locations/states/bulk-action" id="bulkActionForm" onsubmit="return confirmBulkAction()">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" id="bulkActionInput">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-success" onclick="setBulkAction('activate')"><i class="fas fa-check"></i> Activate</button>
                        <button type="button" class="btn btn-outline-warning" onclick="setBulkAction('deactivate')"><i class="fas fa-ban"></i> Deactivate</button>
                        <button type="button" class="btn btn-outline-danger" onclick="setBulkAction('delete')"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                    <span class="text-muted small" id="selectedCount">0 selected</span>
                </div>

            <!-- States Table -->
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="mb-0">States</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                    </th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Districts</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($states ?? [])): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No states found</h5>
                                        <p class="text-muted mb-3">Add states to start building your location hierarchy.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($states as $state): ?>
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="<?= e($state['id']) ?>" class="rowCheckbox" onchange="updateSelectedCount()"></td>
                                    <td><?= e($state['id']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($state['name'] ?? '') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($state['code'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/locations/districts?state_id=<?= e($state['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <?= e($state['district_count']) ?> Districts
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $state['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $state['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/admin/locations/districts?state_id=<?= e($state['id']) ?>" class="btn btn-outline-info" title="View Districts">
                                                <i class="fas fa-city"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/locations/states/edit/<?= e($state['id']) ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/locations/states/delete/<?= e($state['id']) ?>" class="btn btn-outline-danger" title="Delete" data-aps-confirm="Are you sure?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (($totalPages ?? 1) > 1): ?>
                    <nav aria-label="States pagination">
                        <ul class="pagination justify-content-center">
                            <?php 
                            $prevPage = max(1, ($page ?? 1) - 1);
                            $nextPage = min($totalPages ?? 1, ($page ?? 1) + 1);
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $queryString = $queryParams ? '&' . http_build_query($queryParams) : '';
                            ?>
                            <li class="page-item <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/locations/states?page=<?= $prevPage ?><?= $queryString ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= ($totalPages ?? 1); $i++): ?>
                                <?php if ($i == 1 || $i == ($totalPages ?? 1) || abs($i - ($page ?? 1)) <= 2): ?>
                                <li class="page-item <?= ($page ?? 1) == $i ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/admin/locations/states?page=<?= $i ?><?= $queryString ?>"><?= $i ?></a>
                                </li>
                                <?php elseif ($i == ($page ?? 1) - 3 || $i == ($page ?? 1) + 3): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page ?? 1) >= ($totalPages ?? 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/admin/locations/states?page=<?= $nextPage ?><?= $queryString ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</form>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel"><i class="fas fa-upload"></i> Import States CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/locations/states/import" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">CSV File</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        <div class="form-text">Format: Name,Code,Status (Active/Inactive). Example: "Uttar Pradesh,UP,Active"</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setBulkAction(action) {
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkActionForm').submit();
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.rowCheckbox:checked').length;
    document.getElementById('selectedCount').textContent = count + ' selected';
}

function confirmBulkAction() {
    const action = document.getElementById('bulkActionInput').value;
    const count = document.querySelectorAll('.rowCheckbox:checked').length;
    if (!action || count === 0) {
        alert('Please select an action and at least one item');
        return false;
    }
    return confirm('Apply "' + action + '" to ' + count + ' selected item(s)?');
}
</script>


