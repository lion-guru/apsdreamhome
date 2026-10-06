<?php
/**
 * Assign Shift View
*/

$page_title = $page_title ?? 'Assign Shift';
$employees = $employees ?? [];
$shift_types = $shift_types ?? [];
$today = $today ?? date('Y-m-d');
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-clock me-2"></i>Assign Shift to Employee</h4>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?> (<?= $emp['employee_code'] ?>) - <?= htmlspecialchars($emp['designation']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Shift Type <span class="text-danger">*</span></label>
                                <select name="shift_type_id" class="form-select" required>
                                    <option value="">Select Shift</option>
                                    <?php foreach ($shift_types as $st): ?>
                                    <option value="<?= $st['id'] ?>" data-start="<?= $st['start_time'] ?>" data-end="<?= $st['end_time'] ?>">
                                        <?= htmlspecialchars($st['name']) ?> (<?= date('g:i A', strtotime($st['start_time'])) ?> - <?= date('g:i A', strtotime($st['end_time'])) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Shift Date <span class="text-danger">*</span></label>
                                <input type="date" name="shift_date" class="form-control" required value="<?= htmlspecialchars($today) ?>" min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control" id="shift_start_time">
                                <small class="text-muted">Leave empty to use shift type default</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control" id="shift_end_time">
                                <small class="text-muted">Leave empty to use shift type default</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/admin/shift-roster/roster" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-clock me-1"></i> Assign Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
</style>

<script>
// Auto-fill start/end times when shift type changes
document.querySelector('select[name="shift_type_id"]').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.dataset.start) {
        document.getElementById('shift_start_time').value = option.dataset.start;
    }
    if (option.dataset.end) {
        document.getElementById('shift_end_time').value = option.dataset.end;
    }
});
</script>