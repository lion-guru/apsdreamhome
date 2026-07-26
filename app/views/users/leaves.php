<?php
$leaveTypes = $leaveTypes ?? [];
$leaveBalance = $leaveBalance ?? [];
$leaves = $leaves ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

function lvStatusBadge($status) {
    $map = ['approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', 'cancelled' => 'secondary'];
    $cls = $map[strtolower($status)] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
function lvDate($d) { return $d ? date('d M Y', strtotime($d)) : '—'; }
function lvDays($d) { return (int)$d; }
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.lv-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.lv-stat:hover { transform: translateY(-2px); }
.lv-stat .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.lv-row { transition: background 0.15s; }
.lv-row:hover { background: #f8fafc; }
.lv-apply-btn { border-radius: 8px; padding: 6px 16px; font-weight: 500; transition: all 0.2s; }
.lv-balance-card { border: none; border-radius: 12px; border-left: 4px solid; transition: transform 0.2s; }
.lv-balance-card:hover { transform: translateY(-2px); }
.lv-empty { color: #94a3b8; }
.lv-badge { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; vertical-align: middle; }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Leave Management</h4>
            <p class="text-muted mb-0 small"><?= $stats['total'] ?> leave applications on record</p>
        </div>
        <button class="btn btn-primary lv-apply-btn" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
            <i class="fas fa-plus me-1"></i>Apply Leave
        </button>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card lv-stat shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3"><i class="fas fa-file-alt"></i></div>
                    <div><h5 class="mb-0 fw-bold"><?= $stats['total'] ?></h5><small class="text-muted">Total Applied</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card lv-stat shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3"><i class="fas fa-clock"></i></div>
                    <div><h5 class="mb-0 fw-bold"><?= $stats['pending'] ?></h5><small class="text-muted">Pending</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card lv-stat shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3"><i class="fas fa-check-circle"></i></div>
                    <div><h5 class="mb-0 fw-bold"><?= $stats['approved'] ?></h5><small class="text-muted">Approved</small></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card lv-stat shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3"><i class="fas fa-times-circle"></i></div>
                    <div><h5 class="mb-0 fw-bold"><?= $stats['rejected'] ?></h5><small class="text-muted">Rejected</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balance -->
    <?php if (!empty($leaveBalance)): ?>
    <div class="row g-3 mb-4">
        <div class="col-12"><h6 class="fw-bold text-muted mb-2"><i class="fas fa-balance-scale me-1"></i>Leave Balance <?= date('Y') ?></h6></div>
        <?php foreach ($leaveBalance as $b): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card lv-balance-card shadow-sm" style="border-left-color: <?= htmlspecialchars($b['color'] ?? '#6c757d') ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <small class="text-muted fw-semibold"><?= htmlspecialchars($b['type_name'] ?? '') ?></small>
                        <span class="lv-badge" style="background: <?= htmlspecialchars($b['color'] ?? '#6c757d') ?>"></span>
                    </div>
                    <h4 class="mb-1 fw-bold"><?= number_format($b['remaining_days'] ?? 0, 1) ?> <small class="text-muted fs-6">/ <?= number_format($b['allocated_days'] ?? 0, 1) ?></small></h4>
                    <small class="text-muted">remaining</small>
                    <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                        <?php $pct = ($b['allocated_days'] ?? 0) > 0 ? (($b['used_days'] ?? 0) / ($b['allocated_days'] ?? 1)) * 100 : 0; ?>
                        <div class="progress-bar" style="width: <?= min(100, $pct) ?>%; background: <?= htmlspecialchars($b['color'] ?? '#6c757d') ?>; border-radius: 3px;"></div>
                    </div>
                    <small class="text-muted mt-1 d-block"><?= number_format($b['used_days'] ?? 0, 1) ?> used<?= ($b['carried_forward'] ?? 0) > 0 ? ' · ' . number_format($b['carried_forward'], 1) . ' carried forward' : '' ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Leave History Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-history me-1"></i>Leave History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Type</th>
                            <th>Period</th>
                            <th class="text-center">Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaves)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 lv-empty">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No leave applications yet.<br>
                                    <small>Click "Apply Leave" to submit your first application.</small>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leaves as $l): ?>
                                <tr class="lv-row">
                                    <td class="ps-3">
                                        <span class="lv-badge" style="background: <?= htmlspecialchars($l['type_color'] ?? '#6c757d') ?>"></span>
                                        <?= htmlspecialchars($l['type_name'] ?? ucfirst($l['leave_type'] ?? '')) ?>
                                    </td>
                                    <td>
                                        <small><?= lvDate($l['start_date']) ?></small>
                                        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size: 0.7rem;"></i>
                                        <small><?= lvDate($l['end_date']) ?></small>
                                    </td>
                                    <td class="text-center fw-semibold"><?= lvDays($l['total_days']) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars(mb_strimwidth($l['reason'] ?? '—', 0, 60, '...')) ?></small></td>
                                    <td><?= lvStatusBadge($l['status'] ?? 'pending') ?></td>
                                    <td class="text-end pe-3">
                                        <a href="/employee/leaves/<?= (int)$l['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (strtolower($l['status'] ?? '') === 'pending'): ?>
                                            <form method="POST" action="/employee/leaves/<?= (int)$l['id'] ?>/cancel" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this leave application?')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="applyLeaveModalLabel"><i class="fas fa-calendar-plus me-2 text-primary"></i>Apply for Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/employee/leaves/apply">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" required>
                                <option value="">Select leave type...</option>
                                <?php foreach ($leaveTypes as $lt): ?>
                                    <option value="<?= (int)$lt['id'] ?>"><?= htmlspecialchars($lt['name']) ?> (<?= (int)$lt['days_per_year'] ?> days/year<?= $lt['is_paid'] ? ' · Paid' : ' · Unpaid' ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control" placeholder="Phone number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Please provide a reason for your leave..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Work Coverage Plan</label>
                            <input type="text" name="work_coverage" class="form-control" placeholder="Who will cover your responsibilities? (optional)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane me-1"></i>Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.querySelector('#applyLeaveModal form')?.addEventListener('submit', function(e) {
    const start = this.querySelector('[name=start_date]').value;
    const end = this.querySelector('[name=end_date]').value;
    if (start && end && end < start) {
        e.preventDefault();
        alert('End date cannot be before start date.');
    }
});
</script>
