<?php
/**
 * Holiday Calendar Create
*/

$page_title = $page_title ?? 'Add Holiday';
$holiday_types = $holiday_types ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.form-section { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; background: #fafafa; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Holiday</h4>
            <p class="text-muted mb-0 small">Create a new holiday entry</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/holiday-calendar" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Calendar
        </a>
    </div>

    <form method="POST" id="holidayForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-calendar-plus me-2"></i>Holiday Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., New Year's Day">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">Select type</option>
                            <?php foreach ($holiday_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Region</label>
                        <select name="region" class="form-select">
                            <option value="">All Regions</option>
                            <option value="IN">India</option>
                            <option value="US">United States</option>
                            <option value="UK">United Kingdom</option>
                            <option value="SG">Singapore</option>
                            <option value="AE">UAE</option>
                            <option value="global">Global</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Half Day</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_half_day" id="is_half_day" value="1">
                            <label class="form-check-label" for="is_half_day">Half day holiday</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Working Day</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_working_day" id="is_working_day" value="1">
                            <label class="form-check-label" for="is_working_day">Count as working day</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-align-left me-2"></i>Description</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Recurring Holiday</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_recurring" id="is_recurring" value="1">
                            <label class="form-check-label" for="is_recurring">Recurs annually</label>
                        </div>
                    </div>
                    <div class="col-md-6" id="recurrenceGroup" style="display: none;">
                        <label class="form-label fw-semibold">Recurrence Rule</label>
                        <input type="text" name="recurrence_rule" class="form-control" placeholder="e.g., FREQ=YEARLY;BYMONTH=1;BYMONTHDAY=1">
                        <div class="form-text">iCal RRULE format (optional)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/holiday-calendar" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Create Holiday
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const recurringCheckbox = document.getElementById('is_recurring');
    const recurrenceGroup = document.getElementById('recurrenceGroup');
    
    function toggleRecurrence() {
        recurrenceGroup.style.display = recurringCheckbox.checked ? 'block' : 'none';
    }
    
    recurringCheckbox.addEventListener('change', toggleRecurrence);
    toggleRecurrence(); // Initial state
    
    // Form validation
    document.getElementById('holidayForm').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
});
</script>