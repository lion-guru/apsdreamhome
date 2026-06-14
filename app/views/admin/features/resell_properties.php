<?php
$page_title = $page_title ?? 'Resell Properties';
$page_heading = $page_heading ?? 'Resell Properties Marketplace';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-home me-2"></i>Resell Properties</h1>

  <div class="row mb-3">
    <div class="col-md-12 text-end">
      <a href="<?= BASE_URL ?>/properties/submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> List New Property</a>
    </div>
  </div>

  <div class="row">
    <?php if (empty($properties)): ?>
      <div class="col-12"><div class="alert alert-info text-center">No resell properties listed yet</div></div>
    <?php else: foreach ($properties as $p): ?>
      <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
          <div class="card-body aps-cp-card-body">
            <h5 class="card-title"><?= htmlspecialchars($p['title'] ?? '') ?></h5>
            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars(($p['colony_name'] ?? '') . ', ' . ($p['district_name'] ?? '')) ?></p>
            <div class="d-flex justify-content-between mb-2">
              <span class="badge bg-info"><?= htmlspecialchars($p['property_type'] ?? '') ?></span>
              <span class="badge bg-<?= ($p['listing_type'] ?? '') === 'rent' ? 'warning' : 'success' ?>"><?= htmlspecialchars($p['listing_type'] ?? '') ?></span>
            </div>
            <h4 class="text-primary">₹<?= number_format((float)($p['expected_price'] ?? 0), 0) ?></h4>
            <small class="text-muted"><?= htmlspecialchars($p['area_sqft'] ?? 0) ?> sqft</small>
            <p class="mt-2 small"><?= htmlspecialchars(substr($p['address'] ?? '', 0, 80)) ?></p>
            <small class="text-muted">Listed: <?= htmlspecialchars($p['created_at'] ?? '') ?></small>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <?php if (!empty($commissionStructure)): ?>
  <div class="card shadow-sm mt-4">
    <div class="card-header bg-white"><strong>Commission Structure</strong></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead class="table-light"><tr><th>Min Price</th><th>Max Price</th><th>Rate</th><th>Description</th></tr></thead>
          <tbody>
            <?php foreach ($commissionStructure as $cs): ?>
              <tr>
                <td>₹<?= number_format((float)($cs['min_price'] ?? 0), 0) ?></td>
                <td>₹<?= number_format((float)($cs['max_price'] ?? 0), 0) ?></td>
                <td><?= htmlspecialchars($cs['commission_rate'] ?? '') ?>%</td>
                <td><small><?= htmlspecialchars($cs['description'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/admin/layouts/admin.php';
