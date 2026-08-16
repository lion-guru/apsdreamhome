<?php
$data = $data ?? ['success' => false, 'colonies' => []];
$colonies = $data['colonies'] ?? [];
?>

<div class="container-fluid py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="/admin/legal-colony-pipeline" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
      <h2 class="mb-1"><i class="fas fa-heartbeat me-2 text-danger"></i>Colony Health Dashboard</h2>
      <small class="text-muted">Automated health scoring across 7 pipeline stages</small>
    </div>
    <button class="btn btn-outline-success" onclick="location.reload()"><i class="fas fa-sync me-1"></i> Refresh</button>
  </div>

  <!-- Summary Cards -->
  <?php
  $totalColonies = count($colonies);
  $avgScore = $totalColonies > 0 ? round(array_sum(array_column($colonies, 'overall_score')) / $totalColonies) : 0;
  $highRisk = count(array_filter($colonies, fn($c) => ($c['risk_count'] ?? 0) >= 2));
  $excellent = count(array_filter($colonies, fn($c) => ($c['overall_score'] ?? 0) >= 80));
  ?>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-info"><?= $totalColonies ?></div>
          <small>Total Colonies</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-<?= $avgScore >= 70 ? 'success' : ($avgScore >= 50 ? 'warning' : 'danger') ?>"><?= $avgScore ?>%</div>
          <small>Average Health</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-success"><?= $excellent ?></div>
          <small>Excellent (80+)</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-danger"><?= $highRisk ?></div>
          <small>High Risk (2+ issues)</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Health Alerts (colonies below threshold) -->
  <?php
  $belowThreshold = array_filter($colonies, fn($c) => ($c['overall_score'] ?? 100) < 50);
  if (!empty($belowThreshold)):
  ?>
  <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-start" role="alert">
    <i class="fas fa-exclamation-triangle me-3 mt-1 fa-lg"></i>
    <div class="flex-grow-1">
      <h6 class="alert-heading mb-2">Health Alerts — <?= count($belowThreshold) ?> colony(ies) below 50% threshold</h6>
      <div class="row g-2">
        <?php foreach ($belowThreshold as $alert): ?>
          <div class="col-md-6 col-lg-4">
            <div class="d-flex align-items-center gap-2 p-2 rounded" class="style-60853">
              <span class="badge bg-danger fs-6"><?= $alert['overall_score'] ?? 0 ?>%</span>
              <div>
                <strong><?= htmlspecialchars($alert['name'] ?? '') ?></strong>
                <?php if (!empty($alert['top_risk'])): ?>
                  <br><small class="text-muted"><?= htmlspecialchars($alert['top_risk'] ?? '') ?></small>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <a href="/admin/legal-colony-pipeline/health/alerts" class="btn btn-sm btn-outline-danger ms-3" id="alertDetailsBtn" title="View alert details">
      <i class="fas fa-external-link-alt"></i>
    </a>
  </div>
  <?php endif; ?>

  <!-- Colony Health Grid -->
  <div class="row g-4">
    <?php foreach ($colonies as $colony): ?>
      <?php
      $score = $colony['overall_score'] ?? 0;
      $grade = $colony['grade'] ?? ['letter' => 'F', 'label' => 'Unknown', 'color' => 'secondary'];
      $stage = ucwords(str_replace('_', ' ', $colony['current_stage'] ?? ''));
      ?>
      <div class="col-lg-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="mb-1"><?= htmlspecialchars($colony['name'] ?? '') ?></h5>
                <span class="badge bg-secondary"><?= $stage ?></span>
              </div>
              <div class="text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-<?= $grade['color'] ?> text-white fw-bold" class="style-47547">
                  <?= $grade['letter'] ?>
                </div>
              </div>
            </div>

            <!-- Score Bar -->
            <div class="mb-3">
              <div class="d-flex justify-content-between mb-1">
                <small class="text-muted">Health Score</small>
                <small class="fw-bold text-<?= $grade['color'] ?>"><?= $score ?>%</small>
              </div>
              <div class="progress" class="style-87912">
                <div class="progress-bar bg-<?= $grade['color'] ?>" class="style-12479"></div>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="row g-2 mb-3">
              <div class="col-4">
                <div class="text-center p-2 rounded bg-dark">
                  <div class="fw-bold text-<?= ($colony['risk_count'] ?? 0) > 0 ? 'danger' : 'success' ?>"><?= $colony['risk_count'] ?? 0 ?></div>
                  <small class="text-muted" class="style-48967">Risks</small>
                </div>
              </div>
              <div class="col-4">
                <div class="text-center p-2 rounded bg-dark">
                  <div class="fw-bold text-info"><?= $colony['risk_count'] ?? 0 ?></div>
                  <small class="text-muted" class="style-48967">Issues</small>
                </div>
              </div>
              <div class="col-4">
                <div class="text-center p-2 rounded bg-dark">
                  <div class="fw-bold text-warning"><?= $colony['risk_count'] ?? 0 ?></div>
                  <small class="text-muted" class="style-48967">Alerts</small>
                </div>
              </div>
            </div>

            <!-- Top Risk -->
            <?php if (!empty($colony['top_risk'])): ?>
              <div class="alert alert-danger py-2 px-3 mb-2" class="style-49273">
                <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($colony['top_risk'] ?? '') ?>
              </div>
            <?php endif; ?>

            <!-- Recommendation -->
            <?php if (!empty($colony['recommendation'])): ?>
              <div class="alert alert-info py-2 px-3 mb-0" class="style-49273">
                <i class="fas fa-lightbulb me-1"></i> <?= htmlspecialchars($colony['recommendation'] ?? '') ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="card-footer bg-transparent border-top">
            <div class="d-flex gap-2">
              <a href="/admin/legal-colony-pipeline/detail/<?= $colony['colony_id'] ?>" class="btn btn-sm btn-outline-primary flex-fill">
                <i class="fas fa-eye me-1"></i> Pipeline
              </a>
              <a href="/admin/colony-feasibility/<?= $colony['colony_id'] ?>" class="btn btn-sm btn-outline-success flex-fill">
                <i class="fas fa-calculator me-1"></i> Feasibility
              </a>
              <a href="/admin/legal-colony-pipeline/analytics/<?= $colony['colony_id'] ?>" class="btn btn-sm btn-outline-info flex-fill">
                <i class="fas fa-chart-bar me-1"></i> Analytics
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($colonies)): ?>
    <div class="text-center py-5">
      <i class="fas fa-heartbeat fa-3x text-muted mb-3"></i>
      <h4 class="text-muted">No colonies found</h4>
      <p class="text-muted">Create a colony in the pipeline to see health scores.</p>
    </div>
  <?php endif; ?>
</div>
