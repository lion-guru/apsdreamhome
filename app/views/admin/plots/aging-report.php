<?php
/** @var array $colonies */
/** @var array $buckets */
/** @var array $values */
/** @var array $rows */
$colonies = $colonies ?? [];
$buckets = $buckets ?? ['fast' => 0, 'normal' => 0, 'slow' => 0, 'stagnant' => 0];
$values = $values ?? ['fast' => 0, 'normal' => 0, 'slow' => 0, 'stagnant' => 0];
$rows = $rows ?? [];
$colonyId = $colony_id ?? 0;
$bucket = $bucket ?? '';
$cards = [
    'fast' => ['Fast Moving', '< 30 days', 'success'],
    'normal' => ['Normal', '30–90 days', 'primary'],
    'slow' => ['Slow Moving', '90–180 days', 'warning'],
    'stagnant' => ['Stagnant Stock', '> 180 days', 'danger'],
];
$qs = $colonyId > 0 ? '&colony_id=' . $colonyId : '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Inventory Aging &amp; Velocity</h4>
        <a href="<?= BASE_URL ?>/admin/plots" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>All Plots</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/plots/aging-report" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Colony</label>
                    <select name="colony_id" class="form-select">
                        <option value="0">All colonies</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($colonyId === (int)$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bucket</label>
                    <select name="bucket" class="form-select">
                        <option value="">All buckets</option>
                        <option value="fast" <?= $bucket === 'fast' ? 'selected' : '' ?>>Fast Moving (&lt; 30d)</option>
                        <option value="normal" <?= $bucket === 'normal' ? 'selected' : '' ?>>Normal (30–90d)</option>
                        <option value="slow" <?= $bucket === 'slow' ? 'selected' : '' ?>>Slow Moving (90–180d)</option>
                        <option value="stagnant" <?= $bucket === 'stagnant' ? 'selected' : '' ?>>Stagnant (&gt; 180d)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= BASE_URL ?>/admin/plots/aging-report" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ($cards as $k => [$label, $range, $color]): ?>
        <div class="col-md-3 col-6">
            <a href="<?= BASE_URL ?>/admin/plots/aging-report?bucket=<?= $k ?><?= $qs ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm <?= $bucket === $k ? 'border border-' . $color : '' ?>">
                    <div class="card-body text-center">
                        <small class="text-muted d-block"><?= $label ?> <span class="text-muted">(<?= $range ?>)</span></small>
                        <div class="fs-3 fw-bold"><?= number_format($buckets[$k] ?? 0) ?></div>
                        <small class="text-<?= $color ?> fw-bold">Rs.<?= number_format($values[$k] ?? 0) ?> locked</small>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Available Plots by Age (<?= count($rows) ?>)</h5>
            <?php if (($buckets['slow'] ?? 0) + ($buckets['stagnant'] ?? 0) > 0): ?>
                <small class="text-muted">Target slow/stagnant stock for promos &amp; associate bonuses</small>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($rows)): ?>
                <div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><p>No plots in this bucket.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Plot</th><th>Colony</th><th>Value</th><th>Listed</th><th>Age</th><th>Bucket</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r):
                                $b = $r['bucket'];
                                $badge = $b === 'fast' ? 'success' : ($b === 'normal' ? 'primary' : ($b === 'slow' ? 'warning' : 'danger'));
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['plot_number']) ?></strong></td>
                                <td><?= htmlspecialchars($r['colony_name'] ?? '-') ?></td>
                                <td>Rs.<?= number_format((float)($r['total_price'] ?? 0)) ?></td>
                                <td><small><?= htmlspecialchars(substr((string)($r['created_at'] ?? ''), 0, 10)) ?></small></td>
                                <td><?= (int)($r['age_in_days'] ?? 0) ?> days</td>
                                <td><span class="badge bg-<?= $badge ?>"><?= $cards[$b][0] ?></span></td>
                                <td><a href="<?= BASE_URL ?>/admin/plots/<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
