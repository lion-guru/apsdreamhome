<?php
/**
 * Weekly Roster Assignment View
*/

$page_title = $page_title ?? 'Weekly Roster Assignment';
$employees = $employees ?? [];
$shift_types = $shift_types ?? [];
$today = $today ?? date('Y-m-d');
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Weekly Roster Assignment</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Bulk Assign Weekly Shifts</h6>
                        <p class="mb-0">Assign the same shift type to an employee for multiple consecutive weeks. Existing shifts on those dates will be skipped.</p>
                    </div>

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
                                    <option value="">Select Shift Type</option>
                                    <?php foreach ($shift_types as $st): ?>
                                    <option value="<?= $st['id'] ?>">
                                        <?= htmlspecialchars($st['name']) ?> (<?= date('g:i A', strtotime($st['start_time'])) ?> - <?= date('g:i A', strtotime($st['end_time'])) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($today) ?>" required min="<?= date('Y-m-d') ?>">
                                <small class="text-muted">Starting week (Monday of the week)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Weeks <span class="text-danger">*</span></label>
                                <input type="number" name="weeks" class="form-control" value="4" min="1" max="12" required>
                                <small class="text-muted">Number of consecutive weeks (1-12)</small>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/admin/shift-roster/roster" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Roster
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-week me-1"></i> Assign Weekly Roster
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