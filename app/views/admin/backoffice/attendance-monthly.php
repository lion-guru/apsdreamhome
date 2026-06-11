<?php
$records = $records ?? [];
$employees = $employees ?? [];
$month = $month ?? date('Y-m');
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Monthly Attendance - <?= $month ?></h1>
  <form class="row g-2 mb-4" method="get">
    <div class="col-auto">
      <input type="month" name="month" class="form-control" value="<?= $month ?>">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Filter</button>
    </div>
    <div class="col-auto">
      <a href="<?= BASE_URL ?>/admin/backoffice/attendance/monthly/export?month=<?= $month ?>" class="btn btn-outline-success"><i class="fas fa-download me-1"></i>Export CSV</a>
    </div>
  </form>

  <div class="card aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>Employee</th><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>OT</th><th>Late</th></tr></thead>
        <tbody>
          <?php if (empty($records)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No records for this month</td></tr>
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
