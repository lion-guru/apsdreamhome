<?php $leaves = $leaves ?? []; $status_filter = $status_filter ?? ''; ?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Leave History</h1>
  <form class="row g-2 mb-4" method="get">
    <div class="col-auto">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="pending" <?= $status_filter==='pending'?'selected':'' ?>>Pending</option>
        <option value="approved" <?= $status_filter==='approved'?'selected':'' ?>>Approved</option>
        <option value="rejected" <?= $status_filter==='rejected'?'selected':'' ?>>Rejected</option>
        <option value="cancelled" <?= $status_filter==='cancelled'?'selected':'' ?>>Cancelled</option>
      </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
  </form>
  <div class="card aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Approved By</th></tr></thead>
        <tbody>
          <?php if (empty($leaves)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No records</td></tr>
          <?php else: ?>
            <?php foreach ($leaves as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['employee_name'] ?? '') ?></td>
                <td><?= ucfirst($l['leave_type'] ?? '') ?></td>
                <td><?= $l['start_date'] ?? '' ?></td>
                <td><?= $l['end_date'] ?? '' ?></td>
                <td><?= $l['total_days'] ?? '' ?></td>
                <td><span class="badge bg-<?= ($l['status'] ?? '') === 'approved' ? 'success' : (($l['status'] ?? '') === 'rejected' ? 'danger' : (($l['status'] ?? '') === 'cancelled' ? 'secondary' : 'warning')) ?>"><?= ucfirst($l['status'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($l['approver_name'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
