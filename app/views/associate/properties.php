<?php
$page_title = $page_title ?? 'My Properties - APS Dream Home';
$properties = $properties ?? [];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-building text-primary me-2"></i>My Properties</h4>
            <small class="text-muted">Manage your property listings</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/associate/browse" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-search me-1"></i>Browse All
            </a>
            <a href="<?= BASE_URL ?>/associate/add-property" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add Property
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (empty($properties)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Properties Listed Yet</h5>
                <p class="text-muted mb-3">Start listing properties to earn commissions from sales.</p>
                <a href="<?= BASE_URL ?>/associate/add-property" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>List Your First Property
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($properties as $p):
                $statusClass = ($p['status'] ?? '') === 'approved' ? 'success' : (($p['status'] ?? '') === 'rejected' ? 'danger' : (($p['status'] ?? '') === 'archived' ? 'secondary' : 'warning'));
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="" style="height:180px;object-fit:cover;">
                    <?php else: ?>
                        <div class="card-img-top d-flex align-items-center justify-content-center" style="height:180px;background:#f1f5f9;">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title fw-bold mb-0"><?= htmlspecialchars($p['title'] ?? 'Untitled') ?></h6>
                            <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($p['status'] ?? 'pending') ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-light text-dark"><i class="fas fa-home me-1"></i><?= ucfirst(str_replace('_', ' ', $p['property_type'] ?? '')) ?></span>
                            <span class="badge bg-light text-dark"><i class="fas fa-tag me-1"></i><?= ucfirst($p['listing_type'] ?? '') ?></span>
                        </div>
                        <div class="fw-bold text-primary mb-1" style="font-size:1.1rem;">₹<?= number_format($p['price'] ?? 0) ?></div>
                        <?php if (!empty($p['area_sqft'])): ?>
                            <small class="text-muted"><i class="fas fa-ruler-combined me-1"></i><?= number_format($p['area_sqft']) ?> sq ft</small>
                        <?php endif; ?>
                        <?php if (!empty($p['address'])): ?>
                            <div class="small text-muted mt-1"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars(mb_substr($p['address'], 0, 60)) ?></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <small class="text-muted"><i class="fas fa-eye me-1"></i><?= (int)($p['views'] ?? 0) ?> views</small>
                            <small class="text-muted"><?= $p['date'] ?? '' ?></small>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>/associate/properties/edit/<?= (int)$p['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <?php if (($p['status'] ?? '') !== 'archived'): ?>
                            <form action="<?= BASE_URL ?>/associate/properties/delete/<?= (int)$p['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Archive this property?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
