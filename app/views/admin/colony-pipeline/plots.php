<?php
$colony = $colony ?? [];
$plots = $plots ?? [];
$plotStats = $plot_stats ?? [];
$currentPage = $current_page ?? 1;
$totalPages = $total_pages ?? 1;
$total = $total_plots ?? 0;
$perPage = $per_page ?? 25;
$filters = $filters ?? [];
$colonyId = (int)($colony['id'] ?? 0);
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">
        <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $colonyId ?>" class="text-decoration-none">
          <?= htmlspecialchars($colony['name'] ?? 'Colony') ?>
        </a>
        <span class="text-muted"> / <?= __('cp_plots') ?></span>
      </h1>
      <span class="text-muted"><?= number_format($total) ?> <?= __('cp_plots') ?> &middot; <?= __('cp_block') ?>: <?= htmlspecialchars($filters['block'] ?? __('cp_all')) ?></span>
    </div>
    <div>
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $colonyId ?>/layout" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-drafting-compass me-1"></i><?= __('cp_layout') ?>
      </a>
      <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $colonyId ?>/pricing" class="btn btn-outline-success btn-sm">
        <i class="fas fa-tags me-1"></i><?= __('cp_pricing') ?>
      </a>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="fas fa-filter me-1"></i><?= __('cp_filters') ?>
      </button>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card aps-cp-card"><div class="card-body text-center">
        <div class="fs-3 text-primary"><?= number_format((int)($plotStats['total'] ?? 0)) ?></div>
        <div class="text-muted small"><?= __('cp_total_plots') ?></div>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card"><div class="card-body text-center">
        <div class="fs-3 text-success"><?= number_format((int)($plotStats['available'] ?? 0)) ?></div>
        <div class="text-muted small"><?= __('cp_available') ?></div>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card"><div class="card-body text-center">
        <div class="fs-3 text-warning"><?= number_format((int)($plotStats['booked'] ?? 0)) ?></div>
        <div class="text-muted small"><?= __('cp_booked') ?></div>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card"><div class="card-body text-center">
        <div class="fs-3 text-danger"><?= number_format((int)($plotStats['sold'] ?? 0)) ?></div>
        <div class="text-muted small"><?= __('cp_sold') ?></div>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card"><div class="card-body text-center">
        <div class="fs-3 text-info">₹<?= number_format((float)($plotStats['total_value'] ?? 0) / 100000, 1) ?>L</div>
        <div class="text-muted small"><?= __('cp_total_value') ?></div>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card aps-cp-card"><div class="card-body text-center">
        <div class="fs-3 text-secondary">₹<?= number_format((float)($plotStats['avg_ppsf'] ?? 0), 0) ?></div>
        <div class="text-muted small"><?= __('cp_avg_per_sqft') ?></div>
      </div></div>
    </div>
  </div>

  <div class="card aps-cp-card mb-4">
    <div class="card-header aps-cp-card-header">
      <strong><i class="fas fa-map me-2"></i><?= __('cp_plot_inventory') ?></strong>
      <div class="d-flex gap-2">
        <?php if (!empty($filters['status'])): ?>
          <span class="badge bg-primary"><?= __('cp_status') ?>: <?= htmlspecialchars($filters['status']) ?></span>
        <?php endif; ?>
        <?php if (!empty($filters['search'])): ?>
          <span class="badge bg-info"><?= __('cp_search') ?>: <?= htmlspecialchars($filters['search']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th><?= __('cp_plot_no') ?></th>
            <th><?= __('cp_block') ?></th>
            <th><?= __('cp_area') ?> (sqft)</th>
            <th><?= __('cp_dimensions') ?></th>
            <th><?= __('cp_facing') ?></th>
            <th><?= __('cp_per_sqft') ?></th>
            <th><?= __('cp_total_price') ?></th>
            <th><?= __('cp_status') ?></th>
            <th><?= __('cp_flags') ?></th>
            <th><?= __('cp_actions') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($plots)): ?>
            <tr><td colspan="10" class="text-center text-muted py-4">
              <i class="fas fa-map fa-3x mb-3 d-block text-muted"></i>
              <?= __('cp_no_plots_found') ?> <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $colonyId ?>/layout"><?= __('cp_configure_layout') ?></a>
            </td></tr>
          <?php else: ?>
            <?php foreach ($plots as $plot): ?>
              <?php
                $statusClass = match($plot['status'] ?? '') {
                  'available' => 'success',
                  'booked' => 'warning',
                  'sold' => 'danger',
                  'hold', 'reserved' => 'secondary',
                  'cancelled' => 'dark',
                  default => 'info'
                };
                $flags = [];
                if (!empty($plot['corner_plot'])) $flags[] = '<span class="badge bg-warning text-dark" title="' . __('cp_corner_plot') . '"><i class="fas fa-angle-double-up"></i> ' . __('cp_corner') . '</span>';
                if (!empty($plot['park_facing'])) $flags[] = '<span class="badge bg-success" title="' . __('cp_park_facing') . '"><i class="fas fa-tree"></i> ' . __('cp_park') . '</span>';
                if (!empty($plot['road_width_ft']) && $plot['road_width_ft'] >= 40) $flags[] = '<span class="badge bg-info" title="' . __('cp_wide_road') . '"><i class="fas fa-road"></i> ' . __('cp_wide_road') . '</span>';
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($plot['plot_number'] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($plot['block'] ?? '-') ?></td>
                <td><?= number_format((float)($plot['area_sqft'] ?? 0), 0) ?></td>
                <td>
                  <?php if (!empty($plot['width_ft']) && !empty($plot['length_ft'])): ?>
                    <?= number_format($plot['width_ft'], 0) ?> x <?= number_format($plot['length_ft'], 0) ?> ft
                  <?php elseif (!empty($plot['dimension_label'])): ?>
                    <?= htmlspecialchars($plot['dimension_label']) ?>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(ucfirst($plot['facing'] ?? '—')) ?></td>
                <td><?php
                  $ppsf = (float)($plot['price_per_sqft'] ?? 0);
                  echo $ppsf > 0 ? '₹' . number_format($ppsf, 0) : '<span class="text-muted">—</span>';
                ?></td>
                <td><?php
                  $tp = (float)($plot['total_price'] ?? 0);
                  echo $tp > 0 ? '<strong>₹' . number_format($tp / 100000, 2) . 'L</strong>' : '<span class="text-muted">—</span>';
                ?></td>
                <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($plot['status'] ?? 'unknown') ?></span></td>
                <td><?= !empty($flags) ? implode(' ', $flags) : '<span class="text-muted">—</span>' ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/admin/plots/<?= (int)$plot['id'] ?>" class="btn btn-outline-primary btn-sm" title="<?= __('cp_view') ?>">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/admin/plots/<?= (int)$plot['id'] ?>/edit" class="btn btn-outline-warning btn-sm" title="<?= __('cp_edit') ?>">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $currentPage - 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&block=<?= urlencode($filters['block'] ?? '') ?>"><?= __('cp_previous') ?></a>
        </li>
        <?php for ($i = max(1, $currentPage - 3); $i <= min($totalPages, $currentPage + 3); $i++): ?>
          <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filters['status'] ?? '') ?>&block=<?= urlencode($filters['block'] ?? '') ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $currentPage + 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&block=<?= urlencode($filters['block'] ?? '') ?>"><?= __('cp_next') ?></a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<div class="modal fade" id="filterModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="GET">
        <div class="modal-header">
          <h5 class="modal-title"><?= __('cp_filter_plots') ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><?= __('cp_status') ?></label>
            <select name="status" class="form-select">
              <option value=""><?= __('cp_all_status') ?></option>
              <?php foreach (['available','booked','sold','hold','reserved','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label"><?= __('cp_block') ?></label>
            <select name="block" class="form-select">
              <option value=""><?= __('cp_all_blocks') ?></option>
              <?php
                $blockList = [];
                foreach ($plots as $p) {
                  if (!empty($p['block']) && !in_array($p['block'], $blockList)) $blockList[] = $p['block'];
                }
                sort($blockList);
                foreach ($blockList as $b): ?>
                  <option value="<?= htmlspecialchars($b) ?>" <?= ($filters['block'] ?? '') === $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                <?php endforeach;
              ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <a href="?" class="btn btn-outline-secondary"><?= __('cp_clear_filters') ?></a>
          <button type="submit" class="btn btn-primary"><?= __('cp_apply_filters') ?></button>
        </div>
      </form>
    </div>
  </div>
</div>
