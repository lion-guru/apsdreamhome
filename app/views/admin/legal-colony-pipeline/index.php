<?php
$colonies    = $colonies ?? [];
$stats       = $stats ?? [];
$filterStage = $filter_stage ?? null;
$stages      = $stages ?? [];
?>
<div class="container-fluid py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><i class="fas fa-gavel me-2 text-warning"></i>Legal Colony Development Pipeline</h1>
      <small class="text-muted">7-Phase Legal Colony Development: Land → Planning → Plots → RERA → Dev → Pricing → Sales</small>
    </div>
    <a href="/admin/legal-colony-pipeline/start-acquisition" class="btn btn-warning">
      <i class="fas fa-plus me-1"></i> Start New Acquisition
    </a>
  </div>

  <!-- Stats Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card border-0 shadow-sm bg-gradient-primary text-white">
        <div class="card-body text-center py-3">
          <div class="fs-3 fw-bold"><?= (int)($stats['total'] ?? 0) ?></div>
          <small>Total Colonies</small>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm <?= ($filterStage === 'land_acquisition') ? 'bg-warning' : 'bg-dark' ?> text-white">
        <a href="/admin/legal-colony-pipeline?stage=land_acquisition" class="text-white text-decoration-none">
          <div class="card-body text-center py-3">
            <div class="fs-3 fw-bold"><?= (int)($stats['land_acq'] ?? 0) ?></div>
            <small><i class="fas fa-file-contract me-1"></i> Land Acquisition</small>
          </div>
        </a>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm <?= ($filterStage === 'master_planning') ? 'bg-info' : 'bg-dark' ?> text-white">
        <a href="/admin/legal-colony-pipeline?stage=master_planning" class="text-white text-decoration-none">
          <div class="card-body text-center py-3">
            <div class="fs-3 fw-bold"><?= (int)($stats['planning'] ?? 0) ?></div>
            <small><i class="fas fa-drafting-compass me-1"></i> Planning</small>
          </div>
        </a>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm <?= ($filterStage === 'plot_cutting') ? 'bg-primary' : 'bg-dark' ?> text-white">
        <a href="/admin/legal-colony-pipeline?stage=plot_cutting" class="text-white text-decoration-none">
          <div class="card-body text-center py-3">
            <div class="fs-3 fw-bold"><?= (int)($stats['plot_cut'] ?? 0) ?></div>
            <small><i class="fas fa-vector-square me-1"></i> Plot Cutting</small>
          </div>
        </a>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm <?= ($filterStage === 'rera_registration') ? 'bg-danger' : 'bg-dark' ?> text-white">
        <a href="/admin/legal-colony-pipeline?stage=rera_registration" class="text-white text-decoration-none">
          <div class="card-body text-center py-3">
            <div class="fs-3 fw-bold"><?= (int)($stats['rera'] ?? 0) ?></div>
            <small><i class="fas fa-stamp me-1"></i> RERA</small>
          </div>
        </a>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm <?= ($filterStage === 'pricing') ? 'bg-success' : 'bg-dark' ?> text-white">
        <a href="/admin/legal-colony-pipeline?stage=pricing" class="text-white text-decoration-none">
          <div class="card-body text-center py-3">
            <div class="fs-3 fw-bold"><?= (int)($stats['pricing'] ?? 0) ?></div>
            <small><i class="fas fa-tag me-1"></i> Pricing</small>
          </div>
        </a>
      </div>
    </div>
  </div>

  <!-- Pipeline Stages Visual -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <div class="d-flex align-items-center justify-content-between">
        <?php
        $stageKeys = ['land_acquisition', 'master_planning', 'plot_cutting', 'rera_registration', 'development', 'pricing', 'sales_ready'];
        $stageIcons = ['fa-file-contract', 'fa-drafting-compass', 'fa-vector-square', 'fa-stamp', 'fa-hard-hat', 'fa-tag', 'fa-rocket'];
        $stageLabels = ['Land Acquisition', 'Master Planning', 'Plot Cutting', 'RERA Reg.', 'Development', 'Pricing', 'Sales Ready'];
        $stageColors = ['warning', 'info', 'primary', 'danger', 'secondary', 'success', 'dark'];
        foreach ($stageKeys as $i => $sk): ?>
          <div class="text-center flex-fill">
            <div class="rounded-circle bg-<?= $stageColors[$i] ?> d-inline-flex align-items-center justify-content-center mb-1" style="width:40px;height:40px;">
              <i class="fas <?= $stageIcons[$i] ?> text-white"></i>
            </div>
            <div class="small fw-bold text-<?= $stageColors[$i] ?>"><?= $stageLabels[$i] ?></div>
            <div class="small text-muted"><?= (int)($stats[str_replace(['_registration', ''], ['', ''], $sk)] ?? 0) ?> colonies</div>
          </div>
          <?php if ($i < count($stageKeys) - 1): ?>
            <i class="fas fa-arrow-right text-muted mx-1"></i>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Filtered Colonies Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <strong><i class="fas fa-list me-1"></i> Colonies <?= $filterStage ? '— ' . ucfirst(str_replace('_', ' ', $filterStage)) : 'All Stages' ?></strong>
      <?php if ($filterStage): ?>
        <a href="/admin/legal-colony-pipeline" class="btn btn-sm btn-outline-light"><i class="fas fa-times me-1"></i> Clear Filter</a>
      <?php endif; ?>
    </div>
    <div class="table-responsive">
      <table class="table table-dark table-hover mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Colony</th>
            <th>Location</th>
            <th>Pipeline Stage</th>
            <th>Plots</th>
            <th>Available</th>
            <th>Sold</th>
            <th>Dev Cost</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($colonies)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No colonies found. Start a new land acquisition to begin.</td></tr>
          <?php else: ?>
            <?php foreach ($colonies as $i => $c): ?>
              <?php
                $stage = $c['pipeline_stage'] ?? 'land_acquisition';
                $stageLabel = ucfirst(str_replace('_', ' ', $stage));
                $stageBadge = match($stage) {
                  'land_acquisition'  => 'bg-warning text-dark',
                  'master_planning'   => 'bg-info text-white',
                  'plot_cutting'      => 'bg-primary text-white',
                  'rera_registration' => 'bg-danger text-white',
                  'development'       => 'bg-secondary text-white',
                  'pricing'           => 'bg-success text-white',
                  'sales_ready'       => 'bg-dark text-white',
                  default             => 'bg-secondary text-white',
                };
              ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <strong><?= htmlspecialchars($c['name'] ?? '') ?></strong>
                  <?php if (!empty($c['land_owner_name'])): ?>
                    <br><small class="text-muted">Owner: <?= htmlspecialchars($c['land_owner_name']) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($c['location'] ?? $c['name'] ?? '') ?></td>
                <td><span class="badge <?= $stageBadge ?>"><?= $stageLabel ?></span></td>
                <td><?= (int)($c['plot_count'] ?? 0) ?></td>
                <td class="text-success"><?= (int)($c['available_count'] ?? 0) ?></td>
                <td class="text-danger"><?= (int)($c['sold_count'] ?? 0) ?></td>
                <td>₹<?= number_format(floatval($c['dev_cost_total'] ?? 0)) ?></td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="/admin/legal-colony-pipeline/detail/<?= $c['id'] ?>" class="btn btn-outline-info" title="Pipeline Detail">
                      <i class="fas fa-project-diagram"></i>
                    </a>
                    <?php if ($stage === 'land_acquisition'): ?>
                      <a href="/admin/legal-colony-pipeline/master-plan/<?= $c['id'] ?>" class="btn btn-outline-warning" title="Create Master Plan">
                        <i class="fas fa-drafting-compass"></i>
                      </a>
                    <?php elseif ($stage === 'master_planning'): ?>
                      <a href="/admin/legal-colony-pipeline/plot-cutting/<?= $c['id'] ?>" class="btn btn-outline-primary" title="Plot Cutting">
                        <i class="fas fa-vector-square"></i>
                      </a>
                    <?php elseif ($stage === 'plot_cutting'): ?>
                      <a href="/admin/legal-colony-pipeline/rera/<?= $c['id'] ?>" class="btn btn-outline-danger" title="RERA Registration">
                        <i class="fas fa-stamp"></i>
                      </a>
                    <?php elseif ($stage === 'rera_registration' || $stage === 'development'): ?>
                      <a href="/admin/legal-colony-pipeline/development/<?= $c['id'] ?>" class="btn btn-outline-secondary" title="Record Costs">
                        <i class="fas fa-hard-hat"></i>
                      </a>
                    <?php elseif ($stage === 'pricing' || $stage === 'development'): ?>
                      <a href="/admin/legal-colony-pipeline/pricing/<?= $c['id'] ?>" class="btn btn-outline-success" title="Apply Pricing">
                        <i class="fas fa-tag"></i>
                      </a>
                    <?php endif; ?>
                    <a href="/admin/legal-colony-pipeline/readiness/<?= $c['id'] ?>" class="btn btn-outline-light" title="Sales Readiness">
                      <i class="fas fa-clipboard-check"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
