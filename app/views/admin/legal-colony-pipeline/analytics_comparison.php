<?php
$data     = $data ?? [];
$colonies = $data['colonies'] ?? [];
$summary  = $data['summary'] ?? [];

function inr($n) { return '₹' . number_format($n); }
?>

<div class="container-fluid py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="/admin/legal-colony-pipeline" class="text-decoration-none text-muted small">
        <i class="fas fa-arrow-left me-1"></i> Back to Pipeline
      </a>
      <h2 class="mb-1"><i class="fas fa-balance-scale me-2 text-primary"></i>Colony Analytics Comparison</h2>
      <small class="text-muted">Compare revenue, cost, and profit across all colonies</small>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="small text-muted">Total Colonies</div>
        <h3 class="mb-0 text-primary"><?= $summary['total_colonies'] ?? 0 ?></h3>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="small text-muted">Total Plots</div>
        <h3 class="mb-0 text-info"><?= $summary['total_plots'] ?? 0 ?></h3>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="small text-muted">Total Sold</div>
        <h3 class="mb-0 text-danger"><?= $summary['total_sold'] ?? 0 ?></h3>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="small text-muted">Revenue</div>
        <h3 class="mb-0 text-success"><?= inr($summary['total_revenue'] ?? 0) ?></h3>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="small text-muted">Total Cost</div>
        <h3 class="mb-0 text-warning"><?= inr($summary['total_cost'] ?? 0) ?></h3>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm text-center p-3">
        <div class="small text-muted">Overall Margin</div>
        <h3 class="mb-0 <?= ($summary['overall_margin'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
          <?= $summary['overall_margin'] ?? 0 ?>%
        </h3>
      </div>
    </div>
  </div>

  <!-- Colony Comparison Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
      <h6 class="mb-0"><i class="fas fa-table me-2"></i>Colony-wise Comparison</h6>
    </div>
    <div class="card-body p-0">
      <?php if (empty($colonies)): ?>
        <div class="text-center text-muted py-5">
          <i class="fas fa-building fa-3x mb-3 opacity-25"></i><br>
          No colonies found. Create colonies in the pipeline first.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Colony</th>
                <th class="text-center">Stage</th>
                <th class="text-end">Area (acres)</th>
                <th class="text-end">Plots</th>
                <th class="text-end">Sold</th>
                <th class="text-center">Occupancy</th>
                <th class="text-end">Land Cost</th>
                <th class="text-end">Dev Cost</th>
                <th class="text-end">Revenue</th>
                <th class="text-end">Profit</th>
                <th class="text-center">Margin</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($colonies as $c): ?>
              <?php
              $margin = $c['profit_margin'] ?? 0;
              $marginClass = $margin >= 30 ? 'text-success' : ($margin >= 15 ? 'text-warning' : 'text-danger');
              $occPct = $c['occupancy_pct'] ?? 0;
              $stageLabel = ucfirst(str_replace('_', ' ', $c['pipeline_stage'] ?? ''));
              $stageColors = [
                'land_acquisition'  => 'warning',
                'master_planning'   => 'info',
                'plot_cutting'      => 'primary',
                'rera_registration' => 'danger',
                'development'       => 'secondary',
                'pricing'           => 'success',
                'sales_ready'       => 'dark',
              ];
              $stageColor = $stageColors[$c['pipeline_stage'] ?? ''] ?? 'secondary';
              ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($c['name'] ?? '') ?></strong>
                  <br><small class="text-muted"><?= htmlspecialchars($c['location'] ?? '') ?></small>
                </td>
                <td class="text-center">
                  <span class="badge bg-<?= $stageColor ?>"><?= $stageLabel ?></span>
                </td>
                <td class="text-end"><?= number_format($c['total_area_acres'] ?? 0, 2) ?></td>
                <td class="text-end"><?= $c['plot_count'] ?? 0 ?></td>
                <td class="text-end text-danger fw-bold"><?= $c['sold_plots'] ?? 0 ?></td>
                <td class="text-center">
                  <div class="progress" style="height:8px;width:80px;display:inline-block">
                    <div class="progress-bar bg-<?= $occPct >= 50 ? 'success' : ($occPct >= 25 ? 'warning' : 'danger') ?>" style="width:<?= $occPct ?>%"></div>
                  </div>
                  <br><small><?= $occPct ?>%</small>
                </td>
                <td class="text-end"><?= inr($c['land_cost'] ?? 0) ?></td>
                <td class="text-end"><?= inr($c['dev_cost'] ?? 0) ?></td>
                <td class="text-end text-success fw-bold"><?= inr($c['total_revenue'] ?? 0) ?></td>
                <td class="text-end fw-bold <?= ($c['gross_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                  <?= inr($c['gross_profit'] ?? 0) ?>
                </td>
                <td class="text-center">
                  <span class="badge <?= $marginClass ?> fs-6"><?= $margin ?>%</span>
                </td>
                <td class="text-center">
                  <a href="/admin/legal-colony-pipeline/analytics/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Analytics">
                    <i class="fas fa-chart-line"></i>
                  </a>
                  <a href="/admin/legal-colony-pipeline/detail/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Detail">
                    <i class="fas fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light fw-bold">
              <tr>
                <td colspan="2">TOTAL</td>
                <td class="text-end"><?= number_format(array_sum(array_column($colonies, 'total_area_acres')), 2) ?></td>
                <td class="text-end"><?= $summary['total_plots'] ?? 0 ?></td>
                <td class="text-end text-danger"><?= $summary['total_sold'] ?? 0 ?></td>
                <td></td>
                <td class="text-end"><?= inr($summary['total_land_cost'] ?? 0) ?></td>
                <td class="text-end"><?= inr($summary['total_dev_cost'] ?? 0) ?></td>
                <td class="text-end text-success"><?= inr($summary['total_revenue'] ?? 0) ?></td>
                <td class="text-end <?= ($summary['total_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                  <?= inr($summary['total_profit'] ?? 0) ?>
                </td>
                <td class="text-center"><?= $summary['overall_margin'] ?? 0 ?>%</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
