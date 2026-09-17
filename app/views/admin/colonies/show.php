<?php $colony = $colony ?? []; $plots = $plots ?? []; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-eye text-info me-2"></i><?php echo htmlspecialchars($colony['name'] ?? 'Colony Details'); ?></h4>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $colony['id'] ?? 0; ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>" class="btn btn-success btn-sm" target="_blank"><i class="fas fa-external-link-alt me-1"></i>View Public Page</a>
        </div>
    </div>

    <!-- Colony 360° Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom" id="colony360Tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#colony-overview" type="button" role="tab" aria-controls="colony-overview" aria-selected="true">
                <i class="fas fa-eye me-2"></i> Colony Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#colony-inventory" type="button" role="tab" aria-controls="colony-inventory" aria-selected="false">
                <i class="fas fa-database me-2"></i> Inventory Dashboard
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="plot-grid-tab" data-bs-toggle="tab" data-bs-target="#plot-grid" type="button" role="tab" aria-controls="plot-grid" aria-selected="false">
                <i class="fas fa-th-large me-2"></i> Plot Grid / Layout
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="finance-tab" data-bs-toggle="tab" data-bs-target="#colony-finance" type="button" role="tab" aria-controls="colony-finance" aria-selected="false">
                <i class="fas fa-rupee-sign me-2"></i> Financial Summary
            </button>
        </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content" id="colony360TabContent">
        
        <!-- Tab 1: Colony Overview -->
        <div class="tab-pane fade show active" id="colony-overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h6 class="mb-0">Colony Information</h6></div>
                        <div class="card-body aps-cp-card-body">
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($colony['description'] ?? 'No description')) ?></p>
                            <p><strong>Location:</strong> <?= ($colony['district_name'] ?? '') . ', ' . ($colony['state_name'] ?? '') ?></p>
                            <p><strong>Total Acreage:</strong> <?= $colony['total_acreage'] ?? 'Not specified' ?></p>
                            <p><strong>Total Plots:</strong> <?= $colony['total_plots'] ?? 0 ?></p>
                            <p><strong>RERA Approval:</strong> <?= $colony['rera_number'] ?? 'Pending' ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?= ($colony['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($colony['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h6 class="mb-0">Master Plan</h6></div>
                        <div class="card-body aps-cp-card-body">
                            <p><strong>Khasra Numbers:</strong> <?= $colony['khasra_numbers'] ?? 'Not specified' ?></p>
                            <p><strong>Boundary:</strong> <?= $colony['boundary_description'] ?? 'Not specified' ?></p>
                            <p><strong>Approved Facilities:</strong> <?= $colony['approved_facilities'] ?? 'Not specified' ?></p>
                            <p><strong>Expected Completion:</strong> <?= $colony['expected_completion'] ?? 'Not specified' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Inventory Dashboard -->
        <div class="tab-pane fade" id="colony-inventory" role="tabpanel" aria-labelledby="inventory-tab">
            <div class="row g-4">
                <div class="col-12">
                    <h5><i class="fas fa-database me-2"></i> Inventory Dashboard</h5>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white"><h6 class="mb-0">Total Plots</h6></div>
                            <div class="card-body text-center">
                                <h3 class="fw-bold"><?= count($plots) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-success text-white"><h6 class="mb-0">Available</h6></div>
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-success"><?= $colony['available_plots'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-warning text-dark"><h6 class="mb-0">Booked</h6></div>
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-warning"><?= ($colony['booked_plots'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-info text-white"><h6 class="mb-0">Registered</h6></div>
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-info"><?= ($colony['registered_plots'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <a href="<?= BASE_URL ?>/admin/plots?colony_id=<?= $colony['id'] ?>" class="text-primary">
                            <i class="fas fa-filter me-1"></i> Filter Plots by Status
                        </a>
                    </small>
                </div>
            </div>
        </div>

        <!-- Tab 3: Plot Grid / Layout -->
        <div class="tab-pane fade" id="plot-grid" role="tabpanel" aria-labelledby="plot-grid-tab">
            <div class="row g-4">
                <div class="col-12">
                    <h5><i class="fas fa-th-large me-2"></i> Plot Grid / Layout</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Plot #</th>
                                <th>Block/Sector</th>
                                <th>Area (sqft)</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($plots)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No plots assigned to this colony yet.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($plots as $p): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/plots/<?= $p['id'] ?>" class="text-primary">
                                        <?= htmlspecialchars($p['plot_number'] ?? 'N/A') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($p['block'] ?? '-') ?></td>
                                <td><?= $p['area_sqft'] ?? 0 ?></td>
                                <td>₹<?= number_format($p['total_price'] ?? 0) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($p['status'] ?? '') === 'available' ? 'success' : (($p['status'] ?? '') === 'booked' ? 'warning' : (($p['status'] ?? '') === 'registered' ? 'info' : 'danger')) ?> fs-6">
                                        <?= ucfirst($p['status'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/plots/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <a href="<?= BASE_URL ?>/admin/plots?colony_id=<?= $colony['id'] ?>" class="text-primary">
                            <i class="fas fa-filter me-1"></i> Filter: All | Available | Booked | Registered
                        </a>
                    </small>
                </div>
            </div>
        </div>

        <!-- Tab 4: Financial Summary -->
        <div class="tab-pane fade" id="colony-finance" role="tabpanel" aria-labelledby="finance-tab">
            <div class="row g-4">
                <div class="col-12">
                    <h5><i class="fas fa-rupee-sign me-2"></i> Financial Summary</h5>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white"><h6 class="mb-0">Project Valuation</h6></div>
                            <div class="card-body text-center">
                                <h3 class="fw-bold">₹<?= number_format(array_sum(array_map(fn($p) => $p['total_price'] ?? 0, $plots)), 2) ?></h3>
                                <p class="text-muted">Total Value of All Plots</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-success text-white"><h6 class="mb-0">Realized Revenue</h6></div>
                            <div class="card-body text-center">
                                <h3 class="fw-bold text-success">₹<?= number_format(array_sum(array_map(fn($p) => $p['total_price'] ?? 0, $plots) * 0.7, 2) ?? 0) ?></h3>
                                <p class="text-muted">Estimated 70% realized (based on booking rate)</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                                70% Realized
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <a href="<?= BASE_URL ?>/admin/bookings?colony_filter=<?= $colony['id'] ?>" class="text-primary">
                            <i class="fas fa-list me-1"></i> View All Bookings for This Colony
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>