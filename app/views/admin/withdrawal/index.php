<?php
$page_title = $page_title ?? 'Withdrawal Requests';
$base = defined('BASE_URL') ? BASE_URL : '';
$requests = $requests ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0, 'processing' => 0];
$csrf_token = $_SESSION['csrf_token'] ?? '';
$currentStatus = $_GET['status'] ?? '';
$currentUserType = $_GET['user_type'] ?? '';
$currentFromDate = $_GET['from_date'] ?? '';
$currentToDate = $_GET['to_date'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$totalPages = ceil(($stats['total'] ?? 0) / 20);
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="m-0"><i class="fas fa-money-bill-wave me-2 text-info"></i>Withdrawal Requests</h4>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/withdrawals/export" class="btn btn-outline-success btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm stat-card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <div class="stat-value fw-bold fs-3"><?= number_format($stats['total'] ?? 0) ?></div>
                    <div class="stat-label small opacity-75">Total</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm stat-card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <div class="stat-value fw-bold fs-3"><?= number_format($stats['pending'] ?? 0) ?></div>
                    <div class="stat-label small opacity-75">Pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm stat-card bg-success text-white h-100">
                <div class="card-body text-center">
                    <div class="stat-value fw-bold fs-3"><?= number_format($stats['approved'] ?? 0) ?></div>
                    <div class="stat-label small opacity-75">Approved</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm stat-card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <div class="stat-value fw-bold fs-3"><?= number_format($stats['rejected'] ?? 0) ?></div>
                    <div class="stat-label small opacity-75">Rejected</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm stat-card bg-info text-white h-100">
                <div class="card-body text-center">
                    <div class="stat-value fw-bold fs-3"><?= number_format($stats['completed'] ?? 0) ?></div>
                    <div class="stat-label small opacity-75">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm stat-card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <div class="stat-value fw-bold fs-3"><?= number_format($stats['processing'] ?? 0) ?></div>
                    <div class="stat-label small opacity-75">Processing</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $currentStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $currentStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="processing" <?= $currentStatus === 'processing' ? 'selected' : '' ?>>Processing</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">User Type</label>
                    <select name="user_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="customer" <?= $currentUserType === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="associate" <?= $currentUserType === 'associate' ? 'selected' : '' ?>>Associate</option>
                        <option value="agent" <?= $currentUserType === 'agent' ? 'selected' : '' ?>>Agent</option>
                        <option value="employee" <?= $currentUserType === 'employee' ? 'selected' : '' ?>>Employee</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($currentFromDate) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($currentToDate) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Name/Email/Phone/ID" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (!empty($requests)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Role</th>
                                <th class="text-end">Amount</th>
                                <th>Bank</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">#<?= $r['id'] ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2">
                                                <?= strtoupper(substr($r['name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-medium small"><?= htmlspecialchars($r['name'] ?? 'Unknown') ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($r['email'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $r['role'] === 'customer' ? 'primary' : 
                                            ($r['role'] === 'associate' ? 'success' : 
                                            ($r['role'] === 'agent' ? 'warning' : 'info')) ?>">
                                            <?= ucfirst($r['role'] ?? 'unknown') ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-primary">₹<?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <div class="small">
                                            <div class="fw-medium"><?= htmlspecialchars($r['account_holder_name'] ?? 'N/A') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($r['bank_name'] ?? 'N/A') ?></small>
                                            <div class="text-muted">****<?= $r['account_number'] ? substr($r['account_number'], -4) : '----' ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($r['ifsc_code'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = match($r['status']) {
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'rejected' => 'danger',
                                            'completed' => 'success',
                                            'processing' => 'primary',
                                            default => 'secondary'
                                        };
                                        $statusIcon = match($r['status']) {
                                            'pending' => 'clock',
                                            'approved' => 'check-circle',
                                            'rejected' => 'times-circle',
                                            'completed' => 'check-double',
                                            'processing' => 'spinner',
                                            default => 'question'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>">
                                            <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                                            <?= ucfirst($r['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $base ?>/admin/withdrawals/view/<?= $r['id'] ?>" class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($r['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-outline-success" onclick="openApproveModal(<?= $r['id'] ?>)" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="openRejectModal(<?= $r['id'] ?>)" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif ($r['status'] === 'approved'): ?>
                                                <button type="button" class="btn btn-outline-primary" onclick="openCompleteModal(<?= $r['id'] ?>)" title="Mark Completed">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No withdrawal requests found</h5>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Withdrawal requests pagination" class="px-3 py-3 border-top">
                <nav aria-label="Withdrawal requests pagination">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        $params = array_filter($_GET);
                        unset($params['page']);
                        $query = http_build_query($params);
                        $querySuffix = $query ? '&' . $query : '';
                        ?>
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $querySuffix ?>" aria-label="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $querySuffix ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $querySuffix ?>" aria-label="Next">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="text-center text-muted small mt-2">
                    Showing page <?= $page ?> of <?= $totalPages ?> (<?= number_format($stats['total'] ?? 0) ?> total records)
                </div>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/withdrawals/update/{{id}}" id="approveForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="status" value="approved">
                <input type="hidden" name="id" id="approveId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-circle text-success me-2"></i>Approve Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to <strong>approve</strong> this withdrawal request?</p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Add notes for the user..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/withdrawals/update/{{id}}" id="rejectForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="status" value="rejected">
                <input type="hidden" name="id" id="rejectId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Reject Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>Warning:</strong> This will reject the withdrawal and refund the amount to the user's wallet.</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="admin_notes" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/withdrawals/update/{{id}}" id="completeForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="status" value="completed">
                <input type="hidden" name="id" id="completeId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-double text-success me-2"></i>Mark as Completed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Mark this approved withdrawal as <strong>completed</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Add completion notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check-double me-1"></i>Mark Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global state for loading states
let currentLoadingBtn = null;

function showLoading(btnId, spinnerId, textId, loadingText) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);
    if (btn && spinner && text) {
        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = loadingText;
        currentLoadingBtn = { btnId, spinnerId, textId };
    }
}

function hideLoading(btnId, spinnerId, textId, originalText) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);
    if (btn && spinner && text) {
        btn.disabled = false;
        spinner.classList.add('d-none');
        text.textContent = originalText;
        currentLoadingBtn = null;
    }
}

function openApproveModal(id) {
    document.getElementById('approveId').value = id;
    document.getElementById('approveForm').action = '<?= $base ?>/admin/withdrawals/update/' + id;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
    setTimeout(() => {
        const textarea = document.querySelector('#approveForm textarea[name="admin_notes"]');
        if (textarea) textarea.focus();
    }, 150);
}

function openRejectModal(id) {
    document.getElementById('rejectId').value = id;
    document.getElementById('rejectForm').action = '<?= $base ?>/admin/withdrawals/update/' + id;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
    setTimeout(() => {
        const textarea = document.querySelector('#rejectForm textarea[name="admin_notes"]');
        if (textarea) textarea.focus();
    }, 150);
}

function openCompleteModal(id) {
    document.getElementById('completeId').value = id;
    document.getElementById('completeForm').action = '<?= $base ?>/admin/withdrawals/update/' + id;
    new bootstrap.Modal(document.getElementById('completeModal')).show();
    setTimeout(() => {
        const textarea = document.querySelector('#completeForm textarea[name="admin_notes"]');
        if (textarea) textarea.focus();
    }, 150);
}

// Global state for loading states
let currentLoadingBtn = null;

function showLoading(btnId, spinnerId, textId, loadingText) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);
    if (btn && spinner && text) {
        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = loadingText;
        currentLoadingBtn = { btnId, spinnerId, textId };
    }
}

function hideLoading(btnId, spinnerId, textId, originalText) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);
    if (btn && spinner && text) {
        btn.disabled = false;
        spinner.classList.add('d-none');
        text.textContent = originalText;
        currentLoadingBtn = null;
    }
}

// Form submission handlers with loading states
document.addEventListener('DOMContentLoaded', function() {
    // Approve form
    const approveForm = document.getElementById('approveForm');
    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('approveSubmitBtn');
            if (submitBtn) {
                showLoading('approveSubmitBtn', 'approveSpinner', 'approveBtnText', 'Approving...');
            }
        });
    });

    // Reject form
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const textarea = this.querySelector('textarea[name="admin_notes"]');
            if (!textarea.value.trim()) {
                e.preventDefault();
                alert('Rejection reason is required');
                return false;
            }
            const submitBtn = document.getElementById('rejectSubmitBtn');
            if (submitBtn) {
                showLoading('rejectSubmitBtn', 'rejectSpinner', 'rejectBtnText', 'Rejecting...');
            }
        });
    });

    // Complete form
    const completeForm = document.getElementById('completeForm');
    if (completeForm) {
        completeForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('completeSubmitBtn');
            if (submitBtn) {
                showLoading('completeSubmitBtn', 'completeSpinner', 'completeBtnText', 'Completing...');
            }
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to close modals
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                bootstrap.Modal.getInstance(modal)?.hide();
            });
        }
        // Enter to submit focused form (when modal is open) - Ctrl+Enter
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            const activeModal = document.querySelector('.modal.show');
            if (activeModal) {
                const form = activeModal.querySelector('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.click();
                    }
                }
            }
        }
    });

    // Auto-hide loading states after 30 seconds (fallback)
    setInterval(function() {
        if (currentLoadingBtn) {
            const btn = document.getElementById(currentLoadingBtn.btnId);
            if (btn && btn.disabled) {
                console.warn('Loading state timeout for:', currentLoadingBtn.btnId);
            }
        }
    }, 30000);

    // Add enter key support for textareas (Ctrl+Enter to submit)
    document.querySelectorAll('textarea[name="admin_notes"]').forEach(textarea => {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                const form = this.closest('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        e.preventDefault();
                        submitBtn.click();
                    }
                }
            }
        });
    });
});
</script>