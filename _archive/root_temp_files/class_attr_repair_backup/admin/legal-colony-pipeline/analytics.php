<?php
$data = $data ?? [];
$colony        = $data['colony'] ?? [];
$plotStats     = $data['plot_stats'] ?? [];
$devCosts      = $data['dev_costs'] ?? [];
$devTotal      = $data['dev_total'] ?? [];
$landCost      = $data['land_cost'] ?? 0;
$totalCost     = $data['total_cost'] ?? 0;
$totalRevenue  = $data['total_revenue'] ?? 0;
$grossProfit   = $data['gross_profit'] ?? 0;
$profitMargin  = $data['profit_margin'] ?? 0;
$typeBreakdown = $data['type_breakdown'] ?? [];
$blockBreakdown= $data['block_breakdown'] ?? [];
$salesVelocity = $data['sales_velocity'] ?? [];
$milestones    = $data['milestone_progress'] ?? [];
$roi           = $data['roi_projection'] ?? [];

function inr($n) { return '₹' . number_format($n); }
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

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small">
        <i class="fas fa-arrow-left me-1"></i> Back to Colony
      </a>
      <h2 class="mb-1"><i class="fas fa-chart-line me-2 text-success"></i>Colony Analytics</h2>
      <small class="text-muted"><?= htmlspecialchars($colony['name'] ?? '') ?> — <?= htmlspecialchars($colony['location'] ?? '') ?></small>
    </div>
    <div>
      <a href="/admin/legal-colony-pipeline/analytics-all" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-balance-scale me-1"></i> Compare All Colonies
      </a>
      <a href="/admin/legal-colony-pipeline/milestones/<?= $colony['id'] ?? 0 ?>" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-tasks me-1"></i> RERA Milestones
      </a>
    </div>
  </div>

  <!-- Revenue / Cost / Profit Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-gradient-success text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="small opacity-75">Total Revenue (Potential)</div>
              <h3 class="mb-0"><?= inr($totalRevenue) ?></h3>
            </div>
            <i class="fas fa-rupee-sign fa-2x opacity-50"></i>
          </div>
          <div class="mt-2 small opacity-75">
            <?= number_format($plotStats['total'] ?? 0) ?> plots Ã— avg <?= inr($plotStats['avg_price_sqft'] ?? 0) ?>/sqft
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-gradient-danger text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="small opacity-75">Total Cost</div>
              <h3 class="mb-0"><?= inr($totalCost) ?></h3>
            </div>
            <i class="fas fa-coins fa-2x opacity-50"></i>
          </div>
          <div class="mt-2 small opacity-75">
            Land: <?= inr($landCost) ?> + Dev: <?= inr(floatval($devTotal['gross'] ?? 0) + floatval($devTotal['gst'] ?? 0)) ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm <?= $grossProfit >= 0 ? 'bg-gradient-primary' : 'bg-gradient-warning' ?> text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="small opacity-75">Gross Profit</div>
              <h3 class="mb-0"><?= inr($grossProfit) ?></h3>
            </div>
            <i class="fas fa-chart-pie fa-2x opacity-50"></i>
          </div>
          <div class="mt-2 small opacity-75">
            Margin: <?= $profitMargin ?>%
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm bg-gradient-info text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="small opacity-75">ROI Projection</div>
              <h3 class="mb-0"><?= $roi['roi_pct'] ?? 0 ?>%</h3>
            </div>
            <i class="fas fa-percentage fa-2x opacity-50"></i>
          </div>
          <div class="mt-2 small opacity-75">
            Break-even: <?= $roi['break_even_plots'] ?? 0 ?> plots
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <!-- Plot Status Distribution -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-th-large me-2 text-primary"></i>Plot Distribution</h6>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <?php
            $statuses = [
              'available' => ['label' => 'Available', 'color' => 'success', 'icon' => 'fa-check-circle'],
              'booked'    => ['label' => 'Booked',    'color' => 'warning', 'icon' => 'fa-clock'],
              'sold'      => ['label' => 'Sold',      'color' => 'danger',  'icon' => 'fa-times-circle'],
              'hold'      => ['label' => 'Hold',      'color' => 'secondary','icon' => 'fa-pause-circle'],
            ];
            foreach ($statuses as $key => $info):
              $count = $plotStats[$key] ?? 0;
              $total = max($plotStats['total'] ?? 1, 1);
              $pct = round(($count / $total) * 100);
            ?>
              <div class="col-3 mb-3">
                <div class="p-3 rounded bg-<?= $info['color'] ?>-subtle">
                  <i class="fas <?= $info['icon'] ?> fa-2x text-<?= $info['color'] ?>"></i>
                  <h4 class="mb-0 mt-2"><?= $count ?></h4>
                  <small class="text-muted"><?= $info['label'] ?></small>
                  <div class="progress mt-2 style-21032">
                    <div class="progress-bar bg-<?= $info['color'] ?>" class="style-21859"></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="text-center mt-2">
            <small class="text-muted">
              Total Area: <?= number_format($plotStats['total_area'] ?? 0) ?> sqft |
              Min: <?= inr($plotStats['min_price'] ?? 0) ?> |
              Max: <?= inr($plotStats['max_price'] ?? 0) ?>
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Development Cost Breakdown -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-hard-hat me-2 text-warning"></i>Development Costs by Type</h6>
        </div>
        <div class="card-body style-82023">
          <?php if (empty($devCosts)): ?>
            <div class="text-center text-muted py-4">
              <i class="fas fa-inbox fa-2x mb-2"></i><br>No development costs recorded yet
            </div>
          <?php else: ?>
            <table class="table table-sm table-hover mb-0">
              <thead><tr><th>Type</th><th class="text-end">Amount</th><th class="text-end">GST</th><th class="text-end">Paid</th></tr></thead>
              <tbody>
              <?php
              $grandTotal = 0;
              foreach ($devCosts as $dc):
                $grandTotal += floatval($dc['gross_amount'] ?? 0) + floatval($dc['total_gst'] ?? 0);
              ?>
                <tr>
                  <td><span class="badge bg-secondary-subtle text-dark"><?= ucfirst(str_replace('_', ' ', $dc['cost_type'])) ?></span></td>
                  <td class="text-end"><?= inr($dc['gross_amount'] ?? 0) ?></td>
                  <td class="text-end text-muted"><?= inr($dc['total_gst'] ?? 0) ?></td>
                  <td class="text-end text-success"><?= inr($dc['paid'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
              <tfoot><tr class="fw-bold"><td>Total</td><td class="text-end" colspan="2"><?= inr($grandTotal) ?></td><td class="text-end text-success"><?= inr($devTotal['paid'] ?? 0) ?></td></tr></tfoot>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <!-- Block-wise Breakdown -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-th me-2 text-info"></i>Block-wise Breakdown</h6>
        </div>
        <div class="card-body">
          <?php if (empty($blockBreakdown)): ?>
            <div class="text-center text-muted py-4">
              <i class="fas fa-cube fa-2x mb-2"></i><br>No blocks data available
            </div>
          <?php else: ?>
            <table class="table table-sm table-hover mb-0">
              <thead><tr><th>Block</th><th class="text-end">Plots</th><th class="text-end">Available</th><th class="text-end">Sold</th><th class="text-end">Value</th></tr></thead>
              <tbody>
              <?php foreach ($blockBreakdown as $b): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($b['block'] ?? '—') ?></strong></td>
                  <td class="text-end"><?= $b['count'] ?? 0 ?></td>
                  <td class="text-end text-success"><?= $b['available'] ?? 0 ?></td>
                  <td class="text-end text-danger"><?= $b['sold'] ?? 0 ?></td>
                  <td class="text-end"><?= inr($b['value'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Plot Type Breakdown + Velocity -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent border-0">
          <h6 class="mb-0"><i class="fas fa-layer-group me-2 text-purple"></i>Plot Type Breakdown</h6>
        </div>
        <div class="card-body">
          <?php if (empty($typeBreakdown)): ?>
            <div class="text-center text-muted py-4">
              <i class="fas fa-shapes fa-2x mb-2"></i><br>No plot type data
            </div>
          <?php else: ?>
            <table class="table table-sm table-hover mb-0">
              <thead><tr><th>Type</th><th class="text-end">Count</th><th class="text-end">Area (sqft)</th><th class="text-end">Value</th><th class="text-end">Avg ₹/sqft</th></tr></thead>
              <tbody>
              <?php foreach ($typeBreakdown as $t): ?>
                <tr>
                  <td><span class="badge bg-primary-subtle text-dark"><?= ucfirst($t['plot_type'] ?? 'standard') ?></span></td>
                  <td class="text-end"><?= $t['count'] ?? 0 ?></td>
                  <td class="text-end"><?= number_format($t['area'] ?? 0) ?></td>
                  <td class="text-end"><?= inr($t['value'] ?? 0) ?></td>
                  <td class="text-end"><?= inr($t['avg_price'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

          <!-- Sales Velocity -->
          <hr>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted">Sales Velocity (30 days)</small>
              <h5 class="mb-0 text-info"><?= $salesVelocity['booked_30d'] ?? 0 ?> plots</h5>
            </div>
            <div>
              <small class="text-muted">Milestones</small>
              <h5 class="mb-0 text-warning">
                <?= $milestones['done'] ?? 0 ?>/<?= $milestones['total'] ?? 0 ?> completed
              </h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ROI Projection -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0">
      <h6 class="mb-0"><i class="fas fa-chart-area me-2 text-success"></i>ROI Projection</h6>
    </div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-2">
          <div class="p-3">
            <small class="text-muted">Total Cost</small>
            <h5 class="mb-0"><?= inr($roi['total_cost'] ?? 0) ?></h5>
          </div>
        </div>
        <div class="col-md-2">
          <div class="p-3">
            <small class="text-muted">Potential Revenue</small>
            <h5 class="mb-0 text-success"><?= inr($roi['potential_revenue'] ?? 0) ?></h5>
          </div>
        </div>
        <div class="col-md-2">
          <div class="p-3">
            <small class="text-muted">Realized Revenue</small>
            <h5 class="mb-0 text-primary"><?= inr($roi['realized_revenue'] ?? 0) ?></h5>
          </div>
        </div>
        <div class="col-md-2">
          <div class="p-3">
            <small class="text-muted">Gross Profit</small>
            <h5 class="mb-0 <?= ($roi['profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>"><?= inr($roi['profit'] ?? 0) ?></h5>
          </div>
        </div>
        <div class="col-md-2">
          <div class="p-3">
            <small class="text-muted">ROI %</small>
            <h5 class="mb-0 text-warning"><?= $roi['roi_pct'] ?? 0 ?>%</h5>
          </div>
        </div>
        <div class="col-md-2">
          <div class="p-3">
            <small class="text-muted">Realization %</small>
            <div class="progress mt-1 style-40280">
              <div class="progress-bar bg-info style-13536"><?= $roi['realization_pct'] ?? 0 ?>%</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .bg-gradient-success { background: linear-gradient(135deg, #28a745, #20c997) !important; }
  .bg-gradient-danger  { background: linear-gradient(135deg, #dc3545, #e83e8c) !important; }
  .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #6610f2) !important; }
  .bg-gradient-warning { background: linear-gradient(135deg, #ffc107, #fd7e14) !important; }
  .bg-gradient-info    { background: linear-gradient(135deg, #17a2b8, #6f42c1) !important; }
  .text-purple         { color: #6f42c1 !important; }
</style>
