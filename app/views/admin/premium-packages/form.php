<?php $pkg = $package ?? []; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-<?= empty($pkg) ? 'plus' : 'edit' ?> me-2 text-warning"></i><?= empty($pkg) ? 'New' : 'Edit' ?> Package</h2>
    <a href="<?= BASE_URL ?>/admin/premium-packages" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="aps-cp-card">
        <div class="aps-cp-card-body">
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($pkg['name'] ?? '') ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Slug *</label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($pkg['slug'] ?? '') ?>" required placeholder="e.g. featured, urgent, premium_plus">
              </div>
            </div>
            <div class="mb-3 mt-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($pkg['description'] ?? '') ?></textarea>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label class="form-label">Price (₹)</label>
                <input type="number" name="price" class="form-control" value="<?= $pkg['price'] ?? 0 ?>" min="0" step="1">
              </div>
              <div class="col-md-3">
                <label class="form-label">Duration (days)</label>
                <input type="number" name="duration_days" class="form-control" value="<?= $pkg['duration_days'] ?? 30 ?>" min="1">
              </div>
              <div class="col-md-3">
                <label class="form-label">Badge Label</label>
                <input type="text" name="badge_label" class="form-control" value="<?= htmlspecialchars($pkg['badge_label'] ?? 'Featured') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Badge Color</label>
                <input type="color" name="badge_color" class="form-control form-control-color" value="<?= htmlspecialchars($pkg['badge_color'] ?? '#ff6b35') ?>">
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label class="form-label">Priority Order</label>
                <input type="number" name="priority_order" class="form-control" value="<?= $pkg['priority_order'] ?? 0 ?>" min="0">
              </div>
              <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !isset($pkg['is_active']) || $pkg['is_active'] ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_active">Active</label>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Features (max 10)</label>
              <p class="small text-muted">Leave blank to skip</p>
              <?php $features = is_array($pkg['features'] ?? null) ? $pkg['features'] : []; ?>
              <?php for ($i = 1; $i <= 10; $i++): ?>
                <div class="input-group mb-1">
                  <span class="input-group-text"><?= $i ?></span>
                  <input type="text" name="feature_<?= $i ?>" class="form-control" value="<?= htmlspecialchars($features[$i-1] ?? '') ?>" placeholder="Feature description">
                </div>
              <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= empty($pkg) ? 'Create Package' : 'Update Package' ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
