<?php $colonies = $colonies ?? []; ?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Colony Feasibility — Pricing Overview</h1>
      <span class="text-muted">Compare recommended pricing across all colonies</span>
    </div>
  </div>

  <?php if (empty($colonies)): ?>
    <div class="card aps-cp-card">
      <div class="card-body text-center py-5">
        <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
        <h5>No Colonies Found</h5>
        <p class="text-muted">Create colonies in the pipeline to run feasibility analysis.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="card aps-cp-card">
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Colony</th>
              <th class="text-center">Plots</th>
              <th class="text-end">Current Price</th>
              <th class="text-end">Recommended ₹/sqft</th>
              <th class="text-end">Cost Basis ₹/sqft</th>
              <th class="text-center">Last Calculated</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($colonies as $c): ?>
              <?php
                $recPrice = $c['recommended_price'] ?? null;
                $curPrice = (float) ($c['current_starting_price'] ?? 0);
                $diff = ($recPrice && $curPrice > 0) ? round((($recPrice - $curPrice) / $curPrice) * 100, 1) : null;
              ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($c['name']) ?></strong>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary"><?= (int) $c['total_plots'] ?></span>
                  <span class="text-muted small">(<?= (int) $c['available_plots'] ?> avail)</span>
                </td>
                <td class="text-end">
                  <?= $curPrice > 0 ? '₹' . number_format($curPrice, 0) . '/sqft' : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="text-end">
                  <?php if ($recPrice): ?>
                    <strong class="text-primary">₹<?= number_format($recPrice, 0) ?>/sqft</strong>
                  <?php else: ?>
                    <span class="text-muted">Not calculated</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <?= $c['cost_basis'] ? '₹' . number_format($c['cost_basis'], 0) : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="text-center">
                  <?php if ($c['last_calculated']): ?>
                    <small class="text-muted"><?= date('d M Y H:i', strtotime($c['last_calculated'])) ?></small>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <a href="<?= BASE_URL ?>/admin/colony-feasibility/<?= (int) $c['colony_id'] ?>"
                     class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-calculator me-1"></i>Calculate
                  </a>
                  <?php if ($c['last_calculated']): ?>
                    <a href="<?= BASE_URL ?>/admin/colony-feasibility/<?= (int) $c['colony_id'] ?>/history"
                       class="btn btn-sm btn-outline-secondary">
                      <i class="fas fa-history"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
