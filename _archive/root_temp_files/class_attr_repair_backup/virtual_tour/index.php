<div class="container-fluid py-4">
    <?php $property = $property ?? []; $tour_data = $tour_data ?? []; $ar_enabled = $ar_enabled ?? false; ?>
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>properties">Properties</a></li>
                    <li class="breadcrumb-item active">Virtual Tour</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-vr-cardboard me-3 text-primary"></i><?= ($page_title ?? 'Virtual Tour') ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0 position-relative">
                    <div id="panoramaViewer" class="style-19581" class="rounded">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center text-white">
                                <i class="fas fa-vr-cardboard fa-5x mb-3 opacity-50"></i>
                                <p class="lead">360&deg; Panorama Viewer</p>
                                <button class="btn btn-primary btn-lg" onclick="alert('Panorama viewer would initialize with Three.js/Pannellum')">
                                    <i class="fas fa-play me-2"></i>Start Tour
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 text-center">
                        <div class="card-body aps-cp-card-body">
                            <i class="fas fa-sync-alt fa-2x text-primary mb-2"></i>
                            <h6>360&deg; View</h6>
                            <small class="text-muted"><?= count($tour_data['panoramas'] ?? []) ?> panoramas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 text-center">
                        <div class="card-body aps-cp-card-body">
                            <i class="fas fa-th-large fa-2x text-success mb-2"></i>
                            <h6>Floor Plans</h6>
                            <small class="text-muted"><?= count($tour_data['floor_plans'] ?? []) ?> plans</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 text-center">
                        <div class="card-body aps-cp-card-body">
                            <i class="fas fa-crosshairs fa-2x text-warning mb-2"></i>
                            <h6>Hotspots</h6>
                            <small class="text-muted"><?= count($tour_data['hotspots'] ?? []) ?> hotspots</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($tour_data['panoramas'] ?? [])): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-images me-2 text-primary"></i>Panorama Views</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php foreach ($tour_data['panoramas'] as $pano): ?>
                        <div class="col-md-4">
                            <div class="border rounded p-2 text-center">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($pano['thumbnail_path'] ?? $pano['file_path'] ?? 'assets/img/no-image.jpg') ?>" alt="" class="img-fluid rounded mb-2" class="style-93542" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                                <small class="text-muted text-capitalize d-block"><?= ($pano['panorama_type'] ?? 'interior') ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Property Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h5><?= ($property['title'] ?? 'Property') ?></h5>
                    <p class="text-muted small"><?= ($property['city'] ?? '') ?>, <?= ($property['state'] ?? '') ?></p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Price</span><strong>₹<?= number_format($property['price'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Status</span><strong class="text-success"><?= ($property['status'] ?? 'Available') ?></strong></div>
                </div>
            </div>

            <?php if (!empty($tour_data['hotspots'] ?? [])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-crosshairs me-2 text-warning"></i>Hotspots</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($tour_data['hotspots'] as $hs): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span><?= ($hs['room_name'] ?? 'Room') ?></span>
                            <small class="text-muted"><?= ($hs['hotspot_type'] ?? 'navigation') ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($ar_enabled): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-cube fa-3x text-info mb-3"></i>
                    <h5>AR View Available</h5>
                    <p class="small text-muted">View this property in augmented reality on your mobile device.</p>
                    <button class="btn btn-info" onclick="alert('AR view would open on your device')"><i class="fas fa-cube me-2"></i>Open AR</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
