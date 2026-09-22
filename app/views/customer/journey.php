<?php $this->layout = 'layouts/customer'; ?>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Booking Journey</h1>
      <small class="text-muted">
        Plot <?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?> · <?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?>
        · Booking #<?= htmlspecialchars($booking['booking_number'] ?? $booking['id']) ?>
      </small>
    </div>
    <div class="btn-group">
      <a href="/customer/passbook" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Passbook</a>
      <?php if ($can_download): ?>
        <a href="/customer/possession-certificate/<?= (int)$booking['id'] ?>" class="btn btn-outline-success"><i class="fas fa-download me-1"></i> Certificate</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($this->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= $this->getFlash('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
  <?php if ($this->getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= $this->getFlash('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <!-- Overall Progress -->
  <div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Journey Progress</h5>
      <span class="badge bg-primary fs-6"><?= $progress_pct ?>% Complete</span>
    </div>
    <div class="card-body">
      <div class="progress mb-3" style="height: 12px;">
        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $progress_pct ?>%" aria-valuenow="<?= $progress_pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
      <div class="d-flex justify-content-between text-muted small">
        <span><?= $completed_count ?> of <?= count($stages) ?> stages completed</span>
        <span>Current: <?= $current_index >= 0 ? $stages[$current_index]['title'] : 'All done!' ?></span>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left: Payment Ledger & EMI Schedule -->
    <div class="col-xl-8">
      <!-- Stats Cards -->
      <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
          <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="aps-cp-stat-body">
              <div class="aps-cp-stat-label">Total Investment</div>
              <div class="aps-cp-stat-value">₹<?= number_format((float)$stats['total_investment'], 0) ?></div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="aps-cp-stat-body">
              <div class="aps-cp-stat-label">Total Paid</div>
              <div class="aps-cp-stat-value">₹<?= number_format((float)$stats['total_paid'], 0) ?></div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="aps-cp-stat-body">
              <div class="aps-cp-stat-label">Outstanding</div>
              <div class="aps-cp-stat-value">₹<?= number_format((float)$stats['total_outstanding'], 0) ?></div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="aps-cp-stat aps-cp-stat--red">
            <div class="aps-cp-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="aps-cp-stat-body">
              <div class="aps-cp-stat-label">Overdue</div>
              <div class="aps-cp-stat-value"><?= (int)$stats['overdue_count'] ?> EMI (₹<?= number_format((float)$stats['overdue_amount'], 0) ?>)</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Unified Ledger -->
      <div class="card aps-cp-card mb-4">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-book me-2"></i> Unified Payment Ledger</h5>
          <span class="badge bg-info"><?= count($ledger) ?> entries</span>
        </div>
        <div class="card-body p-0">
          <?php if (empty($ledger)): ?>
            <div class="aps-cp-empty text-center py-4">
              <i class="aps-cp-empty-icon fas fa-receipt"></i>
              <h6>No payment records yet</h6>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover aps-cp-table mb-0">
                <thead class="tenant-header">
                  <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-end">Debit (Due)</th>
                    <th class="text-end">Credit (Paid)</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($ledger as $entry): ?>
                    <tr>
                      <td class="text-nowrap"><?= date('d M Y', strtotime($entry['date'])) ?></td>
                      <td>
                        <div class="fw-medium"><?= htmlspecialchars($entry['description']) ?></div>
                        <?php if (!empty($entry['ref'])): ?>
                          <small class="text-muted">Ref: <?= htmlspecialchars($entry['ref']) ?></small>
                        <?php endif; ?>
                      </td>
                      <td class="text-end text-danger fw-medium"><?= $entry['debit'] > 0 ? '₹' . number_format($entry['debit'], 2) : '—' ?></td>
                      <td class="text-end text-success fw-medium"><?= $entry['credit'] > 0 ? '₹' . number_format($entry['credit'], 2) : '—' ?></td>
                      <td class="text-end fw-bold text-<?= (float)$entry['balance'] > 0 ? 'danger' : 'success' ?>">₹<?= number_format((float)$entry['balance'], 2) ?></td>
                      <td>
                        <?php
                          $badge = match($entry['status']) {
                            'paid' => 'aps-cp-badge-green',
                            'overdue' => 'aps-cp-badge-red',
                            'pending' => 'aps-cp-badge-orange',
                            default => 'aps-cp-badge-indigo',
                          };
                        ?>
                        <span class="aps-cp-badge <?= $badge ?>"><?= ucfirst($entry['status']) ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                  <tr>
                    <th colspan="2" class="text-end">Totals</th>
                    <th class="text-end text-danger">₹<?= number_format(array_sum(array_column($ledger, 'debit')), 2) ?></th>
                    <th class="text-end text-success">₹<?= number_format(array_sum(array_column($ledger, 'credit')), 2) ?></th>
                    <th class="text-end fw-bold">₹<?= number_format(max(0, $stats['total_investment'] - $stats['total_paid']), 2) ?></th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Next EMI Quick Action -->
      <?php if ($stats['next_emi_date']): ?>
        <div class="card aps-cp-card mb-4 border-warning">
          <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-bell me-2"></i> Next EMI Due</h6>
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-6">
                <strong>Due Date:</strong> <?= date('d M Y', strtotime($stats['next_emi_date'])) ?>
              </div>
              <div class="col-md-6 text-md-end">
                <a href="/customer/passbook?booking_id=<?= (int)$booking['id'] ?>" class="btn btn-tenant-primary">
                  <i class="fas fa-credit-card me-1"></i> Pay Now
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Right: Registry Timeline -->
    <div class="col-xl-4">
      <div class="card aps-cp-card h-100 sticky-top" style="top: 20px;">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-timeline me-2"></i> Registry Progress</h5>
          <span class="badge bg-<?= $progress_pct >= 100 ? 'success' : 'primary' ?>"><?= $progress_pct ?>%</span>
        </div>
        <div class="card-body p-0">
          <div class="rg-stepper">
            <?php foreach ($stages as $i => $stage): ?>
              <div class="rg-step <?= $stage['completed'] ? 'done' : '' ?> <?= ($stage['in_progress'] ?? false) ? 'current' : '' ?>">
                <div class="rg-dot">
                  <i class="fas <?= $stage['icon'] ?>"></i>
                </div>
                <div class="rg-card">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($stage['title']) ?></h6>
                      <small class="text-muted"><?= htmlspecialchars($stage['description']) ?></small>
                    </div>
                    <div class="text-end">
                      <?php if ($stage['completed']): ?>
                        <span class="aps-cp-badge aps-cp-badge-green"><i class="fas fa-check me-1"></i> Done</span>
                      <?php elseif ($stage['in_progress'] ?? false): ?>
                        <span class="aps-cp-badge aps-cp-badge-orange"><i class="fas fa-spinner fa-spin me-1"></i> In Progress</span>
                      <?php else: ?>
                        <span class="aps-cp-badge aps-cp-badge-indigo"><i class="fas fa-clock me-1"></i> Pending</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php if (!empty($stage['meta'])): ?>
                    <div class="mt-2 small text-muted">
                      <?php foreach ($stage['meta'] as $k => $v): ?>
                        <?php if ($v !== null && $v !== ''): ?>
                          <div><strong><?= ucfirst(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></div>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card-footer bg-light">
          <?php if ($can_download): ?>
            <a href="/customer/possession-certificate/<?= (int)$booking['id'] ?>" class="btn btn-success w-100">
              <i class="fas fa-certificate me-1"></i> Download Possession Certificate
            </a>
          <?php else: ?>
            <button class="btn btn-outline-secondary w-100" disabled>
              <i class="fas fa-lock me-1"></i> Certificate unlocks at Stage 7
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Extras Section -->
  <div class="row g-4 mt-4">
    <?php if (!empty($extras['agreement'])): ?>
      <div class="col-md-6">
        <div class="card aps-cp-card h-100">
          <div class="aps-cp-card-header"><h6 class="mb-0"><i class="fas fa-file-contract me-1"></i> Agreement</h6></div>
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <span>Status</span>
              <span class="badge bg-<?= in_array($extras['agreement']['status'] ?? '', ['signed','executed']) ? 'success' : 'warning' ?>"><?= ucfirst($extras['agreement']['status'] ?? 'draft') ?></span>
            </div>
            <?php if (!empty($extras['agreement']['agreement_date'])): ?>
              <div class="mt-1 small">Date: <?= date('d M Y', strtotime($extras['agreement']['agreement_date'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($extras['agreement']['file_path'])): ?>
              <a href="<?= BASE_URL . '/' . ltrim($extras['agreement']['file_path'], '/') ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">View Agreement</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($extras['noc'])): ?>
      <div class="col-md-6">
        <div class="card aps-cp-card h-100">
          <div class="aps-cp-card-header"><h6 class="mb-0"><i class="fas fa-shield-check me-1"></i> NOC</h6></div>
          <div class="card-body">
            <span class="badge bg-<?= ($extras['noc']['status'] ?? '') === 'approved' ? 'success' : 'warning' ?>"><?= ucfirst($extras['noc']['status'] ?? 'pending') ?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($extras['deed'])): ?>
      <div class="col-md-6">
        <div class="card aps-cp-card h-100">
          <div class="aps-cp-card-header"><h6 class="mb-0"><i class="fas fa-file-signature me-1"></i> Registry Deed</h6></div>
          <div class="card-body">
            <?php if (!empty($extras['deed']['file_path'])): ?>
              <a href="<?= BASE_URL . '/' . ltrim($extras['deed']['file_path'], '/') ?>" target="_blank" class="btn btn-outline-primary">View Deed</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($extras['possession'])): ?>
      <div class="col-md-6">
        <div class="card aps-cp-card h-100">
          <div class="aps-cp-card-header"><h6 class="mb-0"><i class="fas fa-key me-1"></i> Possession</h6></div>
          <div class="card-body">
            <span class="badge bg-<?= in_array($extras['possession']['status'] ?? '', ['handed_over','completed']) ? 'success' : 'warning' ?>"><?= ucfirst($extras['possession']['status'] ?? 'pending') ?></span>
            <?php if (!empty($extras['possession']['possession_date'])): ?>
              <div class="mt-1 small">Date: <?= date('d M Y', strtotime($extras['possession']['possession_date'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.rg-stepper { display:flex; flex-direction:column; }
.rg-step { position:relative; padding:16px 0 16px 48px; }
.rg-step:not(:last-child)::before { content:''; position:absolute; left:22px; top:44px; bottom:0; width:2px; background:#e2e8f0; }
.rg-step.done::before { background:#22c55e; }
.rg-step .rg-dot { position:absolute; left:8px; top:8px; width:32px; height:32px; border-radius:50%; background:#fff; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; color:#64748b; z-index:1; }
.rg-step.done .rg-dot { background:#22c55e; border-color:#22c55e; color:#fff; }
.rg-step.current .rg-dot { border-color:#f59e0b; box-shadow:0 0 0 4px rgba(245,158,11,0.2); }
.rg-step.current .rg-dot i { color:#f59e0b; }
.rg-card { background:#f8fafc; border-radius:12px; padding:16px; border:1px solid #e2e8f0; }
.rg-step.done .rg-card { border-color:#86efac; }
.rg-step.current .rg-card { border-color:#fde68a; }
.aps-cp-badge-green { background:#dcfce7; color:#166534; }
.aps-cp-badge-orange { background:#ffedd5; color:#9a3412; }
.aps-cp-badge-red { background:#fee2e2; color:#991b1b; }
.aps-cp-badge-indigo { background:#e0e7ff; color:#3730a3; }
</style>