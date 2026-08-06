<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-check text-primary me-2"></i>Daily Attendance</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/hr/attendance/report?month=<?= date('m') ?>&year=<?= date('Y') ?>" class="btn btn-outline-info bg-white shadow-sm me-2">
                <i class="fas fa-calendar-alt me-1"></i> Monthly Report
            </a>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#markModal">
                <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Mark Attendance
            </button>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="row mb-4">
        <?php 
            // Simple counts logic if available
            $present = 0; $absent = 0; $halfday = 0;
            if(!empty($records)) {
                foreach($records as $r) {
                    if(($r['attendance_status']??'') === 'present') $present++;
                    if(($r['attendance_status']??'') === 'absent') $absent++;
                    if(($r['attendance_status']??'') === 'half_day') $halfday++;
                }
            }
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Records</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($records ?? []) ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Present Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $present ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Absent Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $absent ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Half Day</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $halfday ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-adjust fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list me-2"></i>Attendance Roster</h6>
            <form method="GET" class="d-flex align-items-center">
                <div class="input-group input-group-sm me-2" style="width: 200px;">
                    <span class="input-group-text bg-light"><i class="fas fa-calendar"></i></span>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>" onchange="this.form.submit()">
                </div>
                <select name="status" class="form-select form-select-sm me-2" style="width: 130px;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="present" <?= ($status_filter ?? '') === 'present' ? 'selected' : '' ?>>Present</option>
                    <option value="absent" <?= ($status_filter ?? '') === 'absent' ? 'selected' : '' ?>>Absent</option>
                    <option value="late" <?= ($status_filter ?? '') === 'late' ? 'selected' : '' ?>>Late</option>
                    <option value="half_day" <?= ($status_filter ?? '') === 'half_day' ? 'selected' : '' ?>>Half Day</option>
                    <option value="leave" <?= ($status_filter ?? '') === 'leave' ? 'selected' : '' ?>>Leave</option>
                </select>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x d-block mb-3 text-gray-300" aria-hidden="true"></i>No attendance records found for this date.</td></tr>
                        <?php else: ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 15px;">
                                                <?= strtoupper(substr(htmlspecialchars($r['employee_name'] ?? 'E'), 0, 1)) ?>
                                            </div>
                                            <strong><?= htmlspecialchars($r['employee_name'] ?? '') ?></strong>
                                        </div>
                                    </td>
                                    <td><?= date('d M Y', strtotime($r['attendance_date'] ?? 'now')) ?></td>
                                    <td>
                                        <?php
                                            $status = $r['attendance_status'] ?? '';
                                            $badgeClass = match($status) {
                                                'present' => 'success',
                                                'absent' => 'danger',
                                                'half_day' => 'warning',
                                                'late' => 'warning',
                                                'leave' => 'info',
                                                default => 'secondary'
                                            };
                                            $icon = match($status) {
                                                'present' => 'check-circle',
                                                'absent' => 'times-circle',
                                                'half_day' => 'adjust',
                                                'late' => 'clock',
                                                'leave' => 'plane-departure',
                                                default => 'circle'
                                            };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>-subtle text-<?= $badgeClass ?> border border-<?= $badgeClass ?>-subtle rounded-pill px-3 py-1">
                                            <i class="fas fa-<?= $icon ?> me-1"></i> <?= ucwords(str_replace('_', ' ', $status)) ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><i class="far fa-clock text-success me-1"></i> <?= htmlspecialchars(date('h:i A', strtotime($r['check_in_time'] ?? '00:00'))) ?></span></td>
                                    <td>
                                        <?php if(!empty($r['check_out_time'])): ?>
                                            <span class="badge bg-light text-dark border"><i class="far fa-clock text-danger me-1"></i> <?= htmlspecialchars(date('h:i A', strtotime($r['check_out_time']))) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 150px;" title="<?= htmlspecialchars($r['remarks'] ?? '') ?>">
                                            <?= htmlspecialchars($r['remarks'] ?? '-') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (($total_pages ?? 1) > 1): ?>
            <div class="card-footer bg-white py-3">
                <nav><ul class="pagination pagination-sm mb-0 justify-content-end">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="?date=<?= urlencode($date ?? date('Y-m-d')) ?>&status=<?= urlencode($status_filter ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/attendance/mark">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title"><i class="fas fa-user-check me-2"></i>Mark Attendance</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select select2-modal" required style="width: 100%;">
                                <option value="">Select Employee...</option>
                                <?php foreach ($users ?? [] as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <label class="form-label text-muted small fw-bold text-uppercase d-block mb-3">Attendance Status</label>
                            <div class="d-flex flex-wrap gap-2 status-buttons">
                                <input type="radio" class="btn-check" name="status" id="stat_present" value="present" checked>
                                <label class="btn btn-outline-success px-4 rounded-pill" for="stat_present"><i class="fas fa-check-circle me-1"></i> Present</label>

                                <input type="radio" class="btn-check" name="status" id="stat_absent" value="absent">
                                <label class="btn btn-outline-danger px-4 rounded-pill" for="stat_absent"><i class="fas fa-times-circle me-1"></i> Absent</label>

                                <input type="radio" class="btn-check" name="status" id="stat_halfday" value="half_day">
                                <label class="btn btn-outline-warning px-4 rounded-pill" for="stat_halfday"><i class="fas fa-adjust me-1"></i> Half Day</label>
                                
                                <input type="radio" class="btn-check" name="status" id="stat_late" value="late">
                                <label class="btn btn-outline-warning px-4 rounded-pill" for="stat_late"><i class="fas fa-clock me-1"></i> Late</label>

                                <input type="radio" class="btn-check" name="status" id="stat_leave" value="leave">
                                <label class="btn btn-outline-info px-4 rounded-pill" for="stat_leave"><i class="fas fa-plane-departure me-1"></i> Leave</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Check In Time</label>
                            <input type="time" name="check_in" class="form-control" value="<?= date('H:i') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Check Out Time</label>
                            <input type="time" name="check_out" class="form-control">
                            <small class="text-muted">Leave blank if not checked out yet</small>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold text-uppercase">Remarks / Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Reason for late/absent, or any special notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-2"></i>Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if($.fn.select2) {
        $('.select2-modal').select2({
            dropdownParent: $('#markModal'),
            theme: 'bootstrap-5'
        });
    }
});
</script>
