<?php
$page_title = 'Saved Properties - APS Dream Home';
$extraHead = '<style>
    .property-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .property-card:hover { transform: translateY(-3px); }
    .save-btn { position: absolute; top: 10px; right: 10px; z-index: 10; }
</style>';
?>

<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-heart me-2 text-danger"></i>Saved Properties</h3>
        <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary"><i class="fas fa-search me-2"></i>Browse More</a>
    </div>

    <?php if (empty($properties)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No saved properties yet</h5>
                <p class="text-muted">Save properties you're interested in to find them quickly later.</p>
                <a href="<?= BASE_URL ?>/properties" class="btn btn-primary"><i class="fas fa-search me-2"></i>Browse Properties</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($properties as $p): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card property-card h-100 position-relative">
                        <div class="save-btn">
                            <button class="btn btn-sm btn-danger" onclick="unsaveProperty(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['listing_type']) ?>')">
                                <i class="fas fa-heart-broken"></i>
                            </button>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <span class="badge bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'sold' ? 'dark' : 'warning') ?> mb-2">
                                <?= ucfirst($p['status'] ?? 'pending') ?>
                            </span>
                            <h5 class="card-title"><?= htmlspecialchars($p['title'] ?? 'Property') ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($p['location'] ?? 'Location not specified') ?>
                            </p>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-info"><?= htmlspecialchars($p['property_type'] ?? 'N/A') ?></span>
                                <span class="text-success fw-bold">₹<?= number_format($p['price'] ?? 0) ?></span>
                            </div>
                            <div class="d-grid">
                                <?php if ($p['listing_type'] === 'user'): ?>
                                    <a href="<?= BASE_URL ?>/listing/<?= (int)$p['property_id'] ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                <?php elseif ($p['listing_type'] === 'resell'): ?>
                                    <a href="<?= BASE_URL ?>/resell-properties/<?= (int)$p['property_id'] ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/property/<?= (int)$p['property_id'] ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer text-muted small">
                            Saved: <?= date('d M Y', strtotime($p['saved_at'] ?? 'now')) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function unsaveProperty(propertyId, listingType) {
    if (!confirm('Remove this property from saved list?')) return;
    
    fetch('<?= BASE_URL ?>/marketplace/toggle-save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'property_id=' + propertyId + '&listing_type=' + listingType
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Failed: ' + (data.message || 'Unknown error'));
    })
    .catch(() => alert('Network error'));
}
</script>