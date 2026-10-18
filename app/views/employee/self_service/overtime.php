<?php
/**
 * Overtime Management Dashboard
 */

$page_title = $page_title ?? 'Overtime Management';
$requests = $requests ?? [];
$policy = $policy ?? [];
$summary = $summary ?? ['total_requests' => 0, 'total_hours' => 0, 'total_amount' => 0, 'pending_count' => 0];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-clock me-2 text-info"></i>Overtime Management</h4>
                    <p class="text-muted mb-0 small">Request overtime, track approvals, view earnings</p>
                </div>
                <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#applyOvertimeModal">
                    <i class="fas fa-plus me-1"></i> Request Overtime
                </button>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-primary"><?= $summary['total_requests'] ?></div>
                            <div class="small text-muted">Total Requests</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-success"><?= $summary['total_hours'] ?></div>
                            <div class="small text-muted">Total Hours</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-warning">₹<?= number_format($summary['total_amount'], 2) ?></div>
                            <div class="small text-muted">Total Earnings</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 <?= $summary['pending_count'] > 0 ? 'text-warning' : 'text-success' ?>">
                                <?= $summary['pending_count'] ?>
                            </div>
                            <div class="small text-muted">Pending Approval</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Policy Info -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Overtime Policy</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Rate:</strong> <?= $policy['weekday_rate'] ?? '1.5x' ?> (Weekdays), <?= $policy['weekend_rate'] ?? '2x' ?> (Weekends/Holidays)
                        </div>
                        <div class="col-md-4">
                            <strong>Max Hours/Month:</strong> <?= $policy['max_hours_per_month'] ?? '50' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Approval:</strong> Manager + HR
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <strong>Min Duration:</strong> <?= $policy['min_duration_minutes'] ?? 30 ?> minutes
                        </div>
                        <div class="col-md-4">
                            <strong>Compensatory Off:</strong> <?= $policy['comp_off_allowed'] ? 'Yes' : 'No' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Payment:</strong> With next salary
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Requests -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>My Overtime Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($requests)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Overtime Requests</h5>
                            <p class="text-muted">Submit your first overtime request to get started.</p>
                            <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#applyOvertimeModal">
                                <i class="fas fa-plus me-1"></i> Request Overtime
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Duration</th>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $r): ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($r['overtime_date'] ?? $r['date'] ?? 'now')) ?></td>
                                        <td>
                                            <?= date('H:i', strtotime($r['start_time'] ?? '00:00')) ?> - 
                                            <?= date('H:i', strtotime($r['end_time'] ?? '00:00')) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?= $r['hours'] ?? 0 ?> hrs</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $r['rate_multiplier'] ?? '1.5x' ?></span>
                                        </td>
                                        <td class="fw-bold text-success">₹<?= number_format($r['amount'] ?? 0, 2) ?></td>
                                        <td><?= htmlspecialchars(substr($r['reason'] ?? '', 0, 50)) ?><?= strlen($r['reason'] ?? '') > 50 ? '...' : '' ?></td>
                                        <td>
                                            <span class="badge <?= $r['status'] === 'approved' ? 'bg-success' : ($r['status'] === 'rejected' ? 'bg-danger' : ($r['status'] === 'paid' ? 'bg-info' : 'bg-warning text-dark')) ?>">
                                                <?= ucfirst($r['status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/employee/self-service/overtime/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
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

<!-- Apply Overtime Modal -->
<div class="modal fade" id="applyOvertimeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/employee/self-service/overtime/apply" id="overtimeApplyForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-clock me-2"></i>Request Overtime</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="date" class="col-sm-3 col-form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
                            <div class="form-text">Cannot request overtime for future dates.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="start_time" class="col-sm-3 col-form-label fw-bold">Start Time <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="end_time" class="col-sm-3 col-form-label fw-bold">End Time <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="reason" class="col-sm-3 col-form-label fw-bold">Reason <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Describe the work done during overtime (project name, task details, etc.)"></textarea>
                        </div>
                    </div>
                    <div class="card bg-light mb-3" id="overtimePreview">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fas fa-calculator me-2"></i>Estimated Overtime Calculation</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="fw-bold text-primary" id="previewHours">0</div>
                                    <small class="text-muted">Hours</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-info" id="previewRate">1.5x</div>
                                    <small class="text-muted">Rate</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-success" id="previewAmount">₹0</div>
                                    <small class="text-muted">Est. Amount</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    const dateInput = document.getElementById('date');
    const previewHours = document.getElementById('previewHours');
    const previewRate = document.getElementById('previewRate');
    const previewAmount = document.getElementById('previewAmount');

    function calculateOvertime() {
        if (!startTime.value || !endTime.value) return;
        
        const start = new Date('2000-01-01 ' + startTime.value);
        const end = new Date('2000-01-01 ' + endTime.value);
        
        if (end <= start) {
            // Handle overnight
            end.setDate(end.getDate() + 1);
        }
        
        const diffMs = end - start;
        const hours = diffMs / (1000 * 60 * 60);
        
        if (hours > 0) {
            // Determine rate based on day
            const date = new Date(dateInput.value);
            const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
            
            const rate = isWeekend ? 2.0 : 1.5;
            const hourlyRate = 500; // placeholder - would come from salary
            const amount = hours * hourlyRate * rate;
            
            previewHours.textContent = hours.toFixed(2);
            previewRate.textContent = rate + 'x';
            previewAmount.textContent = '₹' + amount.toLocaleString('en-IN', {maximumFractionDigits: 2});
        }
    }

    [startTime, endTime, dateInput].forEach(el => {
        el.addEventListener('change', calculateOvertime);
    });

    // Initial calculation
    calculateOvertime();
});
</script>