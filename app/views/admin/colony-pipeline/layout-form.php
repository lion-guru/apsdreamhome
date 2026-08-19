<?php
$colony = $colony ?? [];
$existingPlots = (int)($existing_plots ?? 0);
$currentLayout = $current_layout ?? null;
$totalAreaSqft = (float)($total_area_sqft ?? 0);
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1"><?= __('cp_layout_config') ?></h1>
      <span class="text-muted">
        <?= htmlspecialchars($colony['name'] ?? '') ?>
        &middot; <?= number_format((float)($colony['total_area_acres'] ?? 0), 2) ?> <?= __('cp_acres') ?>
        &middot; <?= number_format($existingPlots) ?> <?= __('cp_existing_plots') ?>
      </span>
    </div>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i><?= __('cp_back_to_colony') ?>
    </a>
  </div>

  <?php if ($existingPlots > 0): ?>
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <div>
        <strong><?= __('cp_existing_layout_found') ?></strong> <?= __('cp_colony_has_plots', ['count' => number_format($existingPlots)]) ?>
        <?= __('cp_delete_before_regenerating') ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($currentLayout)): ?>
    <div class="card aps-cp-card mb-4">
      <div class="card-header aps-cp-card-header"><strong><i class="fas fa-info-circle me-2"></i><?= __('cp_current_layout') ?></strong></div>
      <div class="card-body aps-cp-card-body">
        <div class="row text-center">
          <div class="col-md-2">
            <div class="fw-bold"><?= htmlspecialchars($currentLayout['block_name'] ?? '-') ?></div>
            <div class="text-muted small"><?= __('cp_block') ?></div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((float)($currentLayout['road_width'] ?? 0)) ?> ft</div>
            <div class="text-muted small"><?= __('cp_road_width') ?></div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((float)($currentLayout['park_area_pct'] ?? 0)) ?>%</div>
            <div class="text-muted small"><?= __('cp_park_area') ?></div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((float)($currentLayout['amenity_area_pct'] ?? 0)) ?>%</div>
            <div class="text-muted small"><?= __('cp_amenity_area') ?></div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((int)($currentLayout['total_plots'] ?? 0)) ?></div>
            <div class="text-muted small"><?= __('cp_total_plots') ?></div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold">₹<?= number_format((float)($totalAreaSqft), 0) ?></div>
            <div class="text-muted small"><?= __('cp_total_area_sqft') ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-md-8">
      <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-drafting-compass me-2"></i><?= __('cp_generate_layout') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout/generate" id="layoutForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label"><?= __('cp_block_name') ?></label>
                <input type="text" name="block_name" class="form-control" value="A" required maxlength="10">
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('cp_road_width_ft') ?></label>
                <input type="number" name="road_width" class="form-control" value="30" min="20" max="50" required>
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('cp_park_area_pct') ?></label>
                <input type="number" name="park_area_pct" class="form-control" value="7" min="3" max="15" step="0.5" required>
              </div>
              <div class="col-md-6">
                <label class="form-label"><?= __('cp_amenity_area_pct') ?></label>
                <input type="number" name="amenity_area_pct" class="form-control" value="3" min="1" max="10" step="0.5" required>
              </div>
            </div>

            <div class="mt-4">
              <label class="form-label fw-semibold"><?= __('cp_plot_sizes') ?></label>
              <div class="row g-2">
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="1200" id="sz1200" checked>
                    <label class="form-check-label" for="sz1200">1200 <?= __('cp_sqft') ?> <small class="text-muted">(30x40)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="1500" id="sz1500" checked>
                    <label class="form-check-label" for="sz1500">1500 <?= __('cp_sqft') ?> <small class="text-muted">(30x50)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="1800" id="sz1800">
                    <label class="form-check-label" for="sz1800">1800 <?= __('cp_sqft') ?> <small class="text-muted">(30x60)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="2000" id="sz2000">
                    <label class="form-check-label" for="sz2000">2000 <?= __('cp_sqft') ?> <small class="text-muted">(40x50)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="2400" id="sz2400">
                    <label class="form-check-label" for="sz2400">2400 <?= __('cp_sqft') ?> <small class="text-muted">(40x60)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="3000" id="sz3000">
                    <label class="form-check-label" for="sz3000">3000 <?= __('cp_sqft') ?> <small class="text-muted">(50x60)</small></label>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex gap-2">
              <button type="button" class="btn btn-outline-info" id="previewBtn">
                <i class="fas fa-eye me-1"></i><?= __('cp_preview_plots') ?>
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-cogs me-1"></i><?= __('cp_generate_plots') ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong><?= __('cp_summary') ?></strong></div>
        <div class="card-body aps-cp-card-body">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted"><?= __('cp_colony_area') ?></span>
            <strong><?= number_format((float)($colony['total_area_acres'] ?? 0), 2) ?> <?= __('cp_acres') ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted"><?= __('cp_total_area_sqft') ?></span>
            <strong><?= number_format($totalAreaSqft, 0) ?></strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted"><?= __('cp_park_area') ?> (7%)</span>
            <strong><?= number_format($totalAreaSqft * 0.07, 0) ?> <?= __('cp_sqft') ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted"><?= __('cp_amenity_area') ?> (3%)</span>
            <strong><?= number_format($totalAreaSqft * 0.03, 0) ?> <?= __('cp_sqft') ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted"><?= __('cp_road_area') ?> (15%)</span>
            <strong><?= number_format($totalAreaSqft * 0.15, 0) ?> <?= __('cp_sqft') ?></strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <span class="text-muted"><?= __('cp_buildable_area') ?></span>
            <strong class="text-success"><?= number_format($totalAreaSqft * 0.75, 0) ?> <?= __('cp_sqft') ?></strong>
          </div>
          <div class="d-flex justify-content-between mt-2">
            <span class="text-muted"><?= __('cp_existing_plots') ?></span>
            <strong><?= number_format($existingPlots) ?></strong>
          </div>
        </div>
      </div>

      <?php if ($existingPlots > 0): ?>
      <div class="card aps-cp-card border-danger">
        <div class="card-body aps-cp-card-body text-center">
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout/delete"
                onsubmit="return confirm('<?= __('cp_confirm_delete_all', ['count' => number_format($existingPlots)]) ?>');">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <i class="fas fa-trash-alt fa-2x text-danger mb-2"></i>
            <div class="fw-semibold text-danger mb-2"><?= __('cp_delete_all_plots') ?></div>
            <small class="text-muted d-block mb-3"><?= __('cp_remove_existing', ['count' => number_format($existingPlots)]) ?></small>
            <button type="submit" class="btn btn-danger btn-sm w-100">
              <i class="fas fa-trash me-1"></i><?= __('cp_delete_all_plots') ?>
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div id="preview-results" class="mt-4" class="style-54390">
    <div class="card aps-cp-card">
      <div class="card-header aps-cp-card-header"><strong><i class="fas fa-map me-2"></i><?= __('cp_layout_preview') ?></strong></div>
      <div class="card-body aps-cp-card-body" id="preview-content">
        <div class="text-center text-muted py-3">
          <i class="fas fa-spinner fa-spin me-1"></i><?= __('cp_generating_preview') ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var previewBtn = document.getElementById('previewBtn');
  if (previewBtn) {
    previewBtn.addEventListener('click', function() {
      var form = document.getElementById('layoutForm');
      var formData = new FormData(form);
      var previewSection = document.getElementById('preview-results');
      var previewContent = document.getElementById('preview-content');
      previewSection.style.display = 'block';
      previewContent.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-1"></i><?= __('cp_generating_preview') ?></div>';
      fetch('<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout/preview', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '<?= $csrf_token ?? '' ?>' },
        body: new URLSearchParams(formData)
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
      .catch(err => console.error('Request failed:', err));
        if (data.success && data.plots && data.plots.length > 0) {
          var parkPct = document.querySelector('input[name="park_area_pct"]').value || '7';
          var roadW = document.querySelector('input[name="road_width"]').value || '30';
          
          var html = '<div class="alert alert-success d-flex align-items-center justify-content-between mb-4">' +
              '<span><i class="fas fa-check-circle me-2"></i><strong>' + data.plots.length + ' <?= __('cp_plots_will_be_generated') ?></strong></span>' +
              '</div>';
          
          html += '<h6 class="mb-3 fw-bold text-secondary"><i class="fas fa-map-marked-alt me-1"></i><?= __('cp_spatial_layout_map') ?></h6>';
          html += '<div class="style-9547">';
          html += '<div class="style-32634"><i class="fas fa-tree me-2"></i><?= __('cp_dedicated_park') ?> (' + parkPct + '%)</div>';
          html += '<div class="style-89666"><i class="fas fa-road me-2"></i><?= __('cp_main_road') ?> (' + roadW + ' ft)</div>';

          data.plots.forEach(function(p, i) {
            var isCorner = p.plot_type && p.plot_type.toLowerCase().indexOf('corner') !== -1;
            var isPark = p.plot_type && p.plot_type.toLowerCase().indexOf('park') !== -1;
            var bg = '#dbeafe'; 
            var border = '#bfdbfe';
            var color = '#1e3a8a';
            var icon = '<i class="fas fa-home me-1"></i>';
            if (isCorner) { bg = '#fef3c7'; border = '#fcd34d'; color = '#78350f'; icon = '<i class="fas fa-angle-double-up me-1"></i>'; }
            else if (isPark) { bg = '#dcfce7'; border = '#86efac'; color = '#166534'; icon = '<i class="fas fa-tree me-1"></i>'; }
            html += '<div class="style-38373" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">' +
                '<div class="style-20987">' + icon + p.plot_no + '</div>' +
                '<div class="text-muted" class="style-40535">' + p.area_sqft + ' <?= __('cp_sqft') ?></div>' +
                '<div class="style-71519">' + p.width_ft + 'x' + p.length_ft + ' ft</div>' +
                '</div>';
          });
          html += '</div>';

          html += '<h6 class="mb-3 fw-bold text-secondary"><i class="fas fa-list-ul me-1"></i><?= __('cp_plots_inventory') ?></h6>';
          html += '<div class="table-responsive"><table class="table table-sm table-hover align-middle"><thead><tr><th>#</th><th><?= __('cp_plot_no') ?></th><th><?= __('cp_block') ?></th><th><?= __('cp_area') ?> (<?= __('cp_sqft') ?>)</th><th><?= __('cp_type') ?></th><th><?= __('cp_front_ft') ?></th><th><?= __('cp_depth_ft') ?></th></tr></thead><tbody>';
          data.plots.forEach(function(p, i) {
            html += '<tr><td>' + (i+1) + '</td><td><strong>' + p.plot_no + '</strong></td><td>' + p.block_name + '</td><td>' + p.area_sqft + '</td><td>' + p.plot_type + '</td><td>' + p.width_ft + '</td><td>' + p.length_ft + '</td></tr>';
          });
          html += '</tbody></table></div>';
          
          previewContent.innerHTML = html;
        } else {
          previewContent.innerHTML = '<div class="alert alert-danger">' + (data.error || '<?= __('cp_preview_failed') ?>') + '</div>';
        }
      })
      .catch(function(e) {
        previewContent.innerHTML = '<div class="alert alert-danger">' + e.message + '</div>';
      });
    });
  }
});
</script>
