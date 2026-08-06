<?php
$data     = $data ?? [];
$colonies = $data['colonies'] ?? [];
$summary  = $data['summary'] ?? [];
$colonyHealth = $colony_health ?? [];

function inr($n) { return '₹' . number_format($n); }

$stageColors = [
  'land_acquisition'  => '#ffc107',
  'master_planning'   => '#17a2b8',
  'plot_cutting'      => '#0d6efd',
  'rera_registration' => '#dc3545',
  'development'       => '#6c757d',
  'pricing'           => '#198754',
  'sales_ready'       => '#212529',
];
$stageLabels = [
  'land_acquisition'  => 'Land Acquisition',
  'master_planning'   => 'Master Planning',
  'plot_cutting'      => 'Plot Cutting',
  'rera_registration' => 'RERA Registration',
  'development'       => 'Development',
  'pricing'           => 'Pricing',
  'sales_ready'       => 'Sales Ready',
];
$stageCounts = [];
foreach ($colonies as $c) {
    $s = $c['pipeline_stage'] ?? 'land_acquisition';
    $stageCounts[$s] = ($stageCounts[$s] ?? 0) + 1;
}
?>

<div class="container-fluid py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="/admin/legal-colony-pipeline" class="text-decoration-none text-muted small">
        <i class="fas fa-arrow-left me-1"></i> Back to Pipeline
      </a>
      <h2 class="mb-1"><i class="fas fa-balance-scale me-2 text-primary"></i>Colony Analytics Comparison</h2>
      <small class="text-muted">Compare health, revenue, cost, and profit across all colonies</small>
    </div>
    <a href="/admin/legal-colony-pipeline/health" class="btn btn-sm btn-outline-danger">
      <i class="fas fa-heartbeat me-1"></i> Health Dashboard
    </a>
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

  <!-- Charts Row -->
  <div class="row g-3 mb-4">
    <!-- Health Score Comparison -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-heartbeat me-2 text-danger"></i>Health Score Comparison</h6>
        </div>
        <div class="card-body">
          <canvas id="healthChart" height="220"></canvas>
        </div>
      </div>
    </div>

    <!-- Pipeline Stage Distribution -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-info"></i>Pipeline Stage Distribution</h6>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <canvas id="stageChart" height="220"></canvas>
        </div>
      </div>
    </div>

    <!-- Profit Margin Comparison -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-success"></i>Profit Margin Comparison</h6>
        </div>
        <div class="card-body">
          <canvas id="roiChart" height="220"></canvas>
        </div>
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
                <th class="text-center">Health</th>
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
              $stageLabel = $stageLabels[$c['pipeline_stage'] ?? ''] ?? ucfirst(str_replace('_', ' ', $c['pipeline_stage'] ?? ''));
              $stageColorClass = [
                'land_acquisition'  => 'warning',
                'master_planning'   => 'info',
                'plot_cutting'      => 'primary',
                'rera_registration' => 'danger',
                'development'       => 'secondary',
                'pricing'           => 'success',
                'sales_ready'       => 'dark',
              ][$c['pipeline_stage'] ?? ''] ?? 'secondary';
              $hid = (int)($c['id'] ?? 0);
              $health = $colonyHealth[$hid] ?? null;
              ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($c['name'] ?? '') ?></strong>
                  <br><small class="text-muted"><?= htmlspecialchars($c['location'] ?? '') ?></small>
                </td>
                <td class="text-center">
                  <span class="badge bg-<?= $stageColorClass ?>"><?= $stageLabel ?></span>
                </td>
                <td class="text-center">
                  <?php if ($health): ?>
                    <span class="fw-bold" style="color:<?= $health['grade_color'] ?>;"><?= $health['score'] ?>% (<?= $health['grade'] ?>)</span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
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
                <td colspan="3">TOTAL</td>
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

<!-- Chart.js CDN -->
<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // ── Health Score Chart ──
  const healthData = <?= json_encode(array_map(function($c) use ($colonyHealth) {
      $hid = (int)($c['id'] ?? 0);
      $h = $colonyHealth[$hid] ?? null;
      return [
          'name'  => $c['name'] ?? '',
          'score' => $h['score'] ?? 0,
          'color' => $h['grade_color'] ?? '#6c757d',
      ];
  }, $colonies)) ?>;

  if (healthData.length > 0) {
    new Chart(document.getElementById('healthChart'), {
      type: 'bar',
      data: {
        labels: healthData.map(d => d.name.length > 12 ? d.name.substring(0,12) + '…' : d.name),
        datasets: [{
          label: 'Health %',
          data: healthData.map(d => d.score),
          backgroundColor: healthData.map(d => d.color + '99'),
          borderColor: healthData.map(d => d.color),
          borderWidth: 2,
          borderRadius: 6,
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
        }
      }
    });
  }

  // ── Stage Distribution Doughnut ──
  const stageData = <?= json_encode($stageCounts) ?>;
  const stageLabels = <?= json_encode($stageLabels) ?>;
  const stageColors = <?= json_encode($stageColors) ?>;
  const stageKeys = Object.keys(stageData);

  if (stageKeys.length > 0) {
    new Chart(document.getElementById('stageChart'), {
      type: 'doughnut',
      data: {
        labels: stageKeys.map(k => stageLabels[k] || k),
        datasets: [{
          data: stageKeys.map(k => stageData[k]),
          backgroundColor: stageKeys.map(k => stageColors[k] || '#6c757d'),
          borderWidth: 2,
          borderColor: '#fff',
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 10 } } }
        }
      }
    });
  }

  // ── Profit Margin Chart ──
  const roiData = <?= json_encode(array_map(function($c) {
      return [
          'name'   => $c['name'] ?? '',
          'margin' => $c['profit_margin'] ?? 0,
          'profit' => $c['gross_profit'] ?? 0,
      ];
  }, $colonies)) ?>;

  if (roiData.length > 0) {
    new Chart(document.getElementById('roiChart'), {
      type: 'bar',
      data: {
        labels: roiData.map(d => d.name.length > 12 ? d.name.substring(0,12) + '…' : d.name),
        datasets: [{
          label: 'Margin %',
          data: roiData.map(d => d.margin),
          backgroundColor: roiData.map(d => d.margin >= 30 ? '#19875499' : (d.margin >= 15 ? '#ffc10799' : '#dc354599')),
          borderColor: roiData.map(d => d.margin >= 30 ? '#198754' : (d.margin >= 15 ? '#ffc107' : '#dc3545')),
          borderWidth: 2,
          borderRadius: 6,
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => v + '%' } }
        }
      }
    });
  }
});
</script>
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
