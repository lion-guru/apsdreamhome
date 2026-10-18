<?php
/**
 * Apply Leave Encashment
 */

$page_title = $page_title ?? 'Apply Leave Encashment';
$policy = $policy ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i><?= htmlspecialchars($page_title) ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Convert your unused leave balance to cash. Maximum <?= $policy['max_encashable_days'] ?? 30 ?> days per financial year.</p>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/encashment/apply" id="encashmentApplyForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- Leave Type -->
                        <div class="row mb-3">
                            <label for="leave_type_id" class="col-sm-3 col-form-label fw-bold">Leave Type <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="leave_type_id" name="leave_type_id" required>
                                    <option value="">Select leave type</option>
                                    <?php foreach ($policy['leave_balances'] ?? [] as $lb): ?>
                                        <?php if (($lb['encashable'] ?? 0) > 0): ?>
                                        <option value="<?= $lb['leave_type_id'] ?? 0 ?>" data-balance="<?= $lb['encashable'] ?? 0 ?>" data-rate="<?= $lb['rate'] ?? '100%' ?>">
                                            <?= htmlspecialchars($lb['leave_type'] ?? '') ?> (<?= $lb['encashable'] ?? 0 ?> days available)
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Only leave types with encashable balance are shown.</div>
                            </div>
                        </div>

                        <!-- Days to Encash -->
                        <div class="row mb-3">
                            <label for="days_to_encash" class="col-sm-3 col-form-label fw-bold">Days to Encash <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="days_to_encash" name="days_to_encash" step="0.5" min="0.5" max="<?= $policy['max_encashable_days'] ?? 30 ?>" required>
                                <div class="form-text">Maximum: <?= $policy['max_encashable_days'] ?? 30 ?> days per financial year</div>
                            </div>
                        </div>

                        <!-- Preview Card -->
                        <div class="card bg-light mb-3" id="encashmentPreview" style="display: none;">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="fas fa-calculator me-2"></i>Estimated Encashment Amount</h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="fw-bold text-primary" id="previewDays">0</div>
                                        <small class="text-muted">Days</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold text-info" id="previewRate">100%</div>
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
                                <button type="submit" class="btn btn-success btn-lg me-2">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Request
                                </button>
                                <a href="<?= BASE_URL ?>/employee/self-service/encashment" class="btn btn-outline-secondary btn-lg">
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
    const leaveTypeSelect = document.getElementById('leave_type_id');
    const daysInput = document.getElementById('days_to_encash');
    const previewCard = document.getElementById('encashmentPreview');
    const previewDays = document.getElementById('previewDays');
    const previewRate = document.getElementById('previewRate');
    const previewAmount = document.getElementById('previewAmount');

    function updatePreview() {
        const option = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const days = parseFloat(daysInput.value) || 0;
        
        if (option.value && days > 0) {
            const balance = parseFloat(option.dataset.balance) || 0;
            const rate = option.dataset.rate || '100%';
            
            if (days <= balance) {
                previewCard.style.display = 'block';
                previewDays.textContent = days;
                previewRate.textContent = rate;
                
                // Calculate amount based on daily rate
                const dailyRate = 1000; // placeholder
                const rateMultiplier = parseFloat(rate) / 100 || 1;
                const amount = days * dailyRate * rateMultiplier;
                
                previewAmount.textContent = '₹' + amount.toLocaleString('en-IN', {maximumFractionDigits: 2});
            } else {
                previewCard.style.display = 'none';
            }
        } else {
            previewCard.style.display = 'none';
        }
    }

    leaveTypeSelect.addEventListener('change', updatePreview);
    daysInput.addEventListener('input', updatePreview);

    // Validation
    daysInput.addEventListener('input', function() {
        const option = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const balance = parseFloat(option.dataset.balance) || 0;
        const maxDays = parseFloat(daysInput.max) || 30;
        
        if (this.value > balance) {
            this.setCustomValidity('Cannot exceed available encashable balance (' + balance + ' days)');
        } else if (this.value > maxDays) {
            this.setCustomValidity('Cannot exceed maximum allowed (' + maxDays + ' days)');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>