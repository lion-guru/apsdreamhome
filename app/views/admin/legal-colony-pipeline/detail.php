<?php
$colony      = $colony ?? null;
$pipeline    = $pipeline ?? ['stages' => [], 'current_stage' => '', 'plot_stats' => [], 'readiness' => []];
$acquisition = $acquisition ?? null;
$layout      = $layout ?? null;
$rera        = $rera ?? null;
$dev_costs   = $dev_costs ?? ['count' => 0, 'total' => 0, 'total_gst' => 0, 'total_tds' => 0, 'total_paid' => 0];
$milestones  = $milestones ?? [];
$feasibility = $feasibility ?? ['success' => false];
$landLeads   = $landLeads ?? ['success' => false, 'data' => [], 'count' => 0];
$health      = $health ?? ['success' => false, 'overall_score' => 0, 'grade' => ['letter' => '-', 'label' => 'N/A', 'color' => 'secondary'], 'risks' => [], 'recommendations' => []];
$stages      = $pipeline['stages'] ?? [];
$currentStage = $pipeline['current_stage'] ?? '';
$plotStats   = $pipeline['plot_stats'] ?? [];
$readiness   = $pipeline['readiness'] ?? ['checks' => [], 'readiness_pct' => 0, 'is_ready' => false];
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
      <a href="/admin/legal-colony-pipeline" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
      <h2 class="mb-1"><i class="fas fa-gavel me-2 text-warning"></i><?= htmlspecialchars($colony['name'] ?? 'Colony') ?></h2>
      <small class="text-muted">Pipeline Stage: <strong class="text-white"><?= ucfirst(str_replace('_', ' ', $currentStage)) ?></strong></small>
    </div>
    <div class="text-end">
      <?php if (!empty($health['success'])): ?>
        <div class="mb-2">
          <a href="/admin/legal-colony-pipeline/health" class="text-decoration-none">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-<?= $health['grade']['color'] ?> text-white fw-bold" class="style-43996" title="Health: <?= $health['grade']['label'] ?> (<?= $health['overall_score'] ?>%)">
              <?= $health['grade']['letter'] ?>
            </span>
          </a>
          <div class="small text-muted mt-1">Health: <?= $health['overall_score'] ?>%</div>
          <?php if (!empty($health['risks'])): ?>
            <div class="small text-danger"><i class="fas fa-exclamation-triangle"></i> <?= count($health['risks']) ?> risk(s)</div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($colony['rera_number'])): ?>
        <span class="badge bg-danger fs-6"><i class="fas fa-stamp me-1"></i> RERA: <?= htmlspecialchars($colony['rera_number'] ?? '') ?></span>
      <?php endif; ?>
      <div class="small text-muted mt-1">Owner: <?= htmlspecialchars($colony['land_owner_name'] ?? 'N/A') ?></div>
    </div>
  </div>

  <!-- Pipeline Progress Bar -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center position-relative">
        <?php
        $stageKeys   = ['land_acquisition', 'master_planning', 'plot_cutting', 'rera_registration', 'development', 'pricing', 'sales_ready'];
        $stageIcons  = ['fa-file-contract', 'fa-drafting-compass', 'fa-vector-square', 'fa-stamp', 'fa-hard-hat', 'fa-tag', 'fa-rocket'];
        $stageLabels = ['Land Acquisition', 'Master Planning', 'Plot Cutting', 'RERA Reg.', 'Development', 'Pricing', 'Sales Ready'];
        $stageColors = ['warning', 'info', 'primary', 'danger', 'secondary', 'success', 'dark'];
        $currentIdx  = array_search($currentStage, $stageKeys);
        if ($currentIdx === false) $currentIdx = 0;
        foreach ($stageKeys as $i => $sk):
          $isDone    = ($i < $currentIdx);
          $isCurrent = ($i === $currentIdx);
          $isActive  = ($i <= $currentIdx);
        ?>
          <div class="text-center flex-fill position-relative" class="style-67772">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 <?= $isDone ? "bg-{$stageColors[$i]}" : ($isCurrent ? "bg-{$stageColors[$i]} border border-3 border-white" : 'bg-secondary') ?>" class="style-62485">
              <i class="fas <?= $isDone ? 'fa-check' : $stageIcons[$i] ?> <?= $isActive ? 'text-white' : 'text-muted' ?> <?= $isCurrent ? 'text-white fa-bounce' : '' ?>"></i>
            </div>
            <div class="small fw-bold <?= $isActive ? "text-{$stageColors[$i]}" : 'text-muted' ?>"><?= $stageLabels[$i] ?></div>
            <?php if ($isDone): ?>
              <div class="small text-success"><i class="fas fa-check-circle"></i></div>
            <?php elseif ($isCurrent): ?>
              <div class="small text-warning"><i class="fas fa-arrow-circle-up"></i> Current</div>
            <?php else: ?>
              <div class="small text-muted"><i class="fas fa-circle"></i></div>
            <?php endif; ?>
          </div>
          <?php if ($i < count($stageKeys) - 1): ?>
            <div class="flex-fill position-relative" class="style-46866">
              <div class="progress" class="style-70208">
                <div class="progress-bar bg-<?= $isDone ? $stageColors[$i] : 'secondary' ?>" class="style-90537"></div>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-info"><?= (int)($plotStats['total'] ?? 0) ?></div>
          <small>Total Plots</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-success"><?= (int)($plotStats['available'] ?? 0) ?></div>
          <small>Available</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-danger"><?= (int)($plotStats['sold'] ?? 0) ?></div>
          <small>Sold</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-dark text-white">
        <div class="card-body text-center py-3">
          <div class="fs-2 fw-bold text-warning">₹<?= number_format(floatval($dev_costs['total'] ?? 0)) ?></div>
          <small>Development Cost</small>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left: Phase Details -->
    <div class="col-lg-8">
      <!-- Phase 1: Land Acquisition -->
      <div class="card border-0 shadow-sm mb-4 <?= $currentStage === 'land_acquisition' ? 'border-warning' : '' ?>">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><i class="fas fa-file-contract me-1 text-warning"></i> Phase 1: Land Acquisition</strong>
          <a href="/admin/legal-colony-pipeline/start-acquisition" class="btn btn-sm btn-warning"><i class="fas fa-plus me-1"></i> New</a>
        </div>
        <div class="card-body">
          <?php if ($acquisition): ?>
            <div class="row g-3">
              <div class="col-md-4">
                <small class="text-muted d-block">Status</small>
                <span class="badge bg-<?= $acquisition['status'] === 'registered' ? 'success' : ($acquisition['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= ucfirst($acquisition['status']) ?></span>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Area</small>
                <strong><?= number_format(floatval($acquisition['total_area_sqft'] ?? 0)) ?> sqft</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Cost</small>
                <strong>₹<?= number_format(floatval($acquisition['total_consideration'] ?? 0)) ?></strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Advance Paid</small>
                <strong class="text-success">₹<?= number_format(floatval($acquisition['advance_paid'] ?? 0)) ?></strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Balance</small>
                <strong class="text-danger">₹<?= number_format(floatval($acquisition['balance_amount'] ?? 0)) ?></strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Registration #</small>
                <strong><?= htmlspecialchars($acquisition['registration_number'] ?: 'N/A') ?></strong>
              </div>
              <div class="col-md-12">
                <small class="text-muted d-block">Mutation</small>
                <span class="badge bg-<?= $acquisition['mutation_status'] === 'completed' ? 'success' : 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $acquisition['mutation_status'])) ?></span>
              </div>
            </div>
          <?php else: ?>
            <p class="text-muted mb-2">No land acquisition record for this colony.</p>
          <?php endif; ?>
          <?php if (!empty($landLeads['data'])): ?>
            <hr class="border-secondary">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="text-warning mb-0"><i class="fas fa-search me-1"></i> Land Leads (<?= $landLeads['count'] ?>)</h6>
              <a href="/admin/land-inventory/leads" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="table-responsive">
              <table class="table table-dark table-sm mb-0">
                <thead><tr><th>Owner</th><th>Village</th><th>Area</th><th>Expected</th><th>Status</th></tr></thead>
                <tbody>
                  <?php foreach (array_slice($landLeads['data'], 0, 5) as $lead): ?>
                    <tr>
                      <td><?= htmlspecialchars($lead['land_owner_name'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($lead['village'] ?? '-') ?></td>
                      <td><?= number_format(floatval($lead['area_acres'] ?? 0)) ?> acres</td>
                      <td>₹<?= number_format(floatval($lead['expected_price'] ?? 0)) ?></td>
                      <td><span class="badge bg-<?= ($lead['status'] ?? '') === 'registered' ? 'success' : 'warning' ?>"><?= ucfirst($lead['status'] ?? 'new') ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Phase 2: Master Planning -->
      <div class="card border-0 shadow-sm mb-4 <?= $currentStage === 'master_planning' ? 'border-info' : '' ?>">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><i class="fas fa-drafting-compass me-1 text-info"></i> Phase 2: Master Planning</strong>
          <a href="/admin/legal-colony-pipeline/master-plan/<?= $colony['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit me-1"></i> Create/Update</a>
        </div>
        <div class="card-body">
          <?php if ($layout): ?>
            <div class="row g-3">
              <div class="col-md-4"><small class="text-muted d-block">Layout Name</small><strong><?= htmlspecialchars($layout['layout_name'] ?? '') ?></strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Version</small><strong><?= htmlspecialchars($layout['version'] ?? '') ?></strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Type</small><strong><?= ucfirst($layout['layout_type']) ?></strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Road Area</small><strong><?= $layout['road_area_pct'] ?>%</strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Common Area</small><strong><?= $layout['common_area_pct'] ?>%</strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Status</small><span class="badge bg-<?= $layout['status'] === 'approved' ? 'success' : 'secondary' ?>"><?= ucfirst($layout['status']) ?></span></div>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">No master plan created yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Phase 3: Plot Cutting -->
      <div class="card border-0 shadow-sm mb-4 <?= $currentStage === 'plot_cutting' ? 'border-primary' : '' ?>">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><i class="fas fa-vector-square me-1 text-primary"></i> Phase 3: Plot Cutting</strong>
          <a href="/admin/legal-colony-pipeline/plot-cutting/<?= $colony['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-cut me-1"></i> Generate</a>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3"><small class="text-muted d-block">Total Plots</small><strong class="text-info fs-4"><?= (int)($plotStats['total'] ?? 0) ?></strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Available</small><strong class="text-success fs-4"><?= (int)($plotStats['available'] ?? 0) ?></strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Sold</small><strong class="text-danger fs-4"><?= (int)($plotStats['sold'] ?? 0) ?></strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Total Value</small><strong class="text-warning fs-4">₹<?= number_format(floatval($plotStats['total_value'] ?? 0) / 100000, 1) ?>L</strong></div>
          </div>
        </div>
      </div>

      <!-- Phase 4: RERA Registration -->
      <div class="card border-0 shadow-sm mb-4 <?= $currentStage === 'rera_registration' ? 'border-danger' : '' ?>">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><i class="fas fa-stamp me-1 text-danger"></i> Phase 4: RERA Registration</strong>
          <a href="/admin/legal-colony-pipeline/rera/<?= $colony['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-plus me-1"></i> Register</a>
        </div>
        <div class="card-body">
          <?php if ($rera): ?>
            <div class="row g-3">
              <div class="col-md-4"><small class="text-muted d-block">RERA Number</small><strong class="text-danger"><?= htmlspecialchars($rera['rera_number'] ?? '') ?></strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Builder</small><strong><?= htmlspecialchars($rera['builder_name'] ?? '') ?></strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Status</small><span class="badge bg-success"><?= $rera['status'] ?></span></div>
              <div class="col-md-4"><small class="text-muted d-block">Registration Date</small><strong><?= $rera['registration_date'] ?></strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Validity</small><strong><?= $rera['validity_date'] ?></strong></div>
              <div class="col-md-4"><small class="text-muted d-block">Total Units</small><strong><?= $rera['total_units'] ?></strong></div>
            </div>
            <?php if (!empty($milestones)): ?>
              <hr>
              <h6 class="text-muted mb-3">Milestones</h6>
              <div class="table-responsive">
                <table class="table table-dark table-sm mb-0">
                  <thead><tr><th>Milestone</th><th>Type</th><th>Planned</th><th>Status</th></tr></thead>
                  <tbody>
                    <?php foreach ($milestones as $m): ?>
                      <tr>
                        <td><?= htmlspecialchars($m['milestone_name'] ?? '') ?></td>
                        <td><span class="badge bg-secondary"><?= $m['milestone_type'] ?></span></td>
                        <td><?= $m['planned_date'] ?></td>
                        <td><span class="badge bg-<?= $m['status'] === 'completed' ? 'success' : ($m['status'] === 'delayed' ? 'danger' : 'warning') ?>"><?= ucfirst($m['status']) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <p class="text-muted mb-0">No RERA registration yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Phase 5: Development -->
      <div class="card border-0 shadow-sm mb-4 <?= $currentStage === 'development' ? 'border-secondary' : '' ?>">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><i class="fas fa-hard-hat me-1 text-secondary"></i> Phase 5: Development Costs</strong>
          <a href="/admin/legal-colony-pipeline/development/<?= $colony['id'] ?>" class="btn btn-sm btn-secondary"><i class="fas fa-plus me-1"></i> Record Cost</a>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3"><small class="text-muted d-block">Total Costs</small><strong><?= (int)($dev_costs['count'] ?? 0) ?> entries</strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Gross Amount</small><strong>₹<?= number_format(floatval($dev_costs['total'] ?? 0)) ?></strong></div>
            <div class="col-md-3"><small class="text-muted d-block">GST</small><strong>₹<?= number_format(floatval($dev_costs['total_gst'] ?? 0)) ?></strong></div>
            <div class="col-md-3"><small class="text-muted d-block">Paid</small><strong class="text-success">₹<?= number_format(floatval($dev_costs['total_paid'] ?? 0)) ?></strong></div>
          </div>
        </div>
      </div>

      <!-- Phase 6: Pricing -->
      <div class="card border-0 shadow-sm mb-4 <?= $currentStage === 'pricing' ? 'border-success' : '' ?>">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><i class="fas fa-tag me-1 text-success"></i> Phase 6: Pricing</strong>
          <div>
            <a href="/admin/colony-feasibility/<?= $colony['id'] ?>" class="btn btn-sm btn-outline-success me-1"><i class="fas fa-calculator me-1"></i> Feasibility</a>
            <a href="/admin/legal-colony-pipeline/pricing/<?= $colony['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-dollar-sign me-1"></i> Apply Pricing</a>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4"><small class="text-muted d-block">Starting Price</small><strong class="text-success fs-4">₹<?= number_format(floatval($colony['starting_price'] ?? 0)) ?></strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Min Price/sqft</small><strong>₹<?= number_format(floatval($colony['min_price_per_sqft'] ?? 0)) ?></strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Total Land Cost</small><strong>₹<?= number_format(floatval($colony['estimated_land_cost'] ?? 0)) ?></strong></div>
          </div>
          <?php if (!empty($feasibility['success'])): ?>
            <hr class="border-secondary">
            <h6 class="text-success mb-3"><i class="fas fa-chart-line me-1"></i> Feasibility Analysis (ColonyFeasibilityService)</h6>
            <div class="row g-3">
              <div class="col-md-3">
                <div class="card bg-dark border-secondary">
                  <div class="card-body text-center py-2">
                    <small class="text-muted">Cost Basis/sqft</small>
                    <div class="fs-5 fw-bold text-info">₹<?= number_format($feasibility['raw_cost_per_sqft'] ?? 0) ?></div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card bg-dark border-secondary">
                  <div class="card-body text-center py-2">
                    <small class="text-muted">Markup Factor</small>
                    <div class="fs-5 fw-bold text-warning"><?= $feasibility['markup_factor'] ?? 0 ?>x</div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card bg-dark border-secondary">
                  <div class="card-body text-center py-2">
                    <small class="text-muted">Revenue Projection</small>
                    <div class="fs-5 fw-bold text-success">₹<?= number_format($feasibility['total_revenue'] ?? 0) ?></div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card bg-dark border-secondary">
                  <div class="card-body text-center py-2">
                    <small class="text-muted">Total Costs</small>
                    <div class="fs-5 fw-bold text-danger">₹<?= number_format($feasibility['total_cost_basis'] ?? 0) ?></div>
                  </div>
                </div>
              </div>
            </div>
            <?php if (!empty($feasibility['breakdown'])): ?>
              <div class="mt-2">
                <small class="text-muted">
                  Land: ₹<?= number_format($feasibility['breakdown']['land'] ?? 0) ?> |
                  Registry: ₹<?= number_format($feasibility['breakdown']['registry'] ?? 0) ?> |
                  Dev: ₹<?= number_format($feasibility['breakdown']['dev'] ?? 0) ?> |
                  Approvals: ₹<?= number_format($feasibility['breakdown']['approvals'] ?? 0) ?> |
                  G&A: ₹<?= number_format($feasibility['breakdown']['ga_rupees'] ?? 0) ?>
                </small>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <hr class="border-secondary">
            <p class="text-muted mb-2 small"><i class="fas fa-info-circle me-1"></i> Feasibility analysis not yet calculated.</p>
            <a href="/admin/colony-feasibility/<?= $colony['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-calculator me-1"></i> Run Feasibility Analysis</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Phase 7: Sales Readiness -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><i class="fas fa-clipboard-check me-1 text-dark"></i> Phase 7: Sales Readiness</strong>
          <a href="/admin/legal-colony-pipeline/readiness/<?= $colony['id'] ?>" class="btn btn-sm btn-outline-light"><i class="fas fa-eye me-1"></i> Full Checklist</a>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="small">Readiness</span>
              <span class="small fw-bold <?= $readiness['is_ready'] ?? false ? 'text-success' : 'text-warning' ?>"><?= (int)($readiness['readiness_pct'] ?? 0) ?>%</span>
            </div>
            <div class="progress" class="style-51045">
              <div class="progress-bar <?= ($readiness['is_ready'] ?? false) ? 'bg-success' : 'bg-warning' ?>" class="style-91943"></div>
            </div>
          </div>
          <?php foreach (($readiness['checks'] ?? []) as $chk): ?>
            <div class="d-flex align-items-center mb-2">
              <i class="fas <?= ($chk['passed'] ?? false) ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' ?> me-2"></i>
              <span class="small <?= ($chk['passed'] ?? false) ? '' : 'text-muted' ?>"><?= htmlspecialchars($chk['label'] ?? '') ?></span>
              <?php if (!empty($chk['detail'])): ?>
                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($chk['detail'] ?? '') ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Right: Colony Info Sidebar -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-info-circle me-1"></i> Colony Info</strong></div>
        <div class="card-body">
          <table class="table table-dark table-sm mb-0">
            <tr><td class="text-muted">Name</td><td><?= htmlspecialchars($colony['name'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Location</td><td><?= htmlspecialchars($colony['location'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Owner</td><td><?= htmlspecialchars($colony['land_owner_name'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Total Area</td><td><?= number_format(floatval($colony['total_area_acres'] ?? 0)) ?> acres</td></tr>
            <tr><td class="text-muted">Total Area (sqft)</td><td><?= number_format(floatval($colony['total_area_sqft'] ?? 0)) ?></td></tr>
            <tr><td class="text-muted">Estimated Cost</td><td>₹<?= number_format(floatval($colony['estimated_land_cost'] ?? 0)) ?></td></tr>
            <tr><td class="text-muted">Phase</td><td><?= htmlspecialchars($colony['phase'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Block Count</td><td><?= (int)($colony['block_count'] ?? 0) ?></td></tr>
          </table>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white"><strong><i class="fas fa-bolt me-1"></i> Quick Actions</strong></div>
        <div class="list-group list-group-flush">
          <?php if ($currentStage === 'land_acquisition'): ?>
            <a href="/admin/legal-colony-pipeline/master-plan/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
              <i class="fas fa-drafting-compass me-2 text-info"></i> Create Master Plan
            </a>
          <?php endif; ?>
          <?php if ($currentStage === 'master_planning'): ?>
            <a href="/admin/legal-colony-pipeline/plot-cutting/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
              <i class="fas fa-vector-square me-2 text-primary"></i> Generate Plots
            </a>
          <?php endif; ?>
          <?php if ($currentStage === 'plot_cutting'): ?>
            <a href="/admin/legal-colony-pipeline/rera/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
              <i class="fas fa-stamp me-2 text-danger"></i> Register RERA
            </a>
          <?php endif; ?>
          <?php if (in_array($currentStage, ['rera_registration', 'development'])): ?>
            <a href="/admin/legal-colony-pipeline/development/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
              <i class="fas fa-hard-hat me-2 text-secondary"></i> Record Development Cost
            </a>
          <?php endif; ?>
          <?php if (in_array($currentStage, ['development', 'pricing'])): ?>
            <a href="/admin/legal-colony-pipeline/pricing/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
              <i class="fas fa-tag me-2 text-success"></i> Apply Pricing
            </a>
          <?php endif; ?>
          <a href="/admin/legal-colony-pipeline/readiness/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
            <i class="fas fa-clipboard-check me-2 text-warning"></i> Sales Readiness
          </a>
          <a href="/admin/colony-feasibility/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
            <i class="fas fa-chart-line me-2 text-success"></i> Feasibility Calculator
          </a>
          <a href="/admin/legal-colony-pipeline/analytics/<?= $colony['id'] ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
            <i class="fas fa-chart-bar me-2 text-info"></i> Colony Analytics
          </a>
          <a href="/admin/legal-colony-pipeline/health" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
            <i class="fas fa-heartbeat me-2 text-danger"></i> Health Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
