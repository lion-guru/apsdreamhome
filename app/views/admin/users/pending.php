<?php $layout = "admin/layouts/admin"; $active_page = "pending"; $total_pages = $total_pages ?? 1; $page = $page ?? 1; ?>
<!-- Pending Registrations Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Pending Registrations</h1>
        <p class="text-muted mb-0"><?php echo $total ?? 0; ?> users awaiting approval</p>
    </div>
    <div>
        <button onclick="bulkApprove()" class="btn btn-success" id="bulkApproveBtn" class="style-24280">
            <i class="fas fa-check-double me-2"></i>Approve Selected (<span id="selectedCount">0</span>)
        </button>
        <a href="<?php echo BASE_URL; ?>/admin/users" class="btn btn-outline-secondary">
            <i class="fas fa-list me-2"></i>All Users
        </a>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo e($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Pending Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4" class="style-89354">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                        </th>
                        <th class="border-0">User</th>
                        <th class="border-0">Role</th>
                        <th class="border-0">Registered</th>
                        <th class="border-0">Referral</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr id="row-<?php echo e($user['id']); ?>">
                        <td class="ps-4">
                            <input type="checkbox" class="user-checkbox" value="<?php echo e($user['id']); ?>" onchange="updateBulkButton()">
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" class="style-48301">
                                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></small>
                                    <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($user['phone'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-primary"><?php echo ucfirst($user['role'] ?? 'user'); ?></span></td>
                        <td><?php echo date('M d, Y g:i A', strtotime($user['created_at'] ?? 'now')); ?></td>
                        <td>
                            <?php if (!empty($user['referred_by'])): ?>
                                <span class="badge bg-info">Has Sponsor</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button onclick="approveUser(<?php echo e($user['id']); ?>)" class="btn btn-sm btn-success me-1" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="rejectUser(<?php echo e($user['id']); ?>)" class="btn btn-sm btn-danger" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No pending registrations
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if (($total_pages ?? 0) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?php echo $i == ($page ?? 1) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo e($i); ?>"><?php echo e($i); ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectUserId">
                <div class="mb-3">
                    <label class="form-label">Reason for rejection (optional)</label>
                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Enter reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">Reject</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
function approveUser(userId) {
    apsConfirm('Approve this user? They will be able to login immediately.').then(function(ok) {
        if (!ok) return;
    showLoader();

    fetch('<?php echo BASE_URL; ?>/admin/users/' + userId + '/approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]')?.value || '')
    });
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('row-' + userId);
            .catch(err => console.error('Request failed:', err));
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
            showToast('User approved!', 'success');
        } else {
            showToast(data.message || 'Failed to approve', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'))
    .finally(() => hideLoader());
}

function rejectUser(userId) {
    document.getElementById('rejectUserId').value = userId;
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
    const userId = document.getElementById('rejectUserId').value;
    const reason = document.getElementById('rejectReason').value;
    if (!reason.trim()) { showToast('Please provide a reason', 'warning'); return; }
    showLoader();

    fetch('<?php echo BASE_URL; ?>/admin/users/' + userId + '/reject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]')?.value || '') + '&reason=' + encodeURIComponent(reason)
    })
    .then(r => r.json())
    .then(data => {
        .catch(err => console.error('Request failed:', err));
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        if (data.success) {
            const row = document.getElementById('row-' + userId);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
            showToast('User rejected', 'warning');
        } else {
            showToast(data.message || 'Failed to reject', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'))
    .finally(() => hideLoader());
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateBulkButton();
}

function updateBulkButton() {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const btn = document.getElementById('bulkApproveBtn');
    const count = document.getElementById('selectedCount');
    if (checked.length > 0) {
        btn.style.display = '';
        count.textContent = checked.length;
    } else {
        btn.style.display = 'none';
    }
}

function bulkApprove() {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    if (ids.length === 0) return;
    apsConfirm('Approve ' + ids.length + ' users?').then(function(ok) {
        if (!ok) return;

    });
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
    ids.forEach(id => formData.append('user_ids[]', id));

    fetch('<?php echo BASE_URL; ?>/admin/users/bulk-approve', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
                .catch(err => console.error('Request failed:', err));
            ids.forEach(id => {
                const row = document.getElementById('row-' + id);
                if (row) row.remove();
            });
            showToast(data.message, 'success');
            updateBulkButton();
        } else {
            showToast(data.message || 'Failed', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'));
}
</script>
