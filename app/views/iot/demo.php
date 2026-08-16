<?php $pageTitle = $page_title ?? 'Smart Home Demo'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-play-circle me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
    <div class="row g-3">
        <?php foreach (($demo_features ?? []) as $key => $desc): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-1 text-primary mb-3">
                            <i class="fas fa-<?= $key === 'virtual_tour' ? 'vr-cardboard' : ($key === 'smart_lighting' ? 'lightbulb' : ($key === 'security_system' ? 'shield-alt' : ($key === 'energy_monitoring' ? 'chart-line' : ($key === 'climate_control' ? 'thermometer-half' : 'cogs')))) ?>"></i>
                        </div>
                        <h5 class="card-title"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($desc ?? '') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
