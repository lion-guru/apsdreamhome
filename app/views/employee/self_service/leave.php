<?php
/**
 * Leave Management
*/

$page_title = $page_title ?? 'Leave Management';
$leave_balances = $leave_balances ?? [];
$leave_history = $leave_history ?? [];
$leave_types = $leave_types ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Leave Management</h4>
                </div>
                <div class="card-body">
                    <!-- Leave Balances -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3"><i class="fas fa-balance-scale me-2"></i>Leave Balances (<?= date('Y') ?>)</h5>
                        </div>
                        <?php if (empty($leave_balances)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No leave balances found for this year.</div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($leave_balances as $lb): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 leave-balance-card">
                                <div class="card-body text-center p-4">
                                    <span class="badge mb-2 fs-6" style="background: <?= $lb['color'] ?? '#007bff' ?>">
                                        <?= htmlspecialchars($lb['leave_type_code'] ?? '') ?>
                                    </span>
                                    <h5 class="card-title"><?= htmlspecialchars($lb['leave_type_name'] ?? '') ?></h5>
                                    <div class="row text-center mt-3">
                                        <div class="col-4">
                                            <div class="fw-bold text-primary"><?= number_format($lb['allocated_days'] ?? 0, 1) ?></div>
                                            <small class="text-muted">Allocated</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-warning"><?= number_format($lb['used_days'] ?? 0, 1) ?></div>
                                            <small class="text-muted">Used</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-success fs-5"><?= number_format($lb['remaining_days'] ?? 0, 1) ?></div>
                                            <small class="text-muted">Remaining</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-3" style="height: 8px;">
                                        <?php 
                                        $usedPct = ($lb['allocated_days'] ?? 0) > 0 
                                            ? (($lb['used_days'] ?? 0) / ($lb['allocated_days'] ?? 1)) * 100 
                                            : 0; 
                                        ?>
                                        <div class="progress-bar bg-<?= $usedPct > 80 ? 'danger' : ($usedPct > 50 ? 'warning' : 'success') ?>" 
                                             style="width: <?= min($usedPct, 100) ?>%"></div>
                                    </div>
                                    <small class="text-muted mt-1 d-block"><?= round($usedPct) ?>% Utilized</small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Apply Leave Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Apply New Leave</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3" id="leaveForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="col-md-4">
                                    <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                                    <select name="leave_type_id" class="form-select" required>
                                        <option value="">Select Leave Type</option>
                                        <?php foreach ($leave_types as $lt): ?>
                                        <option value="<?= $lt['id'] ?>"><?= htmlspecialchars($lt['name']) ?> (<?= $lt['code'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason for leave..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" name="emergency_contact" class="form-control" placeholder="Name & Phone">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Work Coverage Plan</label>
                                    <input type="text" name="work_coverage" class="form-control" placeholder="Who will cover your work?">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-paper-plane me-1"></i> Submit Leave Application
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Leave History -->
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Leave History</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($leave_history)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                <p>No leave applications yet</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Period</th>
                                            <th class="text-center">Days</th>
                                            <th>Reason</th>
                                            <th class="text-center">Status</th>
                                            <th>Applied On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leave_history as $lv): ?>
                                        <tr>
                                            <td>
                                                <span class="badge me-1" style="background: <?= $lv['color'] ?? '#007bff' ?>">
                                                    <?= htmlspecialchars($lv['leave_type_code'] ?? '') ?>
                                                </span>
                                                <?= htmlspecialchars($lv['leave_type_name'] ?? '') ?>
                                            </td>
                                            <td><?= date('d M Y', strtotime($lv['start_date'])) ?> - <?= date('d M Y', strtotime($lv['end_date'])) ?></td>
                                            <td class="text-center"><?= $lv['total_days'] ?></td>
                                            <td><small><?= htmlspecialchars(substr($lv['reason'] ?? '', 0, 50)) ?></small></td>
                                            <td class="text-center">
                                                <?php $st = strtolower($lv['status'] ?? 'pending'); ?>
                                                <span class="badge bg-<?= $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'cancelled' ? 'secondary' : 'warning')) ?>">
                                                    <?= ucfirst($st) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($lv['created_at'])) ?></td>
                                            <td class="text-center">
                                                <?php if (($lv['status'] ?? '') === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelLeave(<?= $lv['id'] ?>)" title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if (($lv['status'] ?? '') !== 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="viewLeaveDetails(<?= htmlspecialchars(json_encode($lv)) ?>)" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Detail Modal -->
<div class="modal fade" id="leaveDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i>Leave Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaveDetailBody">
                <!-- Filled by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.leave-balance-card { transition: transform 0.2s; }
.leave-balance-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.progress { border-radius: 4px; }
</style>

<script>
function cancelLeave(id) {
    if (!confirm('Are you sure you want to cancel this leave application?')) return;
    
    fetch(`<?= BASE_URL ?>/employee/leave/cancel/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    }).then(res => res.json()).then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to cancel');
        }
    });
}

function viewLeaveDetails(lv) {
    const modal = new bootstrap.Modal(document.getElementById('leaveDetailModal'));
    document.getElementById('leaveDetailBody').innerHTML = `
        <div class="row mb-3">
            <div class="col-6"><strong>Leave Type:</strong></div>
            <div class="col-6"><span class="badge me-1" style="background: ${lv.color}">${lv.leave_type_code}</span> ${lv.leave_type_name}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Period:</strong></div>
            <div class="col-6">${new Date(lv.start_date).toLocaleDateString()} - ${new Date(lv.end_date).toLocaleDateString()}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Total Days:</strong></div>
            <div class="col-6">${lv.total_days}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Reason:</strong></div>
            <div class="col-6">${lv.reason}</div>
        </div>
        ${lv.emergency_contact ? `<div class="row mb-3"><div class="col-6"><strong>Emergency Contact:</strong></div><div class="col-6">${lv.emergency_contact}</div></div>` : ''}
        ${lv.work_coverage ? `<div class="row mb-3"><div class="col-6"><strong>Work Coverage:</strong></div><div class="col-6">${lv.work_coverage}</div></div>` : ''}
        <div class="row mb-3">
            <div class="col-6"><strong>Status:</strong></div>
            <div class="col-6"><span class="badge bg-${lv.status === 'approved' ? 'success' : (lv.status === 'rejected' ? 'danger' : (lv.status === 'cancelled' ? 'secondary' : 'warning'))}">${lv.status}</span></div>
        </div>
        ${lv.approved_by_name ? `<div class="row mb-3"><div class="col-6"><strong>Approved By:</strong></div><div class="col-6">${lv.approved_by_name}</div></div>` : ''}
        ${lv.approved_at ? `<div class="row mb-3"><div class="col-6"><strong>Approved On:</strong></div><div class="col-6">${new Date(lv.approved_at).toLocaleString()}</div></div>` : ''}
        ${lv.rejection_reason ? `<div class="row mb-3"><div class="col-6"><strong>Rejection Reason:</strong></div><div class="col-6 text-danger">${lv.rejection_reason}</div></div>` : ''}
        <div class="row mb-3">
            <div class="col-6"><strong>Applied On:</strong></div>
            <div class="col-6">${new Date(lv.created_at).toLocaleString()}</div>
        </div>
    `;
    modal.show();
}
</script>