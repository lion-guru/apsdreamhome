<?php
$colony = $colony ?? null;
?>
<div class="container-fluid py-4">
  <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
  <h2 class="mb-4"><i class="fas fa-drafting-compass me-2 text-info"></i>Phase 2: Create Master Plan</h2>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="/admin/legal-colony-pipeline/store-master-plan">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="colony_id" value="<?= $colony['id'] ?? 0 ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-white">Layout Name *</label>
            <input type="text" name="layout_name" class="form-control bg-dark text-white border-secondary" required value="Master Plan v1">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Layout Type</label>
            <select name="layout_type" class="form-select bg-dark text-white border-secondary">
              <option value="residential">Residential</option>
              <option value="commercial">Commercial</option>
              <option value="mixed">Mixed Use</option>
              <option value="industrial">Industrial</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Total Area (Acres)</label>
            <input type="number" name="total_area_acres" class="form-control bg-dark text-white border-secondary" step="0.01" value="<?= $colony['total_area_acres'] ?? 0 ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Min Road Width (ft) *</label>
            <input type="number" name="min_road_width_ft" class="form-control bg-dark text-white border-secondary" value="30" min="20">
            <small class="text-warning">Legal minimum: 20 ft</small>
          </div>
          <div class="col-md-4">
            <label class="form-label text-white">Road Area % *</label>
            <input type="number" name="road_area_pct" class="form-control bg-dark text-white border-secondary" value="15" min="15" step="0.5">
            <small class="text-warning">Min 15% for RERA compliance</small>
          </div>
          <div class="col-md-4">
            <label class="form-label text-white">Park Area % *</label>
            <input type="number" name="park_area_pct" class="form-control bg-dark text-white border-secondary" value="10" min="0" step="0.5">
          </div>
          <div class="col-md-4">
            <label class="form-label text-white">Amenity Area % *</label>
            <input type="number" name="amenity_area_pct" class="form-control bg-dark text-white border-secondary" value="5" min="0" step="0.5">
            <small class="text-info">Total open space must be ≥ 30%</small>
          </div>
          <div class="col-md-12">
            <label class="form-label text-white">Notes</label>
            <textarea name="notes" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Additional planning notes..."></textarea>
          </div>
        </div>

        <hr class="border-secondary">

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-info"><i class="fas fa-save me-1"></i> Create Master Plan</button>
          <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
