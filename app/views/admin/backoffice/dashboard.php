<?php
$stats = $stats ?? [];
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Daily Operations Dashboard</h1>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="fs-1 text-primary"><?= (int)($stats['today_ops'] ?? 0) ?></div>
          <div class="text-muted">Today's Operations</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="fs-1 text-success"><?= (int)($stats['attendance_pct'] ?? 0) ?>%</div>
          <div class="text-muted">Attendance (<?= (int)($stats['present_today'] ?? 0) ?>/<?= (int)($stats['total_employees'] ?? 0) ?>)</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="fs-1 text-warning"><?= (int)($stats['active_leads'] ?? 0) ?></div>
          <div class="text-muted">Active Leads</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="fs-1 text-danger"><?= (int)($stats['pending_leaves'] ?? 0) ?></div>
          <div class="text-muted">Pending Leaves</div>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/attendance" class="card border-0 shadow-sm text-decoration-none">
        <div class="card-body"><i class="fas fa-clock me-2"></i>Attendance</div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/leaves" class="card border-0 shadow-sm text-decoration-none">
        <div class="card-body"><i class="fas fa-calendar-check me-2"></i>Leave Requests</div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/leads" class="card border-0 shadow-sm text-decoration-none">
        <div class="card-body"><i class="fas fa-funnel-dollar me-2"></i>Lead Pipeline</div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/payslips" class="card border-0 shadow-sm text-decoration-none">
        <div class="card-body"><i class="fas fa-money-bill-wave me-2"></i>Payslips</div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/operations" class="card border-0 shadow-sm text-decoration-none">
        <div class="card-body"><i class="fas fa-clipboard-list me-2"></i>Operations Log</div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/reports" class="card border-0 shadow-sm text-decoration-none">
        <div class="card-body"><i class="fas fa-chart-bar me-2"></i>Report Center</div>
      </a>
    </div>
  </div>
</div>
