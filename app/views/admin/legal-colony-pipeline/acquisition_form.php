<?php
$colony = $colony ?? null;
?>
<div class="container-fluid py-4">
  <a href="/admin/legal-colony-pipeline/detail/<?= $colony['id'] ?? 0 ?>" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Pipeline</a>
  <h2 class="mb-4"><i class="fas fa-file-contract me-2 text-warning"></i>Phase 1: Start Land Acquisition</h2>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="/admin/legal-colony-pipeline/store-acquisition">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-white">Land Owner Name *</label>
            <input type="text" name="land_owner_name" class="form-control bg-dark text-white border-secondary" required placeholder="e.g. Ramesh Kumar">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Colony Name</label>
            <input type="text" name="colony_name" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Suryoday Phase 2">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Location / Address</label>
            <input type="text" name="location" class="form-control bg-dark text-white border-secondary" placeholder="e.g. Gorakhpur-Lucknow Highway">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Total Area (Acres) *</label>
            <input type="number" name="total_area_acres" class="form-control bg-dark text-white border-secondary" step="0.01" min="0.1" required placeholder="e.g. 5.5">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Estimated Land Cost (₹) *</label>
            <input type="number" name="estimated_cost" class="form-control bg-dark text-white border-secondary" min="1" required placeholder="e.g. 5500000">
          </div>
          <div class="col-md-6">
            <label class="form-label text-white">Advance Paid (₹)</label>
            <input type="number" name="advance_paid" class="form-control bg-dark text-white border-secondary" min="0" value="0" placeholder="e.g. 1000000">
          </div>
        </div>

        <hr class="border-secondary">

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Start Acquisition</button>
          <a href="/admin/legal-colony-pipeline" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
