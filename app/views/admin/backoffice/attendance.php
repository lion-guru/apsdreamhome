<?php
$records = $records ?? [];
$employees = $employees ?? [];
$today = $today ?? date('Y-m-d');
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= __('admin_employee_attendance') ?></h1>
    <a href="<?= BASE_URL ?>/admin/backoffice/attendance/monthly" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar me-1"></i><?= __('admin_monthly_view') ?></a>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header"><strong><?= __('admin_mark_attendance') ?></strong></div>
    <div class="card-body">
      <form id="attendanceForm" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <input type="hidden" name="attendance_date" value="<?= $today ?>">
        <div class="col-md-4">
          <label class="form-label"><?= __('admin_employee_label') ?></label>
          <select name="employee_id" class="form-select" required>
            <option value=""><?= __('admin_select_placeholder') ?></option>
            <?php foreach ($employees as $e): ?>
              <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label"><?= __('admin_status_label') ?></label>
          <select name="status" class="form-select">
            <option value="present"><?= __('admin_present') ?></option>
            <option value="absent"><?= __('admin_absent') ?></option>
            <option value="half_day"><?= __('admin_half_day') ?></option>
            <option value="on_leave"><?= __('admin_on_leave') ?></option>
            <option value="work_from_home"><?= __('admin_wfh') ?></option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label"><?= __('admin_check_in') ?></label>
          <input type="time" name="check_in_time" class="form-control" value="09:30">
        </div>
        <div class="col-md-2">
          <label class="form-label"><?= __('admin_check_out') ?></label>
          <input type="time" name="check_out_time" class="form-control" value="18:00">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check me-1"></i><?= __('admin_mark_button') ?></button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header"><strong><?= __('admin_todays_records') ?></strong></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th><?= __('admin_employee_label') ?></th><th><?= __('admin_date_label') ?></th><th><?= __('admin_status_label') ?></th><th><?= __('admin_check_in') ?></th><th><?= __('admin_check_out') ?></th><th><?= __('admin_hours_label') ?></th><th><?= __('admin_ot_label') ?></th><th><?= __('admin_late_min') ?></th></tr></thead>
        <tbody>
          <?php if (empty($records)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4"><?= __('admin_no_records_today') ?></td></tr>
          <?php else: ?>
            <?php foreach ($records as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['employee_name'] ?? '') ?></td>
                <td><?= $r['attendance_date'] ?? '' ?></td>
                <td><span class="badge bg-<?= ($r['status'] ?? '') === 'present' ? 'success' : (($r['status'] ?? '') === 'absent' ? 'danger' : 'secondary') ?>"><?= ucfirst($r['status'] ?? '') ?></span></td>
                <td><?= $r['check_in_time'] ? date('H:i', strtotime($r['check_in_time'])) : '-' ?></td>
                <td><?= $r['check_out_time'] ? date('H:i', strtotime($r['check_out_time'])) : '-' ?></td>
                <td><?= $r['hours_worked'] ?? '0' ?></td>
                <td><?= $r['overtime_hours'] ?? '0' ?></td>
                <td><?= $r['late_minutes'] ?? '0' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
document.getElementById('attendanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fetch('<?= BASE_URL ?>/admin/backoffice/attendance/record', {method:'POST',body:fd})
    .then(function(r){return r.json()})
    .then(function(d){if(d.success){location.reload()}else{alert(d.error||'Error')}});
});
</script>
