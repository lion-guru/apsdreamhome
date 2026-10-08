

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building me-2"></i>User Properties
        </h1>
        <a href="<?php echo BASE_URL; ?>/list-property" target="_blank" class="btn btn-sm btn-primary">
            <i class="fas fa-external-link-alt me-1"></i> View Listing Page
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Property updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            Something went wrong. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Status Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo !$status ? 'active' : ''; ?>" href="?">
                All <span class="badge bg-secondary"><?php echo e($statusCounts['all']); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                Pending <span class="badge bg-warning"><?php echo e($statusCounts['pending']); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'verified' ? 'active' : ''; ?>" href="?status=verified">
                Verified <span class="badge bg-info"><?php echo e($statusCounts['verified']); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'approved' ? 'active' : ''; ?>" href="?status=approved">
                Approved <span class="badge bg-success"><?php echo e($statusCounts['approved']); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'rejected' ? 'active' : ''; ?>" href="?status=rejected">
                Rejected <span class="badge bg-danger"><?php echo e($statusCounts['rejected']); ?></span>
            </a>
        </li>
    </ul>

    <!-- Filters & Bulk Actions -->
    <div class="card mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" id="bulkActionForm" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, phone, email..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="plot" <?php echo $type === 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="house" <?php echo $type === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="flat" <?php echo $type === 'flat' ? 'selected' : ''; ?>>Flat</option>
                        <option value="shop" <?php echo $type === 'shop' ? 'selected' : ''; ?>>Shop</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="?" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Action Bar (hidden by default) -->
    <div id="bulkActionBar" class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4" style="display:none;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-bold" id="bulkSelectedCount">0</span> selected
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bolt me-1"></i> Bulk Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-success" href="#" data-bulk-action="approve"><i class="fas fa-check me-2"></i> Approve Selected</a></li>
                    <li><a class="dropdown-item text-danger" href="#" data-bulk-action="reject"><i class="fas fa-times me-2"></i> Reject Selected</a></li>
                    <li><a class="dropdown-item text-primary" href="#" data-bulk-action="verify"><i class="fas fa-check-circle me-2"></i> Verify Selected</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-secondary" href="#" data-bulk-action="export"><i class="fas fa-file-export me-2"></i> Export CSV</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="bulkClearBtn"><i class="fas fa-times me-1"></i> Clear Selection</button>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="card aps-cp-card">
        <div class="card-body aps-cp-card-body">
            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No properties found.</p>
                </div>
            <?php else: ?>
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/bulk-action" id="bulkActionFormSubmit">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width:50px;">
                                        <input type="checkbox" id="selectAll" class="form-check-input" aria-label="Select all">
                                    </th>
                                    <th>ID</th>
                                    <th>Property</th>
                                    <th>Owner</th>
                                    <th>Location</th>
                                    <th>Price</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $p): ?>
                                    <tr data-id="<?php echo e($p['id']); ?>">
                                        <td>
                                            <input type="checkbox" name="ids[]" value="<?php echo e($p['id']); ?>" class="form-check-input rowCheckbox">
                                        </td>
                                        <td><?php echo e($p['id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($p['name'] ?? ''); ?></strong>
                                            <br><small class="text-muted"><?php echo e($p['area_sqft']); ?> sq ft</small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($p['name'] ?? ''); ?></strong>
                                            <br><small><?php echo htmlspecialchars($p['phone'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($p['city_name'] ?? ''); ?>,
                                            <?php echo htmlspecialchars($p['district_name'] ?? ''); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($p['state_name'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <strong class="text-success">₹<?php echo number_format(floatval($p['price'] ?? 0)); ?></strong>
                                            <br><small class="text-muted"><?php echo ucfirst($p['price_type']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo ucfirst($p['property_type']); ?></span>
                                            <br><small class="text-muted"><?php echo ucfirst($p['listing_type']); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = match($p['status']) {
                                                'pending' => 'warning',
                                                'verified' => 'info',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'sold' => 'dark',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo ucfirst($p['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo BASE_URL; ?>/admin/user-properties/verify/<?php echo e($p['id']); ?>" class="btn btn-primary" title="View & Verify">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($p['status'] === 'pending'): ?>
                                                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/action" class="d-inline" data-aps-confirm="Approve this property?">
                                                                     <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                                     <input type="hidden" name="id" value="<?php echo e($p['id']); ?>">
                                                                     <input type="hidden" name="action" value="approve">
                                                                     <button type="submit" class="btn btn-success btn-sm" title="Approve" aria-label="Approve"><i class="fas fa-check"></i></button>
                                                                 </form>
                                                                 <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/action" class="d-inline" data-aps-confirm="Reject this property?">
                                                                     <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                                     <input type="hidden" name="id" value="<?php echo e($p['id']); ?>">
                                                                     <input type="hidden" name="action" value="reject">
                                                                     <button type="submit" class="btn btn-danger btn-sm" title="Reject" aria-label="Reject"><i class="fas fa-times"></i></button>
                                                                 </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo e($i); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo e($i); ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAll');
    var rowCheckboxes = document.querySelectorAll('.rowCheckbox');
    var bulkActionBar = document.getElementById('bulkActionBar');
    var bulkSelectedCount = document.getElementById('bulkSelectedCount');
    var bulkClearBtn = document.getElementById('bulkClearBtn');
    var bulkActionForm = document.getElementById('bulkActionFormSubmit');
    var bulkActionDropdownItems = document.querySelectorAll('[data-bulk-action]');

    function updateBulkUI() {
        var checked = document.querySelectorAll('.rowCheckbox:checked');
        var count = checked.length;
        bulkSelectedCount.textContent = count;
        bulkActionBar.style.display = count > 0 ? 'flex' : 'none';
        selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
        selectAll.checked = count === rowCheckboxes.length && rowCheckboxes.length > 0;
    }

    selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(function(cb) { cb.checked = this.checked; }, this);
        updateBulkUI();
    });

    rowCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateBulkUI);
    });

    bulkClearBtn.addEventListener('click', function() {
        selectAll.checked = false;
        rowCheckboxes.forEach(function(cb) { cb.checked = false; });
        updateBulkUI();
    });

    bulkActionDropdownItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            var action = this.getAttribute('data-bulk-action');
            var checked = document.querySelectorAll('.rowCheckbox:checked');
            if (checked.length === 0) return;

            var ids = Array.from(checked).map(function(cb) { return cb.value; });
            
            var form = document.getElementById('bulkActionFormSubmit');
            form.querySelectorAll('input[name="bulk_action"]').forEach(function(el) { el.remove(); });
            var actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'bulk_action';
            actionInput.value = action;
            form.appendChild(actionInput);

            if (action === 'export') {
                window.location.href = '<?php echo BASE_URL; ?>/admin/user-properties/export?' + new URLSearchParams({ids: ids.join(',')}).toString();
            } else {
                form.submit();
            }
        });
    });

    document.querySelectorAll('[data-aps-confirm]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-aps-confirm'))) e.preventDefault();
        });
    });
});
</script>


