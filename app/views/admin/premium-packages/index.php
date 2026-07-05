<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-crown me-2 text-warning"></i>Premium Packages</h2>
    <a href="<?= BASE_URL ?>/admin/premium-packages/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Package</a>
  </div>
  <div class="aps-cp-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Name</th><th>Badge</th><th>Price</th><th>Duration</th><th>Priority</th><th>Active</th><th>Purchases</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (empty($packages)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No packages defined yet</td></tr>
          <?php else: ?>
            <?php foreach ($packages as $pkg): ?>
              <?php $features = json_decode($pkg['features'] ?? '[]', true); ?>
              <tr>
                <td>#<?= $pkg['id'] ?></td>
                <td><strong><?= htmlspecialchars($pkg['name']) ?></strong></td>
                <td><span class="badge" style="background:<?= htmlspecialchars($pkg['badge_color'] ?? '#6b7280') ?>"><?= htmlspecialchars($pkg['badge_label'] ?? '') ?></span></td>
                <td>₹<?= number_format($pkg['price']) ?></td>
                <td><?= $pkg['duration_days'] ?> days</td>
                <td><?= $pkg['priority_order'] ?></td>
                <td><?= $pkg['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                <td>-</td>
                <td>
                  <a href="<?= BASE_URL ?>/admin/premium-packages/edit/<?= $pkg['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                  <form method="post" action="<?= BASE_URL ?>/admin/premium-packages/delete/<?= $pkg['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this package?');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php if (!empty($features)): ?>
                <tr class="table-light"><td colspan="9" class="small text-muted py-1 ps-5">Features: <?= htmlspecialchars(implode(' | ', $features)) ?></td></tr>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
