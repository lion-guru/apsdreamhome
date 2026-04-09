

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-th"></i> Plots Management</h2>
                <div>
                    <a href="/admin/plots/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Plot
                    </a>
                    <a href="/admin/plots/export" class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <a href="/admin/locations/colonies" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Colonies
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Plots</h5>
                            <h3><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Available</h5>
                            <h3><?php echo $stats['by_status']['available'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Booked</h5>
                            <h3><?php echo $stats['by_status']['booked'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Sold</h5>
                            <h3><?php echo $stats['by_status']['sold'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/plots">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="colony_id" class="form-label">Filter by Colony</label>
                                <select class="form-select" id="colony_id" name="colony_id">
                                    <option value="">All Colonies</option>
                                    <?php foreach ($colonies as $colony): ?>
                                        <option value="<?php echo $colony['id']; ?>" <?php echo (isset($_GET['colony_id']) && $_GET['colony_id'] == $colony['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($colony['state_name'] . ' > ' . $colony['district_name'] . ' > ' . $colony['colony_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="available" <?php echo (isset($_GET['status']) && $_GET['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                    <option value="booked" <?php echo (isset($_GET['status']) && $_GET['status'] == 'booked') ? 'selected' : ''; ?>>Booked</option>
                                    <option value="sold" <?php echo (isset($_GET['status']) && $_GET['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                    <option value="hold" <?php echo (isset($_GET['status']) && $_GET['status'] == 'hold') ? 'selected' : ''; ?>>Hold</option>
                                    <option value="reserved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                                    <option value="under_construction" <?php echo (isset($_GET['status']) && $_GET['status'] == 'under_construction') ? 'selected' : ''; ?>>Under Construction</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="plot_type" class="form-label">Plot Type</label>
                                <select class="form-select" id="plot_type" name="plot_type">
                                    <option value="">All Types</option>
                                    <option value="residential" <?php echo (isset($_GET['plot_type']) && $_GET['plot_type'] == 'residential') ? 'selected' : ''; ?>>Residential</option>
                                    <option value="commercial" <?php echo (isset($_GET['plot_type']) && $_GET['plot_type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                                    <option value="industrial" <?php echo (isset($_GET['plot_type']) && $_GET['plot_type'] == 'industrial') ? 'selected' : ''; ?>>Industrial</option>
                                    <option value="mixed" <?php echo (isset($_GET['plot_type']) && $_GET['plot_type'] == 'mixed') ? 'selected' : ''; ?>>Mixed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Apply Filter
                                    </button>
                                    <a href="/admin/plots" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-warning" onclick="showBulkStatusModal()">
                                        <i class="fas fa-edit"></i> Bulk Update
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Plots Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Plots List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="plotsTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                    <th>Plot Number</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Area (Sqft)</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Customer</th>
                                    <th>Features</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($plots as $plot): ?>
                                <tr>
                                    <td><input type="checkbox" class="plot-checkbox" value="<?php echo $plot['id']; ?>"></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($plot['plot_number']); ?></strong>
                                        <?php if ($plot['is_featured']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo htmlspecialchars($plot['colony_name']); ?><br>
                                            <?php echo htmlspecialchars($plot['district_name']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($plot['plot_type']); ?></span>
                                    </td>
                                    <td>
                                        <?php echo number_format($plot['area_sqft']); ?>
                                        <?php if ($plot['corner_plot']): ?>
                                            <i class="fas fa-crown text-warning" title="Corner Plot"></i>
                                        <?php endif; ?>
                                        <?php if ($plot['park_facing']): ?>
                                            <i class="fas fa-tree text-success" title="Park Facing"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>₹<?php echo number_format($plot['total_price']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($plot['status']) {
                                                'available' => 'success',
                                                'booked' => 'warning',
                                                'sold' => 'danger',
                                                'hold' => 'secondary',
                                                'reserved' => 'info',
                                                'under_construction' => 'dark',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($plot['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($plot['customer_name']): ?>
                                            <small>
                                                <?php echo htmlspecialchars($plot['customer_name']); ?><br>
                                                <?php echo htmlspecialchars($plot['customer_phone'] ?? ''); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo htmlspecialchars($plot['facing'] ?? ''); ?> Facing<br>
                                            <?php echo $plot['road_width_ft']; ?>ft Road
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/plots/show/<?php echo $plot['id']; ?>" class="btn btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/plots/edit/<?php echo $plot['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning" onclick="quickStatusUpdate(<?php echo $plot['id']; ?>, '<?php echo $plot['status']; ?>')" title="Quick Status">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <a href="/admin/plots/delete/<?php echo $plot['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Status Update Modal -->
<div class="modal fade" id="bulkStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Status Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="bulk_status" class="form-label">New Status</label>
                    <select class="form-select" id="bulk_status">
                        <option value="">Select Status</option>
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="sold">Sold</option>
                        <option value="hold">Hold</option>
                        <option value="reserved">Reserved</option>
                        <option value="under_construction">Under Construction</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bulk_reason" class="form-label">Reason</label>
                    <textarea class="form-control" id="bulk_reason" rows="2" placeholder="Reason for status change..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="performBulkUpdate()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Status Update Modal -->
<div class="modal fade" id="quickStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Plot Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="quick_plot_id">
                <div class="mb-3">
                    <label for="quick_status" class="form-label">New Status</label>
                    <select class="form-select" id="quick_status">
                        <option value="">Select Status</option>
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="sold">Sold</option>
                        <option value="hold">Hold</option>
                        <option value="reserved">Reserved</option>
                        <option value="under_construction">Under Construction</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="quick_reason" class="form-label">Reason</label>
                    <textarea class="form-control" id="quick_reason" rows="2" placeholder="Reason for status change..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="performQuickUpdate()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.plot-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
}

function showBulkStatusModal() {
    const selected = document.querySelectorAll('.plot-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select at least one plot');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('bulkStatusModal'));
    modal.show();
}

function performBulkUpdate() {
    const selected = document.querySelectorAll('.plot-checkbox:checked');
    const plotIds = Array.from(selected).map(cb => cb.value);
    const status = document.getElementById('bulk_status').value;
    const reason = document.getElementById('bulk_reason').value;
    
    if (!status) {
        alert('Please select a status');
        return;
    }
    
    fetch('/admin/plots/bulk-status-update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `plot_ids=${plotIds.join(',')}&status=${status}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function quickStatusUpdate(plotId, currentStatus) {
    document.getElementById('quick_plot_id').value = plotId;
    document.getElementById('quick_status').value = currentStatus;
    
    const modal = new bootstrap.Modal(document.getElementById('quickStatusModal'));
    modal.show();
}

function performQuickUpdate() {
    const plotId = document.getElementById('quick_plot_id').value;
    const status = document.getElementById('quick_status').value;
    const reason = document.getElementById('quick_reason').value;
    
    if (!status) {
        alert('Please select a status');
        return;
    }
    
    fetch(`/admin/plots/${plotId}/status`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `status=${status}&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>


