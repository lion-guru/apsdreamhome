<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php $csrf = $_SESSION['csrf_token'] ?? ''; ?>
<?php
// Build filter query string for pagination
$filterQs = '';
if (!empty($filters['search'])) $filterQs .= '&search=' . urlencode($filters['search']);
if (!empty($filters['role'])) $filterQs .= '&role=' . urlencode($filters['role']);
if (!empty($filters['status'])) $filterQs .= '&status=' . urlencode($filters['status']);
?>

<!-- Users Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">User Management</h1>
        <p class="text-muted mb-0">Manage all system users &middot; <strong><?= number_format($total ?? 0) ?></strong> total</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/users/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add User
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, phone..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <?php foreach (['admin','super_admin','manager','employee','telecaller','associate','agent','customer','user'] as $r): ?>
                    <option value="<?= $r ?>" <?= ($filters['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Toolbar (hidden until selection) -->
<div id="bulkToolbar" class="card border-0 shadow-sm mb-3" style="display:none">
    <div class="card-body py-2 d-flex align-items-center gap-3">
        <span class="text-muted"><strong id="selectedCount">0</strong> users selected</span>
        <div class="btn-group btn-group-sm">
            <button onclick="bulkAction('activate')" class="btn btn-outline-success"><i class="fas fa-check me-1"></i>Activate</button>
            <button onclick="bulkAction('deactivate')" class="btn btn-outline-warning"><i class="fas fa-ban me-1"></i>Deactivate</button>
            <button onclick="bulkAction('suspend')" class="btn btn-outline-danger"><i class="fas fa-times me-1"></i>Suspend</button>
        </div>
        <button onclick="clearSelection()" class="btn btn-sm btn-outline-secondary ms-auto">Clear Selection</button>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4" style="width:40px">
                            <input type="checkbox" id="selectAll" class="form-check-input" title="Select all">
                        </th>
                        <th class="border-0">User</th>
                        <th class="border-0">Email</th>
                        <th class="border-0">Role</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Reg. Status</th>
                        <th class="border-0">Joined</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input user-checkbox" value="<?= $user['id'] ?>" data-role="<?= htmlspecialchars($user['role'] ?? '') ?>">
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div>
                                    <a href="<?php echo BASE_URL; ?>/admin/users/<?php echo $user['id']; ?>" class="fw-semibold text-decoration-none"><?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></a>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                        <td><span class="badge bg-primary"><?php echo ucfirst($user['role'] ?? 'user'); ?></span></td>
                        <td><span class="badge bg-<?php echo ($user['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($user['status'] ?? 'active'); ?></span></td>
                        <td>
                            <?php
                            $regStatus = $user['registration_status'] ?? 'approved';
                            $regBadgeClass = match($regStatus) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $regBadgeClass; ?>"><?php echo ucfirst($regStatus); ?></span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                        <td class="text-end pe-4">
                            <a href="<?php echo BASE_URL; ?>/admin/users/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo BASE_URL; ?>/admin/users/<?php echo $user['id']; ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?php echo BASE_URL; ?>/admin/users/<?php echo $user['id']; ?>/wallet" class="btn btn-sm btn-outline-success" title="Wallet"><i class="fas fa-wallet"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No users found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if (!empty($total_pages) && $total_pages > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php $currentPage = $page ?? 1; ?>
    <?php if ($currentPage > 1): ?>
    <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/admin/users?page=<?= $currentPage - 1 ?><?= $filterQs ?>">Prev</a></li>
    <?php endif; ?>
    <?php for ($i = max(1, $currentPage - 2); $i <= min($total_pages, $currentPage + 2); $i++): ?>
    <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
        <a class="page-link" href="<?= BASE_URL ?>/admin/users?page=<?= $i ?><?= $filterQs ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
    <?php if ($currentPage < $total_pages): ?>
    <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/admin/users?page=<?= $currentPage + 1 ?><?= $filterQs ?>">Next</a></li>
    <?php endif; ?>
</ul></nav>
<?php endif; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF = '<?= $csrf ?>';

// Select All
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.user-checkbox').forEach(cb => { cb.checked = this.checked; });
    updateBulkToolbar();
});

// Individual checkboxes
document.querySelectorAll('.user-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkToolbar);
});

function updateBulkToolbar() {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkToolbar').style.display = count > 0 ? 'block' : 'none';
    // Update select all state
    const all = document.querySelectorAll('.user-checkbox');
    const selAll = document.getElementById('selectAll');
    if (selAll) selAll.checked = all.length > 0 && count === all.length;
}

function clearSelection() {
    document.querySelectorAll('.user-checkbox').forEach(cb => { cb.checked = false; });
    document.getElementById('selectAll').checked = false;
    updateBulkToolbar();
}

function bulkAction(action) {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const ids = Array.from(checked).map(cb => parseInt(cb.value));
    if (!ids.length) return alert('No users selected');

    const adminRoles = ['admin', 'super_admin'];
    const hasAdmin = Array.from(checked).some(cb => adminRoles.includes(cb.dataset.role));
    if (hasAdmin && (action === 'deactivate' || action === 'suspend')) {
        return alert('Cannot ' + action + ' admin users');
    }

    if (!confirm('Bulk ' + action + ' ' + ids.length + ' user(s)?')) return;

    fetch(BASE_URL + '/admin/users/bulk-operation', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF) + '&bulk_action=' + action + '&' + ids.map(id => 'user_ids[]=' + id).join('&')
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); } else { alert(d.message || 'Failed'); }
    }).catch(() => alert('Network error'));
}
</script>
