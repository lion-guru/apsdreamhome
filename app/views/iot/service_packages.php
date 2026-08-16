<?php $pageTitle = $page_title ?? 'Smart Home Service Packages'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-tags me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
    <div class="row g-3">
        <?php foreach (($packages ?? []) as $key => $pkg): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <?php if ($key === 'premium'): ?><div class="card-header bg-warning text-dark text-center fw-bold">Most Popular</div><?php endif; ?>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($pkg['name'] ?? ucfirst($key)) ?></h5>
                        <h3 class="text-primary">₹<?= number_format($pkg['price'] ?? 0) ?></h3>
                        <hr>
                        <p class="fw-bold mb-2">Devices Included:</p>
                        <ul class="list-unstyled small">
                            <?php foreach (($pkg['devices'] ?? []) as $d): ?>
                                <li><i class="fas fa-check-circle text-success me-1"></i><?= htmlspecialchars($d ?? '') ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="fw-bold mb-2 mt-3">Features:</p>
                        <ul class="list-unstyled small">
                            <?php foreach (($pkg['features'] ?? []) as $f): ?>
                                <li><i class="fas fa-star text-warning me-1"></i><?= htmlspecialchars($f ?? '') ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="text-muted small mt-3"><?= htmlspecialchars($pkg['support'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
