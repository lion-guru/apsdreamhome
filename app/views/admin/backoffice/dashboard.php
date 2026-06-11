<?php
$stats = $stats ?? [];
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><?= __('admin_daily_operations_dashboard') ?></h1>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-primary"><?= (int)($stats['today_ops'] ?? 0) ?></div>
          <div class="text-muted"><?= __('admin_todays_operations') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-success"><?= (int)($stats['attendance_pct'] ?? 0) ?>%</div>
          <div class="text-muted"><?= __('admin_attendance_label') ?> (<?= (int)($stats['present_today'] ?? 0) ?>/<?= (int)($stats['total_employees'] ?? 0) ?>)</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-warning"><?= (int)($stats['active_leads'] ?? 0) ?></div>
          <div class="text-muted"><?= __('admin_active_leads') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-1 text-danger"><?= (int)($stats['pending_leaves'] ?? 0) ?></div>
          <div class="text-muted"><?= __('admin_pending_leaves') ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/attendance" class="card aps-cp-card text-decoration-none">
        <div class="card-body"><i class="fas fa-clock me-2"></i><?= __('admin_attendance_label') ?></div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/leaves" class="card aps-cp-card text-decoration-none">
        <div class="card-body"><i class="fas fa-calendar-check me-2"></i><?= __('admin_leave_requests') ?></div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/leads" class="card aps-cp-card text-decoration-none">
        <div class="card-body"><i class="fas fa-funnel-dollar me-2"></i><?= __('admin_lead_pipeline') ?></div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/payslips" class="card aps-cp-card text-decoration-none">
        <div class="card-body"><i class="fas fa-money-bill-wave me-2"></i><?= __('admin_payslips') ?></div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/operations" class="card aps-cp-card text-decoration-none">
        <div class="card-body"><i class="fas fa-clipboard-list me-2"></i><?= __('admin_operations_log') ?></div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="<?= BASE_URL ?>/admin/backoffice/reports" class="card aps-cp-card text-decoration-none">
        <div class="card-body"><i class="fas fa-chart-bar me-2"></i><?= __('admin_report_center') ?></div>
      </a>
    </div>
  </div>
</div>
