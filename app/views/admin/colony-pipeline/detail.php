<?php
$colony = $colony ?? [];
$plotStats = $plot_stats ?? [];
$devCost = $dev_cost ?? [];
$layout = $layout ?? null;
$blocks = $blocks ?? [];
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><?= htmlspecialchars($colony['name'] ?? __('cp_colony')) ?></h1>
      <span class="text-muted">
        <?= htmlspecialchars($colony['colony_code'] ?? '') ?>
        &middot; <?= htmlspecialchars($colony['district_name'] ?? '') ?>
        <?= !empty($colony['state_name']) ? ', ' . htmlspecialchars($colony['state_name'] ?? '') : '' ?>
        &middot; <?= number_format((float)($colony['total_area_acres'] ?? 0), 2) ?> <?= __('cp_acres') ?>
      </span>
    </div>
    <div>
      <span class="badge bg-<?= !empty($colony['has_layout']) ? 'success' : 'warning text-dark' ?> fs-6">
        <?= !empty($colony['has_layout']) ? __('cp_layout_configured') : __('cp_no_layout') ?>
      </span>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-primary"><?= number_format((int)($plotStats['total'] ?? 0)) ?></div>
          <div class="text-muted small"><?= __('cp_total_plots') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-success"><?= number_format((int)($plotStats['available'] ?? 0)) ?></div>
          <div class="text-muted small"><?= __('cp_available') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-warning"><?= number_format((int)($plotStats['booked'] ?? 0)) ?></div>
          <div class="text-muted small"><?= __('cp_booked') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-danger"><?= number_format((int)($plotStats['sold'] ?? 0)) ?></div>
          <div class="text-muted small"><?= __('cp_sold') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-secondary"><?= number_format((int)($plotStats['hold'] ?? 0)) ?></div>
          <div class="text-muted small"><?= __('cp_hold') ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card">
        <div class="card-body text-center">
          <div class="fs-3 text-info">₹<?= number_format((float)($plotStats['total_value'] ?? 0) / 100000, 1) ?>L</div>
          <div class="text-muted small"><?= __('cp_total_value') ?></div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($devCost['total_cost']) || !empty($devCost['total_gst']) || !empty($devCost['total_paid'])): ?>
  <div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-tools me-2"></i><?= __('cp_dev_cost_summary') ?></strong></div>
    <div class="card-body aps-cp-card-body">
      <div class="row text-center">
        <div class="col-md-3">
          <div class="fw-bold fs-5">₹<?= number_format((float)($devCost['total_cost'] ?? 0), 0) ?></div>
          <div class="text-muted small"><?= __('cp_total_cost') ?></div>
        </div>
        <div class="col-md-3">
          <div class="fw-bold fs-5">₹<?= number_format((float)($devCost['total_gst'] ?? 0), 0) ?></div>
          <div class="text-muted small"><?= __('cp_total_gst') ?></div>
        </div>
        <div class="col-md-3">
          <div class="fw-bold fs-5 text-success">₹<?= number_format((float)($devCost['total_paid'] ?? 0), 0) ?></div>
          <div class="text-muted small"><?= __('cp_paid') ?></div>
        </div>
        <div class="col-md-3">
          <div class="fw-bold fs-5 text-danger">₹<?= number_format((float)($devCost['total_balance'] ?? 0), 0) ?></div>
          <div class="text-muted small"><?= __('cp_balance') ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($blocks)): ?>
  <div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header"><strong><i class="fas fa-th-large me-2"></i><?= __('cp_blocks_breakdown') ?></strong></div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th><?= __('cp_block') ?></th><th><?= __('cp_plot_count') ?></th><th><?= __('cp_available') ?></th><th><?= __('cp_occupancy') ?></th></tr></thead>
        <tbody>
          <?php foreach ($blocks as $b): ?>
            <?php
              $bc = (int)($b['plot_count'] ?? 0);
              $ba = (int)($b['available'] ?? 0);
              $occ = $bc > 0 ? round((($bc - $ba) / $bc) * 100) : 0;
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($b['block'] ?? '') ?></strong></td>
              <td><?= $bc ?></td>
              <td><?= $ba ?></td>
              <td>
                <div class="progress style-51309">
                  <div class="progress-bar bg-<?= $occ >= 80 ? 'success' : ($occ >= 50 ? 'warning' : 'primary') ?> style-5688"><?= $occ ?>%</div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <h5 class="mb-3"><?= __('cp_quick_actions') ?></h5>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout" class="card aps-cp-card text-decoration-none">
        <div class="card-body aps-cp-card-body text-center">
          <i class="fas fa-drafting-compass fa-2x text-primary mb-2"></i>
          <div class="fw-semibold"><?= __('cp_layout_config') ?></div>
          <small class="text-muted"><?= __('cp_define_layout') ?></small>
        </div>
      </a>
    </div>
    <div class="col-md-3">
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/costs" class="card aps-cp-card text-decoration-none">
        <div class="card-body aps-cp-card-body text-center">
          <i class="fas fa-file-invoice-dollar fa-2x text-success mb-2"></i>
          <div class="fw-semibold"><?= __('cp_add_dev_cost') ?></div>
          <small class="text-muted"><?= __('cp_track_expenses') ?></small>
        </div>
      </a>
    </div>
    <div class="col-md-3">
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/plots" class="card aps-cp-card text-decoration-none">
        <div class="card-body aps-cp-card-body text-center">
          <i class="fas fa-map fa-2x text-warning mb-2"></i>
          <div class="fw-semibold"><?= __('cp_view_plots') ?></div>
          <small class="text-muted"><?= __('cp_manage_inventory') ?></small>
        </div>
      </a>
    </div>
    <div class="col-md-3">
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/pricing" class="card aps-cp-card text-decoration-none">
        <div class="card-body aps-cp-card-body text-center">
          <i class="fas fa-tags fa-2x text-info mb-2"></i>
          <div class="fw-semibold"><?= __('cp_pricing') ?></div>
          <small class="text-muted"><?= __('cp_configure_pricing') ?></small>
        </div>
      </a>
    </div>
    <div class="col-md-3">
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/map" class="card aps-cp-card text-decoration-none">
        <div class="card-body aps-cp-card-body text-center">
          <i class="fas fa-map-marked-alt fa-2x style-3064"></i>
          <div class="fw-semibold">Interactive Map</div>
          <small class="text-muted">Leaflet plot map with filters</small>
        </div>
      </a>
    </div>
  </div>

  <div class="text-muted small">
    <?= __('cp_avg_area') ?>: <?= number_format((float)($plotStats['avg_area'] ?? 0), 0) ?> <?= __('cp_sqft') ?>
    &middot; <?= __('cp_starting_price') ?>: ₹<?= number_format((float)($colony['starting_price'] ?? 0), 0) ?>
    &middot; <?= __('cp_location') ?>: <?= htmlspecialchars($colony['location'] ?? '') ?>
  </div>
</div>
