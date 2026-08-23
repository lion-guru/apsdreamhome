<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-umbrella-beach text-primary me-2"></i>Leave Applications</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/hr/leave-balances" class="btn btn-outline-warning shadow-sm me-2">
                <i class="fas fa-balance-scale me-1"></i> Employee Balances
            </a>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Apply Leave
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filter Requests</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Applications</option>
                        <option value="pending" <?= ($status_filter ?? '') === 'pending' ? 'selected' : '' ?>>Pending Approval</option>
                        <option value="approved" <?= ($status_filter ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= ($status_filter ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Leave Type</label>
                    <a href="<?= BASE_URL ?>/admin/hr/leave-types" class="btn btn-outline-secondary w-100"><i class="fas fa-tags me-2"></i>Manage Types</a>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
                <?php if (!empty($status_filter)): ?>
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>/admin/hr/leaves" class="btn btn-light border w-100">Clear</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4 border-bottom-primary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="leavesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Total Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaves)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x d-block mb-3 text-gray-300" aria-hidden="true"></i>No leave applications found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leaves as $l): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center shadow-sm style-60393">
                                                <?= strtoupper(substr(htmlspecialchars($l['employee_name'] ?? 'E'), 0, 1)) ?>
                                            </div>
                                            <strong><?= htmlspecialchars($l['employee_name'] ?? '') ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <i class="fas fa-tag text-muted me-1"></i> <?= htmlspecialchars($l['leave_type_name'] ?? $l['leave_type'] ?? 'General') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-bold text-gray-800"><?= date('d M Y', strtotime($l['start_date'] ?? 'now')) ?></div>
                                        <div class="small text-muted">to <?= date('d M Y', strtotime($l['end_date'] ?? 'now')) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill px-3 py-1 fs-6"><?= $l['total_days'] ?? '0' ?> Days</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate d-inline-block text-muted small style-67917" title="<?= htmlspecialchars($l['reason'] ?? '') ?>">
                                            <?= htmlspecialchars($l['reason'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                            $status = $l['status'] ?? 'pending';
                                            $badgeClass = match($status) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'pending' => 'warning',
                                                default => 'secondary'
                                            };
                                            $icon = match($status) {
                                                'approved' => 'check-circle',
                                                'rejected' => 'times-circle',
                                                'pending' => 'clock',
                                                default => 'circle'
                                            };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>-subtle text-<?= $badgeClass ?> border border-<?= $badgeClass ?>-subtle rounded-pill px-3 py-1">
                                            <i class="fas fa-<?= $icon ?> me-1"></i> <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if (($l['status'] ?? '') === 'pending'): ?>
                                            <div class="btn-group shadow-sm" role="group">
                                                <a href="<?= BASE_URL ?>/admin/hr/leaves/approve/<?= $l['id'] ?>" class="btn btn-sm btn-success" title="Approve" data-aps-confirm="Are you sure you want to approve this leave request?">
                                                    <i class="fas fa-check me-1"></i> Approve
                                                </a>
                                                <a href="<?= BASE_URL ?>/admin/hr/leaves/reject/<?= $l['id'] ?>" class="btn btn-sm btn-danger" title="Reject" data-aps-confirm="Are you sure you want to reject this leave request?">
                                                    <i class="fas fa-times me-1"></i> Reject
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic"><i class="fas fa-check text-success"></i> Processed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (($total_pages ?? 1) > 1): ?>
            <div class="card-footer bg-white py-3">
                <nav><ul class="pagination pagination-sm mb-0 justify-content-end">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="?status=<?= urlencode($status_filter ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/leaves/store">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Apply for Leave</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select select2-modal" required class="style-13113">
                                <option value="">Select Employee...</option>
                                <?php if (isset($users)): ?>
                                    <?php foreach ($users as $emp): ?>
                                        <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Leave Type</label>
                            <select name="leave_type_id" class="form-select bg-white">
                                <option value="0">General Leave</option>
                                <?php if (isset($leave_types)): ?>
                                    <?php foreach ($leave_types as $lt): ?>
                                        <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="text-primary mb-3"><i class="far fa-calendar-alt me-2"></i>Duration</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label text-muted small fw-bold">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control form-control-lg" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase">Reason for Leave <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Please provide details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-paper-plane me-2"></i>Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 in Modal
    if($.fn.select2) {
        $('.select2-modal').select2({
            dropdownParent: $('#applyModal'),
            theme: 'bootstrap-5'
        });
    }

    // Initialize DataTable
    if($.fn.DataTable) {
        $('#leavesTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']], // Wait, ID isn't in column 0, actually date might be better. Let's just disable default sorting.
            bSort: false, // Server side is already sorting by newest
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search applications..."
            }
        });
    }
});
</script>
