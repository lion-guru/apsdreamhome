<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Marketplace</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-store me-3 text-info"></i><?= ($page_title ?? 'Virtual Marketplace') ?></h1>
        </div>
    </div>

    <?php $virtual_properties = $virtual_properties ?? []; $market_stats = $market_stats ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <h3 class="text-primary mb-0"><?= number_format($market_stats['total_virtual_properties'] ?? 0) ?></h3>
                    <small class="text-muted">Total Properties</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <h3 class="text-success mb-0"><?= ($market_stats['properties_for_sale'] ?? 0) ?></h3>
                    <small class="text-muted">For Sale</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <h3 class="text-warning mb-0"><?= number_format($market_stats['avg_sale_price'] ?? 0) ?> VRC</h3>
                    <small class="text-muted">Avg Price</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <h3 class="text-info mb-0"><?= number_format($market_stats['monthly_volume'] ?? 0) ?> VRC</h3>
                    <small class="text-muted">Monthly Volume</small>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($virtual_properties)): ?>
    <div class="text-center py-5">
        <i class="fas fa-store fa-5x text-muted mb-3"></i>
        <h3>No Properties in Marketplace</h3>
        <p class="text-muted">Check back later or create your own virtual property.</p>
        <a href="<?= $base ?? BASE_URL ?>metaverse/virtual-development" class="btn btn-info btn-lg"><i class="fas fa-plus me-2"></i>Create Property</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($virtual_properties as $vp): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title"><?= ($vp['name'] ?? 'Unnamed') ?></h5>
                    <p class="card-text text-muted small"><?= ($vp['description'] ?? '') ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <h4 class="text-primary mb-0"><?= number_format($vp['base_price'] ?? 0) ?> VRC</h4>
                        <a href="<?= $base ?? BASE_URL ?>metaverse/virtual-property/<?= ($vp['id'] ?? '') ?>" class="btn btn-outline-info btn-sm">View</a>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="fas fa-user me-1"></i><?= ($vp['creator_name'] ?? 'Unknown') ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
