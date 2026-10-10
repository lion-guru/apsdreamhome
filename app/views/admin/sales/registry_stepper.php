<?php
/** @var array $booking */
/** @var array $schedule */
/** @var array $milestones */
/** @var array $eligibility */
$booking      = $booking ?? [];
$schedule     = $schedule ?? [];
$milestones   = $milestones ?? [];
$eligibility  = $eligibility ?? ['eligible' => false, 'reasons' => []];
$base         = defined('BASE_URL') ? BASE_URL : '';
$csrf_token   = $csrf_token ?? '';

// Calculate progress
$totalStages = count($milestones);
$completedStages = 0;
foreach ($milestones as $m) {
    if (($m['status'] ?? 'pending') === 'completed') $completedStages++;
}
$progressPercent = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;

$stageIcons = [
    1 => 'fas fa-coins',
    2 => 'fas fa-file-signature',
    3 => 'fas fa-university',
    4 => 'fas fa-stamp',
    5 => 'fas fa-calendar-check',
    6 => 'fas fa-file-upload',
    6 => 'fas fa-home',
];

$stageColors = [
    1 => 'primary',
    2 => 'info',
    3 => 'success',
    4 => 'warning',
    5 => 'secondary',
    6 => 'primary',
    7 => 'success',
];

$statusBadges = [
    'pending'      => 'secondary',
    'in_progress'  => 'warning',
    'completed'    => 'success',
    'skipped'      => 'light',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-road me-2"></i>7-Stage Registry Milestone Tracker</h4>
        <p class="text-muted mb-0">Booking #<?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Booking
        </a>
        <a href="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/registry-check" class="btn btn-outline-primary">
            <i class="fas fa-clipboard-check me-1"></i> NOC Check
        </a>
    </div>
</div>

<!-- Progress Overview Card -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-chart-line me-2"></i>Overall Progress</span>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-<?= $progressPercent === 100 ? 'success' : 'primary' ?> fs-6 px-3 py-2">
                <?= $progressPercent ?>% Complete
            </span>
            <small class="text-muted">Stage <?= $completedStages ?> of <?= $totalStages ?> completed</small>
        </div>
    </div>
    <div class="aps-cp-card-body">
        <div class="progress" style="height: 12px;" role="progressbar" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar bg-gradient" style="width: <?= $progressPercent ?>%;"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <small class="text-muted">Started</small>
            <small class="text-muted">Completed</small>
        </div>
    </div>
</div>

<!-- Booking Summary -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-info-circle me-2"></i>Booking Details</span>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted d-block">Customer</small>
                <strong><?= htmlspecialchars((string)($booking['customer_name'] ?? '—')) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Plot</small>
                <strong><?= htmlspecialchars((string)($booking['plot_number'] ?? '—')) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Colony</small>
                <strong><?= htmlspecialchars((string)($booking['colony_name'] ?? '—')) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Total Value</small>
                <strong class="text-success">₹<?= number_format(floatval($booking['total_plot_value'] ?? 0), 2) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Paid Amount</small>
                <strong class="text-primary">₹<?= number_format(floatval($booking['total_paid'] ?? 0), 2) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Balance</small>
                <strong class="text-danger">₹<?= number_format(floatval(($booking['total_plot_value'] ?? 0) - ($booking['total_paid'] ?? 0)), 2) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Booking Status</small>
                <span class="badge bg-<?= ($booking['status'] ?? '') === 'paid' ? 'success' : 'warning' ?>">
                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['status'] ?? ''))) ?>
                </span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Payment Status</small>
                <span class="badge bg-<?= ($booking['payment_status'] ?? '') === 'paid' ? 'success' : (($booking['payment_status'] ?? '') === 'partial' ? 'warning' : 'secondary') ?>">
                    <?= htmlspecialchars(ucfirst($booking['payment_status'] ?? 'pending')) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- 7-Stage Visual Stepper -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-road me-2"></i>Registry Milestones</span>
        <small class="text-muted ms-2">Click a stage to update status & add notes</small>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="registry-stepper">
            <?php foreach ($milestones as $index => $milestone): 
                $num = $milestone['stage_number'] ?? ($index + 1);
                $status = $milestone['status'] ?? 'pending';
                $isCompleted = $status === 'completed';
                $isInProgress = $status === 'in_progress';
                $isFirst = $index === 0;
                $isLast = $index === $totalStages - 1;
                $color = $stageColors[$num] ?? 'primary';
                $icon = $stageIcons[$num] ?? 'fas fa-circle';
                $badgeClass = $statusBadges[$status] ?? 'secondary';
            ?>
            <div class="stepper-item <?= $isFirst ? '' : 'ms-0' ?>" data-stage="<?= $num ?>">
                <!-- Connector line (except last) -->
                <?php if (!$isLast): ?>
                <div class="stepper-connector">
                    <div class="connector-line <?= $isCompleted ? 'completed' : '' ?>"></div>
                </div>
                <?php endif; ?>

                <div class="stepper-content d-flex flex-column flex-md-row align-items-md-center">
                    <!-- Step Circle & Number -->
                    <div class="stepper-circle-wrapper d-flex flex-column align-items-center me-md-4 mb-3 mb-md-0" style="min-width: 80px;">
                        <div class="stepper-circle <?= $isCompleted ? 'completed' : ($isInProgress ? 'in-progress' : '') ?> border-<?= $color ?>">
                            <i class="<?= $icon ?>"></i>
                            <span class="step-number"><?= $num ?></span>
                        </div>
                        <div class="step-status mt-2">
                            <span class="badge bg-<?= $badgeClass ?> status-badge"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                        </div>
                    </div>

                    <!-- Step Details -->
                    <div class="stepper-details flex-grow-1 mb-3 mb-md-0">
                        <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($milestone['stage_name'] ?? 'Stage ' . $num) ?></h6>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($milestone['description'] ?? '') ?></p>
                        
                        <!-- Key info badges -->
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <?php if (!empty($milestone['required'])): ?>
                            <span class="badge bg-light text-dark border"><i class="fas fa-lock me-1"></i>Required</span>
                            <?php endif; ?>
                            
                            <?php 
                            // Show related payment info for stages 1-3
                            if ($num === 1 && isset($booking['booking_amount'])): ?>
                            <span class="badge bg-light text-dark border">₹<?= number_format($booking['booking_amount'], 0) ?> Token</span>
                            <?php elseif ($num === 2 && isset($booking['agreement_value'])): ?>
                            <span class="badge bg-light text-dark border">₹<?= number_format($booking['agreement_value'], 0) ?> ATS</span>
                            <?php elseif ($num === 3): ?>
                            <span class="badge bg-light text-dark border">₹<?= number_format(floatval($booking['total_plot_value'] ?? 0) - floatval($booking['total_paid'] ?? 0), 0) ?> Balance</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action / Status Display -->
                    <div class="stepper-actions d-flex flex-column align-items-md-end gap-2">
                        <?php if ($isCompleted): ?>
                            <div class="text-end">
                                <small class="text-success d-block">
                                    <i class="fas fa-check-circle me-1"></i>Completed
                                    <?php if (!empty($milestone['completed_date'])): ?>
                                        on <?= date('d M Y', strtotime($milestone['completed_date'])) ?>
                                    <?php endif; ?>
                                </small>
                                <?php if (!empty($milestone['reference_no'])): ?>
                                    <small class="text-muted">Ref: <?= htmlspecialchars($milestone['reference_no']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-<?= $color ?> update-milestone-btn"
                                    data-stage="<?= $num ?>"
                                    data-booking-id="<?= (int)($booking['id'] ?? 0) ?>"
                                    data-status="<?= $status ?>">
                                <i class="fas fa-edit me-1"></i>Update Status
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Inline Edit Form (hidden by default) -->
                <div class="milestone-edit-form d-none" id="edit-form-<?= $num ?>">
                    <form method="POST" action="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/registry-milestone/<?= $num ?>" class="p-3 border-top bg-light">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Status</label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="skipped" <?= $status === 'skipped' ? 'selected' : '' ?>>Skipped</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Completed Date</label>
                                <input type="date" name="completed_date" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($milestone['completed_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Reference No.</label>
                                <input type="text" name="reference_no" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($milestone['reference_no'] ?? '') ?>" placeholder="Bahi No / Challan No">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Notes</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Add notes..."><?= htmlspecialchars($milestone['notes'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Document Path/URL</label>
                                <input type="text" name="document_path" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($milestone['document_path'] ?? '') ?>" placeholder="Path to uploaded PDF/scan">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-secondary cancel-edit-btn" data-stage="<?= $num ?>">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-<?= $color ?>">
                                <i class="fas fa-save me-1"></i>Save Milestone
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Payment Schedule Summary (Stages 1-3) -->
<?php if (!empty($schedule['installments'])): ?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-calendar-alt me-2"></i>Payment Schedule (Stages 1-3 Tracking)</span>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Paid Date</th>
                        <th>Receipt</th>
                        <th>Stage Mapping</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule['installments'] as $i => $inst): 
                        $stageMap = '';
                        if (($i + 1) <= 3) $stageMap = 'Stage ' . ($i + 1);
                        elseif (($i + 1) <= 5) $stageMap = 'Stage 3 (Installment)';
                        else $stageMap = 'Stage 3 (Balance)';
                        $paid = ($inst['paid_amount'] ?? 0) > 0;
                    ?>
                    <tr class="<?= $paid ? 'table-success' : '' ?>">
                        <td><?= $i + 1 ?></td>
                        <td><?= date('d M Y', strtotime($inst['due_date'])) ?></td>
                        <td>₹<?= number_format(floatval($inst['amount'] ?? 0), 2) ?></td>
                        <td>
                            <span class="badge bg-<?= $paid ? 'success' : (($inst['status'] ?? '') === 'overdue' ? 'danger' : 'warning') ?>">
                                <?= htmlspecialchars(ucfirst($inst['status'] ?? 'pending')) ?>
                            </span>
                        </td>
                        <td><?= $inst['paid_date'] ? date('d M Y', strtotime($inst['paid_date'])) : '—' ?></td>
                        <td><?= $inst['receipt_number'] ? htmlspecialchars($inst['receipt_number']) : '—' ?></td>
                        <td><small class="text-muted"><?= $stageMap ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Eligibility Summary (Stage 7) -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-gavel me-2"></i>Registry Eligibility (Stage 7 Gate)</span>
    </div>
    <div class="aps-cp-card-body">
        <?php if ($eligibility['eligible'] ?? false): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-1">Eligible for Final Registry & Possession</h6>
                    <p class="mb-0 text-muted">All financial obligations met. Proceed with NOC generation and registry completion.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-1">Not Eligible for Final Stage</h6>
                    <p class="mb-0 text-muted">Resolve blocking issues before proceeding to mutation & possession.</p>
                </div>
            </div>
            <?php if (!empty($eligibility['reasons'])): ?>
                <ul class="mt-3 mb-0">
                    <?php foreach ($eligibility['reasons'] as $reason): ?>
                        <li class="text-danger"><i class="fas fa-times-circle me-2"></i><?= htmlspecialchars($reason) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.registry-stepper {
    padding: 1.5rem;
}

.stepper-item {
    position: relative;
    padding: 1rem 0;
}

.stepper-connector {
    position: absolute;
    left: 39px;
    top: 70px;
    bottom: 0;
    width: 2px;
    z-index: 0;
}

.connector-line {
    height: 100%;
    background: #e9ecef;
    border-radius: 1px;
    transition: background 0.3s;
}

.connector-line.completed {
    background: linear-gradient(180deg, #0d6efd 0%, #198754 100%);
}

.stepper-content {
    position: relative;
    z-index: 1;
}

.stepper-circle-wrapper {
    position: relative;
}

.stepper-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 3px solid #dee2e6;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #6c757d;
    transition: all 0.3s ease;
    position: relative;
}

.stepper-circle.completed {
    border-color: #198754;
    background: #198754;
    color: #fff;
}

.stepper-circle.in-progress {
    border-color: #ffc107;
    background: #fff3cd;
    color: #ffc107;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    50% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
}

.step-number {
    position: absolute;
    font-size: 0.7rem;
    font-weight: 700;
    color: #fff;
    background: #6c757d;
    padding: 1px 5px;
    border-radius: 10px;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
}

.stepper-circle.completed .step-number {
    background: #198754;
}

.stepper-circle.in-progress .step-number {
    background: #ffc107;
    color: #000;
}

.step-status .status-badge {
    font-size: 0.65rem;
    padding: 0.25rem 0.5rem;
}

.milestone-edit-form {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .stepper-connector {
        left: 24px;
        top: 60px;
    }
    .stepper-circle {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }
    .stepper-content {
        flex-direction: column;
        align-items: flex-start !important;
    }
    .stepper-details {
        margin-left: 0 !important;
        width: 100%;
    }
    .stepper-actions {
        align-items: flex-start !important;
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit buttons
    document.querySelectorAll('.update-milestone-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const stage = this.dataset.stage;
            document.getElementById('edit-form-' + stage).classList.remove('d-none');
            this.closest('.stepper-actions').classList.add('d-none');
        });
    });

    // Cancel buttons
    document.querySelectorAll('.cancel-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const stage = this.dataset.stage;
            document.getElementById('edit-form-' + stage).classList.add('d-none');
            document.querySelector('.update-milestone-btn[data-stage="' + stage + '"]')?.closest('.stepper-actions')?.classList.remove('d-none');
        });
    });

    // Auto-submit on status change (optional)
    document.querySelectorAll('.milestone-edit-form select[name="status"]').forEach(select => {
        select.addEventListener('change', function() {
            if (this.value === 'completed') {
                const dateInput = this.closest('form').querySelector('input[name="completed_date"]');
                if (!dateInput.value) {
                    dateInput.value = new Date().toISOString().split('T')[0];
                }
            }
        });
    });
});
</script>