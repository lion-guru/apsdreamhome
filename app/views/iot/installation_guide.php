<?php $pageTitle = $page_title ?? 'Smart Home Installation Guide'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-tools me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
    <div class="row g-3">
        <?php foreach (($guide_steps ?? []) as $phase): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title"><i class="fas fa-check-circle text-success me-2"></i><?= htmlspecialchars($phase['title'] ?? '') ?></h5>
                        <ol class="mb-0 small">
                            <?php foreach (($phase['steps'] ?? []) as $step): ?>
                                <li class="mb-1"><?= htmlspecialchars($step ?? '') ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
