<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Virtual Development</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-cubes me-3 text-success"></i><?= ($page_title ?? 'Virtual Development') ?></h1>
        </div>
    </div>

    <?php $templates = $templates ?? []; $environments = $environments ?? []; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>Create Virtual Property</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?? BASE_URL ?>metaverse/virtual-development">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Property Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Oceanview Villa">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Property Type</label>
                                <select name="property_type" class="form-select" required>
                                    <option value="apartment">Apartment</option>
                                    <option value="villa">Villa</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="land">Land</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Describe your virtual property..." required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Area (sq.ft)</label>
                                <input type="number" name="area_sqft" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Location</label>
                                <input type="text" name="location" class="form-control" required placeholder="City/Region">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Base Price (VRC)</label>
                                <input type="number" name="base_price" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Virtual Template</label>
                                <select name="template" class="form-select">
                                    <option value="">Select template...</option>
                                    <?php foreach ($templates as $key => $template): ?>
                                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= ($template['name'] ?? $key) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Environment</label>
                                <select name="environment" class="form-select" required>
                                    <option value="">Select environment...</option>
                                    <?php foreach ($environments as $key => $env): ?>
                                    <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= ($env['name'] ?? $key) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-cube me-2"></i>Create Virtual Property</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-info"></i>Templates</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($templates as $key => $template): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0">
                            <i class="fas fa-drafting-compass fa-2x text-info"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1"><?= ($template['name'] ?? $key) ?></h6>
                            <small class="text-muted"><?= ($template['description'] ?? '') ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-globe me-2 text-info"></i>Environments</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach ($environments as $key => $env): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0">
                            <i class="fas fa-mountain fa-2x text-success"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1"><?= ($env['name'] ?? $key) ?></h6>
                            <small class="text-muted"><?= implode(', ', $env['ambient_sounds'] ?? []) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
