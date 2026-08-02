<?php $pageTitle = $page_title ?? 'Edge AI Processing'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-brain me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <?php foreach (($edge_capabilities ?? []) as $key => $cap): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body aps-cp-card-body">
                        <h5 class="card-title text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $key)) ?></h5>
                        <span class="badge bg-<?= ($cap['supported'] ?? false) ? 'success' : 'secondary' ?> mb-2"><?= ($cap['supported'] ?? false) ? 'Supported' : 'Unsupported' ?></span>
                        <?php if (!empty($cap['models'])): ?>
                            <ul class="list-unstyled small mb-0 mt-2">
                                <?php foreach ($cap['models'] as $m): ?><li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($m) ?></li><?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($cap['throughput'])): ?><p class="small text-muted mt-2 mb-0">Throughput: <?= htmlspecialchars($cap['throughput']) ?></p><?php endif; ?>
                        <?php if (!empty($cap['cache_size'])): ?><p class="small text-muted mt-2 mb-0">Cache: <?= htmlspecialchars($cap['cache_size']) ?></p><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
