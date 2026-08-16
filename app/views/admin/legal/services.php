<?php
$page_title = $page_title ?? 'Legal Services';
$services = $services ?? [];
$total = $total ?? 0;
$active = $active ?? 0;
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Legal Services</h1>
            <p class="text-muted">Manage legal service offerings</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-gavel fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Services</h6>
                            <h3 class="mb-0"><?php echo $total; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0"><?php echo $active; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded p-3">
                                <i class="fas fa-pause-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Inactive</h6>
                            <h3 class="mb-0"><?php echo $total - $active; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="<?php echo BASE_URL; ?>/admin/legal/create-service" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                            <h6 class="mb-0 text-dark">Add New Service</h6>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php if (empty($services)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-gavel fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No legal services found</h5>
            <a href="<?php echo BASE_URL; ?>/admin/legal/create-service" class="btn btn-primary mt-2">Add First Service</a>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($services as $s): ?>
        <div class="col-xl-4 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                            <i class="fas <?php echo $s['icon'] ?? 'fa-gavel'; ?> fa-2x"></i>
                        </div>
                        <span class="badge bg-<?php echo ($s['status'] ?? '') === 'active' ? 'success' : 'secondary'; ?> rounded-pill px-3"><?php echo ucfirst($s['status'] ?? 'inactive'); ?></span>
                    </div>
                    <h5 class="card-title"><?php echo $s['title'] ?? ''; ?></h5>
                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($s['description'] ?? '', 0, 120)); ?></p>
                    <?php if (!empty($s['price_range'])): ?>
                    <p class="mb-1"><i class="fas fa-rupee-sign text-success me-1"></i> <strong><?php echo $s['price_range']; ?></strong></p>
                    <?php endif; ?>
                    <?php if (!empty($s['duration'])): ?>
                    <p class="mb-0"><i class="fas fa-clock text-info me-1"></i> <?php echo $s['duration']; ?></p>
                    <?php endif; ?>
                    <?php if (!empty($s['features'])): ?>
                    <hr>
                    <small class="text-muted"><?php echo nl2br(htmlspecialchars($s['features'] ?? '')); ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
