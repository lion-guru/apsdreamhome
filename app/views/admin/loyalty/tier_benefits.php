<?php
$pageTitle = $pageTitle ?? 'Tier Benefits';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$tiers = $tiers ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-crown me-2 text-warning"></i>Tier Benefits</h1>
        <a href="<?= $base ?>/admin/loyalty" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <?php if (empty($tiers)): ?>
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-crown fa-3x text-muted mb-3"></i>
                <p class="text-muted">No loyalty tiers configured yet.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($tiers as $tier): ?>
            <?php
                $tierName = strtolower($tier['name'] ?? $tier['tier_name'] ?? '');
                $badgeClass = $tierName === 'diamond' ? 'dark' : ($tierName === 'platinum' ? 'primary' : ($tierName === 'gold' ? 'warning' : ($tierName === 'silver' ? 'secondary' : 'light')));
                $textDark = $tierName === 'light' || $tierName === 'bronze' || $tierName === 'gold';
            ?>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100 border-top-<?= $badgeClass === 'dark' ? 'dark' : ($badgeClass === 'primary' ? 'primary' : ($badgeClass === 'warning' ? 'warning' : ($badgeClass === 'secondary' ? 'secondary' : 'light'))) ?> border-3">
                    <div class="card-body aps-cp-card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-crown fa-3x text-<?= $badgeClass === 'warning' ? 'warning' : ($badgeClass === 'light' ? 'muted' : $badgeClass) ?>"></i>
                            <h4 class="mt-2 text-uppercase"><?= htmlspecialchars(ucfirst($tierName)) ?></h4>
                            <span class="badge bg-<?= $badgeClass ?> <?= $textDark ? 'text-dark' : '' ?> p-2">Min: <?= number_format(intval($tier['min_points'] ?? $tier['points_required'] ?? 0)) ?> pts</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Members</span>
                            <strong><?= number_format(intval($tier['member_count'] ?? $tier['members'] ?? 0)) ?></strong>
                        </div>
                        <?php $benefits = $tier['benefits'] ?? []; ?>
                        <?php if (!empty($benefits)): ?>
                            <hr>
                            <h6 class="fw-bold">Benefits</h6>
                            <ul class="list-unstyled">
                                <?php foreach ($benefits as $b): ?>
                                <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i><?= htmlspecialchars(is_string($b) ? $b : ($b['benefit_name'] ?? $b['name'] ?? '')) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
