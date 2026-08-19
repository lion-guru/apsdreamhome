<?php
$colony     = $colony ?? [];
$rera       = $rera ?? null;
$milestones = $milestones ?? [];
$stats      = $stats ?? ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'delayed' => 0, 'pending' => 0];

$validStatuses = ['pending', 'in_progress', 'completed', 'delayed', 'on_hold'];
$statusLabels = [
  'pending'     => 'Pending',
  'in_progress' => 'In Progress',
  'completed'   => 'Completed',
  'delayed'     => 'Delayed',
  'on_hold'     => 'On Hold',
];
$milestoneTypeLabels = [
  'registration'          => 'Registration',
  'layout_approval'       => 'Layout Approval',
  'construction_start'    => 'Construction Start',
  'plinth_completion'     => 'Plinth Completion',
  'structure_completion'  => 'Structure Completion',
  'finishing_start'       => 'Finishing Start',
  'completion_certificate'=> 'Completion Certificate',
  'occupancy_certificate' => 'Occupancy Certificate',
  'handover'              => 'Handover',
];
$statusColors = [
  'pending'     => 'secondary',
  'in_progress' => 'primary',
  'completed'   => 'success',
  'delayed'     => 'danger',
  'on_hold'     => 'warning',
];
$milestoneIcons = [
  'registration'          => 'fa-stamp',
  'layout_approval'       => 'fa-drafting-compass',
  'construction_start'    => 'fa-hard-hat',
  'plinth_completion'     => 'fa-cube',
  'structure_completion'  => 'fa-building',
  'finishing_start'       => 'fa-paint-roller',
  'completion_certificate'=> 'fa-clipboard-check',
  'occupancy_certificate' => 'fa-certificate',
  'handover'              => 'fa-key',
  'default'               => 'fa-flag-checkered',
];

function inr($n) { return '₹' . number_format($n); }
$completionPct = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;
?>

