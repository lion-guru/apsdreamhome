<?php
$items = $items ?? [];
$category = $category ?? '';
$csrf = $_SESSION['csrf_token'] ?? '';
$cats = ['security'=>'Security','energy'=>'Energy','water'=>'Water','climate'=>'Climate','lighting'=>'Lighting','safety'=>'Safety','access'=>'Access','smart'=>'Smart'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-th-list me-2 text-primary"></i>IoT Device Catalog</h2>
    <a href="<?= BASE_URL ?>/admin/iot/catalog/form" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Catalog Item</a>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/iot/catalog" class="btn btn-<?= $category===''?'primary':'outline-primary' ?>">All</a>
            <?php foreach ($cats as $k=>$l): ?>
                <a href="<?= BASE_URL ?>/admin/iot/catalog?category=<?= $k ?>" class="btn btn-<?= $category===$k?'primary':'outline-primary' ?>"><?= $l ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row">
    <?php if (empty($items)): ?>
        <div class="col-12"><p class="text-muted text-center py-4">No catalog items.</p></div>
    <?php else: ?>
        <?php foreach ($items as $it): ?>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><h6 class="mb-1"><i class="fas fa-microchip me-2 text-primary"></i><?= htmlspecialchars($it['name'] ?? '') ?></h6><span class="badge bg-light text-dark"><?= ucfirst($it['category']) ?></span></div>
                    <p class="small text-muted mb-1"><?= htmlspecialchars($it['manufacturer'] ?? 'Generic') ?> · <?= strtoupper($it['protocol'] ?? 'wifi') ?></p>
                    <p class="small"><?= htmlspecialchars($it['description'] ?? '') ?></p>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/admin/iot/catalog/form/<?= $it['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="<?= BASE_URL ?>/admin/iot/catalog/delete/<?= $it['id'] ?>" class="d-inline" onsubmit="return confirm('Delete catalog item?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
