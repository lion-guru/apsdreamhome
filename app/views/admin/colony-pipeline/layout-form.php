<?php
$colony = $colony ?? [];
$existingPlots = (int)($existing_plots ?? 0);
$currentLayout = $current_layout ?? null;
$totalAreaSqft = (float)($total_area_sqft ?? 0);
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Layout Configuration</h1>
      <span class="text-muted">
        <?= htmlspecialchars($colony['name'] ?? '') ?>
        &middot; <?= number_format((float)($colony['total_area_acres'] ?? 0), 2) ?> Acres
        &middot; <?= number_format($existingPlots) ?> Existing Plots
      </span>
    </div>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Colony
    </a>
  </div>

  <?php if ($existingPlots > 0): ?>
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <div>
        <strong>Existing layout found.</strong> This colony already has <?= number_format($existingPlots) ?> plots configured.
        Delete existing plots before regenerating. You can delete all plots from the form below.
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($currentLayout)): ?>
    <div class="card aps-cp-card mb-4">
      <div class="card-header aps-cp-card-header"><strong><i class="fas fa-info-circle me-2"></i>Current Layout</strong></div>
      <div class="card-body aps-cp-card-body">
        <div class="row text-center">
          <div class="col-md-2">
            <div class="fw-bold"><?= htmlspecialchars($currentLayout['block_name'] ?? '-') ?></div>
            <div class="text-muted small">Block</div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((float)($currentLayout['road_width'] ?? 0)) ?> ft</div>
            <div class="text-muted small">Road Width</div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((float)($currentLayout['park_area_pct'] ?? 0)) ?>%</div>
            <div class="text-muted small">Park Area</div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((float)($currentLayout['amenity_area_pct'] ?? 0)) ?>%</div>
            <div class="text-muted small">Amenity Area</div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold"><?= number_format((int)($currentLayout['total_plots'] ?? 0)) ?></div>
            <div class="text-muted small">Total Plots</div>
          </div>
          <div class="col-md-2">
            <div class="fw-bold">₹<?= number_format((float)($totalAreaSqft), 0) ?></div>
            <div class="text-muted small">Total Area (sqft)</div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-md-8">
      <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header"><strong><i class="fas fa-drafting-compass me-2"></i>Generate Layout</strong></div>
        <div class="card-body aps-cp-card-body">
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout/generate" id="layoutForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Block Name</label>
                <input type="text" name="block_name" class="form-control" value="A" required maxlength="10">
              </div>
              <div class="col-md-6">
                <label class="form-label">Road Width (ft)</label>
                <input type="number" name="road_width" class="form-control" value="30" min="20" max="50" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Park Area %</label>
                <input type="number" name="park_area_pct" class="form-control" value="7" min="3" max="15" step="0.5" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Amenity Area %</label>
                <input type="number" name="amenity_area_pct" class="form-control" value="3" min="1" max="10" step="0.5" required>
              </div>
            </div>

            <div class="mt-4">
              <label class="form-label fw-semibold">Plot Sizes</label>
              <div class="row g-2">
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="1200" id="sz1200" checked>
                    <label class="form-check-label" for="sz1200">1200 sqft <small class="text-muted">(30×40)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="1500" id="sz1500" checked>
                    <label class="form-check-label" for="sz1500">1500 sqft <small class="text-muted">(30×50)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="1800" id="sz1800">
                    <label class="form-check-label" for="sz1800">1800 sqft <small class="text-muted">(30×60)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="2000" id="sz2000">
                    <label class="form-check-label" for="sz2000">2000 sqft <small class="text-muted">(40×50)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="2400" id="sz2400">
                    <label class="form-check-label" for="sz2400">2400 sqft <small class="text-muted">(40×60)</small></label>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="plot_sizes[]" value="3000" id="sz3000">
                    <label class="form-check-label" for="sz3000">3000 sqft <small class="text-muted">(50×60)</small></label>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex gap-2">
              <button type="button" class="btn btn-outline-info" id="previewBtn">
                <i class="fas fa-eye me-1"></i>Preview Plots
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-cogs me-1"></i>Generate Plots
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card aps-cp-card mb-4">
        <div class="card-header aps-cp-card-header"><strong>Summary</strong></div>
        <div class="card-body aps-cp-card-body">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Total Colony Area</span>
            <strong><?= number_format((float)($colony['total_area_acres'] ?? 0), 2) ?> Acres</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Total Area (sqft)</span>
            <strong><?= number_format($totalAreaSqft, 0) ?></strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Park Area (7%)</span>
            <strong><?= number_format($totalAreaSqft * 0.07, 0) ?> sqft</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Amenity Area (3%)</span>
            <strong><?= number_format($totalAreaSqft * 0.03, 0) ?> sqft</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Road Area (15%)</span>
            <strong><?= number_format($totalAreaSqft * 0.15, 0) ?> sqft</strong>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <span class="text-muted">Buildable Area</span>
            <strong class="text-success"><?= number_format($totalAreaSqft * 0.75, 0) ?> sqft</strong>
          </div>
          <div class="d-flex justify-content-between mt-2">
            <span class="text-muted">Existing Plots</span>
            <strong><?= number_format($existingPlots) ?></strong>
          </div>
        </div>
      </div>

      <?php if ($existingPlots > 0): ?>
      <div class="card aps-cp-card border-danger">
        <div class="card-body aps-cp-card-body text-center">
          <form method="post" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout/delete"
                onsubmit="return confirm('Are you sure you want to delete ALL <?= number_format($existingPlots) ?> plots? This cannot be undone.');">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <i class="fas fa-trash-alt fa-2x text-danger mb-2"></i>
            <div class="fw-semibold text-danger mb-2">Delete All Plots</div>
            <small class="text-muted d-block mb-3">Remove <?= number_format($existingPlots) ?> existing plots before regenerating layout.</small>
            <button type="submit" class="btn btn-danger btn-sm w-100">
              <i class="fas fa-trash me-1"></i>Delete All Plots
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div id="preview-results" class="mt-4" style="display: none;">
    <div class="card aps-cp-card">
      <div class="card-header aps-cp-card-header"><strong><i class="fas fa-map me-2"></i>Layout Preview</strong></div>
      <div class="card-body aps-cp-card-body" id="preview-content">
        <div class="text-center text-muted py-3">
          <i class="fas fa-spinner fa-spin me-1"></i>Generating preview...
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
      previewContent.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-1"></i>Generating preview...</div>';
      fetch('<?= BASE_URL ?>/admin/colony-pipeline/<?= (int)($colony['id'] ?? 0) ?>/layout/preview', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '<?= $csrf_token ?? '' ?>' },
        body: new URLSearchParams(formData)
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success && data.plots && data.plots.length > 0) {
          var parkPct = document.querySelector('input[name="park_area_pct"]').value || '7';
          var roadW = document.querySelector('input[name="road_width"]').value || '30';
          
          var html = '<div class="alert alert-success d-flex align-items-center justify-content-between mb-4">' +
              '<span><i class="fas fa-check-circle me-2"></i><strong>' + data.plots.length + ' plots</strong> will be generated.</span>' +
              '</div>';
          
          // Spatial Map Grid
          html += '<h6 class="mb-3 fw-bold text-secondary"><i class="fas fa-map-marked-alt me-1"></i>Spatial Layout Map</h6>';
          html += '<div style="display:flex;flex-wrap:wrap;gap:8px;padding:16px;background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;margin-bottom:24px;">';
          
          // Parks Indicator
          html += '<div style="flex:1 1 100%;background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px;border-radius:10px;text-align:center;font-weight:700;font-size:0.85rem;margin-bottom:4px;"><i class="fas fa-tree me-2"></i>Dedicated Park Area (' + parkPct + '%)</div>';
          
          // Road Indicator
          html += '<div style="flex:1 1 100%;background:#e2e8f0;border:1px solid #cbd5e1;color:#475569;padding:8px;border-radius:10px;text-align:center;font-weight:700;font-size:0.8rem;margin-bottom:8px;"><i class="fas fa-road me-2"></i>Main Internal Road (' + roadW + ' ft wide)</div>';

          // Plots List Grid items
          data.plots.forEach(function(p, i) {
            var isCorner = p.plot_type && p.plot_type.toLowerCase().indexOf('corner') !== -1;
            var isPark = p.plot_type && p.plot_type.toLowerCase().indexOf('park') !== -1;
            
            var bg = '#dbeafe'; 
            var border = '#bfdbfe';
            var color = '#1e3a8a';
            var icon = '<i class="fas fa-home me-1"></i>';
            
            if (isCorner) {
              bg = '#fef3c7'; border = '#fcd34d'; color = '#78350f';
              icon = '<i class="fas fa-angle-double-up me-1"></i>';
            } else if (isPark) {
              bg = '#dcfce7'; border = '#86efac'; color = '#166534';
              icon = '<i class="fas fa-tree me-1"></i>';
            }
            
            html += '<div style="flex:0 0 calc(12.5% - 8px);background:' + bg + ';border:1px solid ' + border + ';color:' + color + ';padding:10px;border-radius:10px;text-align:center;font-size:0.75rem;min-height:75px;display:flex;flex-direction:column;justify-content:center;transition:all 0.15s ease;" onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">' +
                '<div style="font-weight:700;margin-bottom:2px;">' + icon + p.plot_no + '</div>' +
                '<div class="text-muted" style="font-size:0.62rem;font-weight:500;">' + p.area_sqft + ' sqft</div>' +
                '<div style="font-size:0.55rem;opacity:0.8;margin-top:2px;">' + p.front + '×' + p.depth + ' ft</div>' +
                '</div>';
          });
          html += '</div>';

          // Grid Table details
          html += '<h6 class="mb-3 fw-bold text-secondary"><i class="fas fa-list-ul me-1"></i>Plots Inventory Details</h6>';
          html += '<div class="table-responsive"><table class="table table-sm table-hover align-middle"><thead><tr><th>#</th><th>Plot No</th><th>Block</th><th>Area (sqft)</th><th>Type</th><th>Front (ft)</th><th>Depth (ft)</th></tr></thead><tbody>';
          data.plots.forEach(function(p, i) {
            html += '<tr><td>' + (i+1) + '</td><td><strong>' + p.plot_no + '</strong></td><td>' + p.block_name + '</td><td>' + p.area_sqft + '</td><td>' + p.plot_type + '</td><td>' + p.front + '</td><td>' + p.depth + '</td></tr>';
          });
          html += '</tbody></table></div>';
          
          previewContent.innerHTML = html;
        } else {
          previewContent.innerHTML = '<div class="alert alert-danger">Preview generation failed: ' + (data.error || 'Unknown error') + '</div>';
        }
      })
      .catch(function(e) {
        previewContent.innerHTML = '<div class="alert alert-danger">Error: ' + e.message + '</div>';
      });
    });
  }
});
</script>