<div class="container-fluid py-4">
  <!-- Flash Messages -->
  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle me-1"></i> <?= $_SESSION['flash_success'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small">
        <i class="fas fa-arrow-left me-1"></i> Back to Colony
      </a>
      <h2 class="mb-1"><i class="fas fa-tasks me-2 text-danger"></i>RERA Milestone Tracker</h2>
      <small class="text-muted"><?= htmlspecialchars($colony['name'] ?? '') ?>
        <?php if ($rera): ?>
          — RERA: <?= htmlspecialchars($rera['rera_number'] ?? '') ?>
        <?php endif; ?>
      </small>
    </div>
    <div>
      <?php if (!$rera): ?>
        <a href="/admin/legal-colony-pipeline/rera/<?= $colony['id'] ?? 0 ?>" class="btn btn-warning">
          <i class="fas fa-stamp me-1"></i> Register RERA First
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!$rera): ?>
    <!-- No RERA registered -->
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <i class="fas fa-stamp fa-4x text-muted opacity-25 mb-3"></i>
        <h4 class="text-muted">RERA Not Registered Yet</h4>
        <p class="text-muted mb-3">Register RERA for this colony to start tracking milestones.</p>
        <a href="/admin/legal-colony-pipeline/rera/<?= $colony['id'] ?? 0 ?>" class="btn btn-primary">
          <i class="fas fa-stamp me-1"></i> Register RERA
        </a>
      </div>
    </div>
  <?php else: ?>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
          <div class="small text-muted">Total Milestones</div>
          <h3 class="mb-0 text-dark"><?= $stats['total'] ?></h3>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
          <div class="small text-muted">Completed</div>
          <h3 class="mb-0 text-success"><?= $stats['completed'] ?></h3>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
          <div class="small text-muted">In Progress</div>
          <h3 class="mb-0 text-primary"><?= $stats['in_progress'] ?></h3>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
          <div class="small text-muted">Delayed</div>
          <h3 class="mb-0 text-danger"><?= $stats['delayed'] ?></h3>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
          <div class="small text-muted">Pending</div>
          <h3 class="mb-0 text-secondary"><?= $stats['pending'] ?></h3>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center p-3">
          <div class="small text-muted">Completion</div>
          <h3 class="mb-0 text-info"><?= $completionPct ?>%</h3>
          <div class="progress mt-1" class="style-51910">
            <div class="progress-bar bg-info" class="style-17987"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Milestone Timeline -->
    <?php if (empty($milestones)): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
          <i class="fas fa-calendar-check fa-4x text-muted opacity-25 mb-3"></i>
          <h4 class="text-muted">No Milestones Defined</h4>
          <p class="text-muted">Milestones will be created when you register RERA.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-stream me-2"></i>Milestone Timeline</h6>
        </div>
        <div class="card-body">
          <div class="timeline">
            <?php foreach ($milestones as $i => $m):
              $status     = $m['status'] ?? 'pending';
              $color      = $statusColors[$status] ?? 'secondary';
              $label      = $statusLabels[$status] ?? ucfirst($status);
              $type       = $m['milestone_type'] ?? 'default';
              $icon       = $milestoneIcons[$type] ?? $milestoneIcons['default'];
              $planned    = $m['planned_date'] ?? '';
              $completed  = $m['actual_date'] ?? $m['completion_date'] ?? '';
              $isDelayed  = ($status === 'delayed' || ($planned && $planned < date('Y-m-d') && $status !== 'completed'));
              $daysLeft   = $planned ? (strtotime($planned) - strtotime(date('Y-m-d'))) / 86400 : null;
            ?>
              <div class="timeline-item mb-4 position-relative ps-5">
                <!-- Timeline dot -->
                <div class="position-absolute top-0 start-0 translate-middle">
                  <div class="rounded-circle bg-<?= $color ?> d-flex align-items-center justify-content-center" class="style-20148">
                    <i class="fas <?= $icon ?> text-white fa-sm"></i>
                  </div>
                  <?php if ($i < count($milestones) - 1): ?>
                    <div class="position-absolute top-100 start-50 translate-middle-x bg-<?= $color ?>-subtle" class="style-90913"></div>
                  <?php endif; ?>
                </div>

                <!-- Card -->
                <div class="card border <?= $isDelayed ? 'border-danger' : 'border-light' ?> shadow-sm">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h6 class="mb-1"><?= htmlspecialchars($m['milestone_name'] ?? ucfirst(str_replace('_', ' ', $type))) ?></h6>
                        <div class="small text-muted">
                          <?= $milestoneTypeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) ?>
                          <?php if (!empty($m['description'])): ?>
                            — <?= htmlspecialchars($m['description'] ?? '') ?>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="text-end">
                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                        <?php if ($isDelayed && $status !== 'completed'): ?>
                          <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Overdue by <?= abs(round($daysLeft)) ?> days</small>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <div class="small">
                        <span class="text-muted"><i class="fas fa-calendar me-1"></i> Planned: <?= $planned ?: '—' ?></span>
                        <?php if ($completed): ?>
                          <span class="text-success ms-3"><i class="fas fa-check-circle me-1"></i> Completed: <?= $completed ?></span>
                        <?php endif; ?>
                        <?php if (!empty($m['estimated_cost'])): ?>
                          <span class="text-muted ms-3"><i class="fas fa-rupee-sign me-1"></i> <?= inr($m['estimated_cost']) ?></span>
                        <?php endif; ?>
                      </div>

                      <!-- Status update buttons -->
                      <div class="btn-group btn-group-sm">
                        <?php if ($status !== 'completed'): ?>
                          <button class="btn btn-outline-success" onclick="updateMilestone(<?= $m['id'] ?>, 'completed')" title="Mark Complete">
                            <i class="fas fa-check"></i>
                          </button>
                        <?php endif; ?>
                        <?php if ($status !== 'in_progress'): ?>
                          <button class="btn btn-outline-primary" onclick="updateMilestone(<?= $m['id'] ?>, 'in_progress')" title="Start">
                            <i class="fas fa-play"></i>
                          </button>
                        <?php endif; ?>
                        <?php if ($status !== 'delayed'): ?>
                          <button class="btn btn-outline-danger" onclick="updateMilestone(<?= $m['id'] ?>, 'delayed')" title="Mark Delayed">
                            <i class="fas fa-exclamation"></i>
                          </button>
                        <?php endif; ?>
                        <?php if ($status !== 'on_hold'): ?>
                          <button class="btn btn-outline-warning" onclick="updateMilestone(<?= $m['id'] ?>, 'on_hold')" title="Hold">
                            <i class="fas fa-pause"></i>
                          </button>
                        <?php endif; ?>
                      </div>
                    </div>

                    <?php if (!empty($m['remarks'])): ?>
                      <div class="mt-2 p-2 bg-light rounded small text-muted">
                        <i class="fas fa-comment me-1"></i> <?= htmlspecialchars($m['remarks'] ?? '') ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<script>
function updateMilestone(id, status) {
  const notes = prompt('Add notes (optional):') || '';
  fetch('/admin/legal-colony-pipeline/update-milestone', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `milestone_id=${id}&status=${status}&notes=${encodeURIComponent(notes)}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      showToast('Error: ' + (data.error || 'Unknown'), 'danger');
    }
  })
  .catch(e => showToast('Request failed', 'danger'));
}
</script>
