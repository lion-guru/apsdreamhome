<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">VR Tours</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-vr-cardboard me-3 text-primary"></i><?= ($page_title ?? 'VR Tours') ?></h1>
        </div>
    </div>

    <?php $featured_tours = $featured_tours ?? []; $tour_categories = $tour_categories ?? []; ?>

    <div class="row g-4 mb-5">
        <div class="col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i>Categories</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active d-flex justify-content-between">All Tours <span class="badge bg-primary rounded-pill"><?= count($featured_tours) ?></span></a>
                        <?php foreach ($tour_categories as $key => $cat): ?>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between"><?= ($cat['name'] ?? $key) ?> <span class="badge bg-secondary rounded-pill"><?= ($cat['count'] ?? 0) ?></span></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <?php if (empty($featured_tours)): ?>
            <div class="text-center py-5">
                <i class="fas fa-vr-cardboard fa-5x text-muted mb-3"></i>
                <h3>No VR Tours Available</h3>
                <p class="text-muted">Check back soon for immersive property tours.</p>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featured_tours as $tour): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($tour['thumbnail'] ?? 'assets/img/no-image.jpg') ?>" alt="" class="rounded" style="width:80px;height:80px;object-fit:cover;" onerror="this.src='<?= BASE_URL ?>/assets/img/no-image.jpg'">
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-1"><?= ($tour['title'] ?? 'Untitled Tour') ?></h5>
                                    <small class="text-muted"><i class="fas fa-clock me-1"></i><?= ($tour['duration'] ?? 'N/A') ?></small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-warning"><i class="fas fa-star"></i> <?= ($tour['rating'] ?? '0') ?></span>
                                    <span class="text-muted ms-2"><i class="fas fa-eye"></i> <?= number_format($tour['views'] ?? 0) ?></span>
                                </div>
                                <a href="<?= $base ?? BASE_URL ?>properties/<?= ($tour['property_id'] ?? '') ?>" class="btn btn-outline-primary btn-sm">View Tour</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
