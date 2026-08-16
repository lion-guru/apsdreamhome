<?php
/**
 * @var array $experiments
 */
$pageTitle    = $page_title ?? 'A/B Experiments';
$flashSuccess = $_SESSION['success'] ?? $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['error']   ?? $_SESSION['flash_error']   ?? null;
unset($_SESSION['success'], $_SESSION['flash_success'], $_SESSION['error'], $_SESSION['flash_error']);
$baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-flask me-2 text-primary"></i><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="text-muted mb-0">Run controlled experiments and measure conversion lifts.</p>
        </div>
        <div>
            <form method="POST" action="<?= $baseUrl ?>/admin/experiments/seed-defaults" class="d-inline" onsubmit="return confirm('Seed the 4 default experiments (homepage_cta, property_card_layout, cta_button_color, registration_form_length)?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-seedling me-1"></i> Seed Defaults
                </button>
            </form>
            <a href="<?= $baseUrl ?>/admin/experiments/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Experiment
            </a>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle me-1"></i> <?= htmlspecialchars($flashError ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-1"></i> All Experiments</h5>
            <span class="badge bg-secondary"><?= count($experiments) ?> total</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($experiments)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-flask fa-3x mb-3 opacity-25"></i>
                    <p class="mb-2">No experiments yet.</p>
                    <a href="<?= $baseUrl ?>/admin/experiments/create" class="btn btn-sm btn-outline-primary">Create your first one</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Variants</th>
                                <th>Traffic</th>
                                <th class="text-end">Users</th>
                                <th class="text-end">Conversions</th>
                                <th>Winner</th>
                                <th>Started</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($experiments as $exp): ?>
                                <?php
                                    $status = $exp['status'] ?? 'draft';
                                    $statusBadge = ['draft' => 'secondary', 'running' => 'success', 'ended' => 'dark'][$status] ?? 'secondary';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($exp['name'] ?? '') ?></strong>
                                        <?php if (!empty($exp['description'])): ?>
                                            <div class="small text-muted text-truncate" class="style-96974">
                                                <?= htmlspecialchars($exp['description'] ?? '') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst($status) ?></span></td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?= (int)($exp['variant_count'] ?? 0) ?> variants</span>
                                        <?php if (!empty($exp['variants_decoded'])): ?>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars(implode(', ', array_map(fn($v) => ($v['name'] ?? '?') . ' (' . ($v['weight'] ?? '?') . ')', $exp['variants_decoded']))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int)($exp['traffic_allocation'] ?? 100) ?>%</td>
                                    <td class="text-end"><?= number_format((int)($exp['unique_users'] ?? 0)) ?></td>
                                    <td class="text-end"><?= number_format((int)($exp['total_conversions'] ?? 0)) ?></td>
                                    <td>
                                        <?php if (!empty($exp['winner'])): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-trophy me-1"></i><?= htmlspecialchars($exp['winner'] ?? '') ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted"><?= !empty($exp['started_at']) ? htmlspecialchars($exp['started_at'] ?? '') : '—' ?></td>
                                    <td class="text-end">
                                        <a href="<?= $baseUrl ?>/admin/experiments/<?= (int)$exp['id'] ?>/results" class="btn btn-sm btn-outline-success" title="View Results Dashboard">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>/admin/experiments/<?= (int)$exp['id'] ?>" class="btn btn-sm btn-outline-primary" title="Show page">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>/admin/experiments/<?= (int)$exp['id'] ?>/export" class="btn btn-sm btn-outline-secondary" title="Export CSV">
                                            <i class="fas fa-file-csv"></i>
                                        </a>
                                        <form method="POST" action="<?= $baseUrl ?>/admin/experiments/<?= (int)$exp['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this experiment and ALL its events? This cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
