<?php
$extraHead = '<style>
    .pkg-card { border: 2px solid #eee; border-radius: 16px; transition: all 0.2s; cursor: pointer; }
    .pkg-card:hover { border-color: #ffc107; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,193,7,0.15); }
    .pkg-card.selected { border-color: #ffc107; background: #fffbe6; }
    .pkg-card .price { font-size: 1.8rem; font-weight: 700; color: #e67e22; }
    .pkg-card .duration { font-size: 0.85rem; color: #6c757d; }
    .pkg-badge-custom { position: absolute; top: -10px; right: 10px; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
</style>';
?>
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/properties">My Properties</a></li>
            <li class="breadcrumb-item active">Boost Listing</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card aps-cp-card">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-building me-2 text-primary"></i>Property</h5>
                    <h6><?= htmlspecialchars($property['name']) ?></h6>
                    <p class="text-muted small mb-1"><?= htmlspecialchars($property['address'] ?? '') ?></p>
                    <p class="mb-0"><strong>â‚¹<?= number_format($property['price']) ?></strong> <span class="text-capitalize badge bg-light text-dark"><?= $property['property_type'] ?> for <?= $property['listing_type'] ?></span></p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if ($activePackage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>This listing is already boosted with <strong><?= htmlspecialchars($activePackage['package_name']) ?></strong> package (active).
                </div>
                <a href="<?= BASE_URL ?>/user/properties" class="btn btn-primary"><i class="fas fa-arrow-left me-2"></i>Back to Properties</a>
            <?php elseif (empty($packages)): ?>
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No premium packages available right now. Check back later.</div>
                <a href="<?= BASE_URL ?>/user/properties" class="btn btn-primary"><i class="fas fa-arrow-left me-2"></i>Back to Properties</a>
            <?php else: ?>
                <h5 class="mb-3"><i class="fas fa-crown text-warning me-2"></i>Choose a Package</h5>
                <p class="text-muted mb-4">Boost your listing to get more visibility and sell faster.</p>

                <form method="post" action="<?= BASE_URL ?>/user/boost-property/purchase" id="boostForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                    <input type="hidden" name="package_id" id="selectedPackageId" value="">

                    <div class="row g-3">
                        <?php foreach ($packages as $pkg):
                            $features = json_decode($pkg['features'] ?? '[]', true);
                            $badgeStyle = $pkg['badge_color'] ? 'background:' . htmlspecialchars($pkg['badge_color']) . ';color:#fff;' : '';
                        ?>
                            <div class="col-md-6">
                                <div class="card pkg-card p-3 position-relative" onclick="selectPackage(<?= $pkg['id'] ?>, this)">
                                    <?php if (!empty($pkg['badge_label'])): ?>
                                        <span class="pkg-badge-custom" class="style-26193"><?= htmlspecialchars($pkg['badge_label']) ?></span>
                                    <?php endif; ?>
                                    <div class="card-body p-0">
                                        <h5 class="card-title"><?= htmlspecialchars($pkg['name']) ?></h5>
                                        <div class="price">â‚¹<?= number_format($pkg['price'] ?? 0) ?></div>
                                        <div class="duration mb-3">Valid for <?= $pkg['duration_days'] ?> days</div>
                                        <?php if (!empty($pkg['description'])): ?>
                                            <p class="small text-muted"><?= htmlspecialchars($pkg['description']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($features)): ?>
                                            <ul class="list-unstyled mb-0 small">
                                                <?php foreach ($features as $f): ?>
                                                    <li><i class="fas fa-check text-success me-1"></i><?= htmlspecialchars($f) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-crown me-2"></i>Activate Package
                        </button>
                        <a href="<?= BASE_URL ?>/user/properties" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (empty($activePackage) && !empty($packages)): ob_start(); ?>
<script>
function selectPackage(id, el) {
    document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selectedPackageId').value = id;
    document.getElementById('submitBtn').disabled = false;
}
</script>
<?php $extra_js = ob_get_clean(); endif; ?>
