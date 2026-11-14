<?php
/** Admin Payroll - Create Run */

$page_title = $page_title ?? 'Create Payroll Run';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-money-bill-wave me-2 text-primary"></i>Create Payroll Run</h4>
            <p class="text-muted mb-0 small">Set up a new payroll period</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payroll
        </a>
    </div>

    <form method="POST" id="payrollForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-2"></i>Payroll Period</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Period Start <span class="text-danger">*</span></label>
                        <input aria-label="Period start" type="date" name="period_start" class="form-control" required value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Period End <span class="text-danger">*</span></label>
                        <input aria-label="Period end" type="date" name="period_end" class="form-control" required value="<?= date('Y-m-t') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pay Date <span class="text-danger">*</span></label>
                        <input aria-label="Pay date" type="date" name="pay_date" class="form-control" required value="<?= date('Y-m-d', strtotime('last day of this month')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
            </div>
            <div class="card-body">
                <textarea aria-label="Notes" name="notes" class="form-control" rows="3" placeholder="Optional notes for this payroll run..."></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/payroll" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Create Payroll Run
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('payrollForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>