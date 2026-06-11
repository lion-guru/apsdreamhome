<?php $pageTitle = $page_title ?? 'Smart Home Device Catalog'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-store me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php foreach (($catalog ?? []) as $category => $devices): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent"><h5 class="mb-0 text-capitalize"><i class="fas fa-<?= $category === 'lighting' ? 'lightbulb' : ($category === 'security' ? 'shield-alt' : ($category === 'climate' ? 'thermometer-half' : 'plug')) ?> me-2"></i><?= ucfirst($category) ?></h5></div>
            <div class="card-body aps-cp-card-body">
                <div class="row g-3">
                    <?php foreach ($devices as $key => $d): ?>
                        <div class="col-md-4">
                            <div class="card h-100 border">
                                <div class="card-body aps-cp-card-body">
                                    <h6 class="card-title"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></h6>
                                    <p class="text-primary fw-bold mb-2">₹<?= number_format($d['price'] ?? 0) ?></p>
                                    <ul class="list-unstyled small mb-0">
                                        <?php foreach (($d['features'] ?? []) as $f): ?>
                                            <li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($f) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
