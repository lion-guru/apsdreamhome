<?php
$features = $features ?? [];
$category = $category ?? '';
$csrf = $_SESSION['csrf_token'] ?? '';
$cats = ['energy' => 'Energy', 'water' => 'Water', 'waste' => 'Waste', 'materials' => 'Materials', 'air' => 'Air', 'landscape' => 'Landscape', 'smart' => 'Smart'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-seedling me-2 text-info"></i>Green Features Catalog</h2>
    <a href="<?= BASE_URL ?>/admin/sustainable/feature/form" class="btn btn-info text-white"><i class="fas fa-plus me-1"></i> Add Feature</a>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group" role="group">
            <a href="<?= BASE_URL ?>/admin/sustainable/features" class="btn btn-<?= $category === '' ? 'primary' : 'outline-primary' ?>">All</a>
            <?php foreach ($cats as $k => $l): ?>
                <a href="<?= BASE_URL ?>/admin/sustainable/features?category=<?= $k ?>" class="btn btn-<?= $category === $k ? 'primary' : 'outline-primary' ?>"><?= $l ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row">
    <?php if (empty($features)): ?>
        <div class="col-12"><p class="text-muted text-center py-4">No features found.</p></div>
    <?php else: ?>
        <?php foreach ($features as $f): ?>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1"><i class="fas <?= htmlspecialchars($f['icon'] ?? 'fa-leaf') ?> me-2 text-success"></i><?= htmlspecialchars($f['name'] ?? '') ?></h6>
                        <span class="badge bg-light text-dark"><?= ucfirst($f['category']) ?></span>
                    </div>
                    <p class="small text-muted mb-2"><?= htmlspecialchars($f['description'] ?? '') ?></p>
                    <div class="small">
                        <div class="d-flex justify-content-between"><span>CO₂ saved:</span><strong><?= number_format($f['co2_saved_kg_yr'] ?? 0) ?> kg/yr</strong></div>
                        <div class="d-flex justify-content-between"><span>Est. cost:</span><strong>₹<?= number_format($f['cost_estimate'] ?? 0) ?></strong></div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/sustainable/feature/form/<?= $f['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/feature/delete/<?= $f['id'] ?>" class="d-inline" onsubmit="return confirm('Delete feature?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
