<?php
/**
 * Apply Overtime
 */

$page_title = $page_title ?? 'Apply Overtime';
$policy = $policy ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-clock me-2"></i><?= htmlspecialchars($page_title) ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Request overtime payment for extra hours worked. Rate: <?= $policy['weekday_rate'] ?? '1.5x' ?> (weekdays), <?= $policy['weekend_rate'] ?? '2x' ?> (weekends/holidays).</p>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/overtime/apply" id="overtimeApplyForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- Date -->
                        <div class="row mb-3">
                            <label for="date" class="col-sm-3 col-form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
                                <div class="form-text">Cannot request overtime for future dates.</div>
                            </div>
                        </div>

                        <!-- Start Time -->
                        <div class="row mb-3">
                            <label for="start_time" class="col-sm-3 col-form-label fw-bold">Start Time <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>

                        <!-- End Time -->
                        <div class="row mb-3">
                            <label for="end_time" class="col-sm-3 col-form-label fw-bold">End Time <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="row mb-3">
                            <label for="reason" class="col-sm-3 col-form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Describe the work done during overtime (project name, task details, etc.)"></textarea>
                            </div>
                        </div>

                        <!-- Preview Card -->
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

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-info text-white btn-lg me-2">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Request
                                </button>
                                <a href="<?= BASE_URL ?>/employee/self-service/overtime" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
            end.setDate(end.getDate() + 1);
        }
        
        const diffMs = end - start;
        const hours = diffMs / (1000 * 60 * 60);
        
        if (hours > 0) {
            const date = new Date(dateInput.value);
            const dayOfWeek = date.getDay();
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
            
            const rate = isWeekend ? 2.0 : 1.5;
            const hourlyRate = 500; // placeholder
            const amount = hours * hourlyRate * rate;
            
            previewHours.textContent = hours.toFixed(2);
            previewRate.textContent = rate + 'x';
            previewAmount.textContent = '₹' + amount.toLocaleString('en-IN', {maximumFractionDigits: 2});
        }
    }

    [startTime, endTime, dateInput].forEach(el => {
        el.addEventListener('change', calculateOvertime);
    });

    calculateOvertime();
});
</script>