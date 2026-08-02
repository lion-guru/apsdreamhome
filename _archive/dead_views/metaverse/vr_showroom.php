<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">VR Showroom</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-vr-cardboard me-3 text-primary"></i><?= ($page_title ?? 'VR Showroom') ?></h1>
        </div>
    </div>

    <?php $property = $property ?? []; $vr_data = $vr_data ?? []; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0 position-relative">
                    <div class="vr-viewport" style="height: 500px; background: linear-gradient(135deg, #0a0a1a, #0f172a, #24243e);">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center text-white">
                                <i class="fas fa-vr-cardboard fa-5x mb-3 opacity-50"></i>
                                <p class="lead">VR Viewport Loading...</p>
                                <button class="btn btn-primary btn-lg mt-3" onclick="alert('VR viewer would initialize here')">
                                    <i class="fas fa-play me-2"></i>Enter VR Mode
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <?php foreach (($vr_data['scenes'] ?? []) as $scene_id => $scene): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-building fa-3x text-primary mb-3"></i>
                            <h5><?= ($scene['name'] ?? 'Scene') ?></h5>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-mouse-pointer me-1"></i><?= count($scene['hotspots'] ?? []) ?> hotspots
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($vr_data['scenes'] ?? [])): ?>
                <div class="col-12">
                    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No VR scenes available for this property yet.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Property Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <h4><?= ($property['title'] ?? 'Untitled Property') ?></h4>
                    <p class="text-muted"><i class="fas fa-map-marker-alt me-2"></i><?= ($property['location'] ?? $property['city'] ?? 'N/A') ?></p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Price</span>
                        <strong>₹<?= number_format($property['price'] ?? 0) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Area</span>
                        <strong><?= ($property['area_sqft'] ?? 'N/A') ?> sq.ft</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Type</span>
                        <strong><?= ucfirst($property['property_type'] ?? 'N/A') ?></strong>
                    </div>
                </div>
            </div>

            <?php if (!empty($vr_data['ar_objects']['furniture'] ?? [])): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-couch me-2 text-primary"></i>AR Objects</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach (($vr_data['ar_objects']['furniture'] ?? []) as $name => $obj): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <?= ucfirst($name) ?>
                            <span class="badge bg-primary rounded-pill"><i class="fas fa-cube"></i></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2 text-primary"></i>Lighting</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="me-2">Ambient:</span>
                        <span class="badge bg-secondary d-inline-block" style="width:30px;height:30px;background:<?= ($vr_data['lighting']['ambient'] ?? '#ffffff') ?> !important;"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Intensity</span>
                        <strong><?= ($vr_data['lighting']['intensity'] ?? '0.8') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Shadows</span>
                        <strong><?= ($vr_data['lighting']['shadows'] ?? false) ? 'Enabled' : 'Disabled' ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
