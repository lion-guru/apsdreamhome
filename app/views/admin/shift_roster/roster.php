<?php
/**
 * Shift Roster View
*/

$page_title = $page_title ?? 'Shift Roster';
$roster = $roster ?? [];
$employees = $employees ?? [];
$shift_types = $shift_types ?? [];
$start_date = $start_date ?? date('Y-m-d');
$end_date = $end_date ?? date('Y-m-d', strtotime('+7 days'));
$employee_id = $employee_id ?? 0;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-calendar-alt me-2"></i>Shift Roster</h1>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/shift-roster/assign-shift" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Assign Shift
            </a>
            <a href="<?= BASE_URL ?>/admin/shift-roster/weekly-roster" class="btn btn-outline-secondary">
                <i class="fas fa-calendar-week me-1"></i> Weekly Roster
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= $employee_id == $emp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['name']) ?> (<?= $emp['employee_code'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Roster Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($roster)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Shifts Scheduled</h5>
                <p class="text-muted">Assign shifts to employees for the selected date range</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Shift</th>
                            <th>Timing</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roster as $r): ?>
                        <tr>
                            <td>
                                <strong><?= date('d M Y', strtotime($r['shift_date'])) ?></strong>
                                <br><small class="text-muted"><?= date('D', strtotime($r['shift_date'])) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['employee_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($r['employee_code']) ?> | <?= htmlspecialchars($r['designation']) ?></small>
                            </td>
                            <td>
                                <span class="badge me-1" style="background: <?= $r['color'] ?>"><?= htmlspecialchars($r['shift_name']) ?></span>
                            </td>
                            <td>
                                <?= date('g:i A', strtotime($r['start_time'])) ?> - 
                                <?= date('g:i A', strtotime($r['end_time'])) ?>
                            </td>
                            <td><?= $r['duration_hours'] ?> hrs</td>
                            <td>
                                <?php $st = $r['status']; ?>
                                <span class="badge bg-<?= 
                                    $st === 'completed' ? 'success' : 
                                    ($st === 'cancelled' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($st) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-danger" title="Cancel" onclick="if(confirm('Cancel this shift?')) window.location.href='<?= BASE_URL ?>/admin/shift-roster/cancel-shift/<?= $r['id'] ?>'">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
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

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
</style>