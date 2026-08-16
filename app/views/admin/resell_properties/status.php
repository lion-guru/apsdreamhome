<?php
$p = $property ?? [];
$statusColors = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger','sold'=>'secondary'];
$st = $p['status'] ?? 'pending';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Update Property Status</h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($p['name'] ?? 'Property') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/resell-properties/edit/<?= $id ?>" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card aps-cp-card mb-4">
            <div class="card-header aps-cp-card-header">Current Status</div>
            <div class="card-body aps-cp-card-body text-center py-4">
                <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?> fs-4 mb-3"><?= ucfirst($st) ?></span>
                <div class="small text-muted mt-2"><?= htmlspecialchars($p['name'] ?? '') ?></div>
                <div class="small text-muted">₹<?= number_format((float)($p['price'] ?? 0)) ?></div>
            </div>
        </div>
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">Quick Info</div>
            <div class="card-body aps-cp-card-body">
                <div class="small mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i><?= htmlspecialchars($p['location'] ?? 'N/A') ?></div>
                <div class="small mb-1"><i class="fas fa-expand-arrows-alt me-1 text-muted"></i><?= number_format((int)($p['area_sqft'] ?? 0)) ?> sq ft</div>
                <div class="small"><i class="fas fa-eye me-1 text-muted"></i><?= (int)($p['views'] ?? 0) ?> views</div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header">Change Status</div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/resell-properties/status/<?= $id ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">New Status</label>
                        <div class="row g-2">
                            <?php foreach ([
                                'pending' => ['warning', 'fas fa-clock', 'Pending', 'Awaiting review'],
                                'verified' => ['info', 'fas fa-check', 'Verified', 'Details confirmed'],
                                'approved' => ['success', 'fas fa-check-double', 'Approved', 'Ready for listing'],
                                'rejected' => ['danger', 'fas fa-times', 'Rejected', 'Does not meet criteria'],
                                'sold' => ['secondary', 'fas fa-handshake', 'Sold', 'Property sold'],
                            ] as $val => [$color, $icon, $label, $desc]): ?>
                                <div class="col-md-4 col-6">
                                    <label class="d-block p-3 border rounded cursor-pointer <?= $st === $val ? 'border-' . $color . ' bg-' . $color . ' bg-opacity-10' : '' ?>" class="style-78508">
                                        <input type="radio" name="status" value="<?= $val ?>" class="d-none" <?= $st === $val ? 'checked' : '' ?> onchange="this.closest('label').querySelectorAll('label').forEach(l=>l.classList.remove('active'));document.querySelectorAll('.status-label').forEach(l=>l.classList.remove('fw-bold','text-primary'))">
                                        <div class="text-center">
                                            <i class="<?= $icon ?> fa-2x mb-2 text-<?= $color ?>"></i>
                                            <div class="fw-semibold small"><?= $label ?></div>
                                            <div class="text-muted" class="style-48967"><?= $desc ?></div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Status</button>
                        <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
