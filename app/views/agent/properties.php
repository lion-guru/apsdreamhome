<?php
$properties = $properties ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color:#15803d;font-weight:700;"><i class="fas fa-building me-2"></i>My Properties</h4>
        <p class="text-muted mb-0">Properties assigned to you or listed by you</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success fs-6"><?= count($properties) ?> Properties</span>
    </div>
</div>

<?php if (empty($properties)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:#dbeafe;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-home fa-2x" style="color:#2563eb;"></i>
        </div>
        <h5 class="text-muted">No properties yet</h5>
        <p class="text-muted mb-0">Your assigned properties will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($properties as $prop): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100" style="transition:transform .2s;">
            <div style="height:140px;background:linear-gradient(135deg,#15803d,#22c55e);border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-home fa-3x" style="color:rgba(255,255,255,.3);"></i>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0" style="font-weight:600;"><?= htmlspecialchars($prop['title'] ?? 'Property') ?></h6>
                    <?php
                    $status = $prop['status'] ?? 'active';
                    $sColor = $status === 'sold' ? 'bg-danger' : ($status === 'active' ? 'bg-success' : 'bg-warning text-dark');
                    ?>
                    <span class="badge <?= $sColor ?>"><?= ucfirst($status) ?></span>
                </div>
                <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($prop['location'] ?? '-') ?></p>
                <?php if (!empty($prop['area_sqft'])): ?>
                    <p class="mb-2"><i class="fas fa-ruler-combined me-1 text-muted"></i><?= number_format($prop['area_sqft']) ?> sq ft</p>
                <?php endif; ?>
                <h5 style="color:#15803d;font-weight:700;">₹<?= number_format($prop['price'] ?? 0) ?></h5>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <small class="text-muted">Listed: <?= date('d M Y', strtotime($prop['created_at'] ?? 'now')) ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
