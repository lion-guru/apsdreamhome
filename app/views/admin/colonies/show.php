<?php
$colony = $colony ?? [];
$plots = $plots ?? [];
$layout = $layout ?? null;
$cid = (int)($colony['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fas fa-eye text-info me-2"></i><?php echo htmlspecialchars($colony['name'] ?? 'Colony Details'); ?></h4>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo BASE_URL; ?>/admin/colony-pipeline/<?php echo $cid; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-sitemap me-1"></i>Pipeline</a>
            <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $cid; ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>" class="btn btn-success btn-sm" target="_blank"><i class="fas fa-external-link-alt me-1"></i>View Public Page</a>
        </div>
    </div>

    <!-- Colony 360° Tabs: Overview | Inventory | Plots | Pipeline | Finance -->
    <ul class="nav nav-tabs nav-tabs-custom" id="colony360Tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#colony-overview" type="button" role="tab" aria-controls="colony-overview" aria-selected="true">
                <i class="fas fa-eye me-2"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#colony-inventory" type="button" role="tab" aria-controls="colony-inventory" aria-selected="false">
                <i class="fas fa-database me-2"></i> Inventory
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="plots-tab" data-bs-toggle="tab" data-bs-target="#colony-plots" type="button" role="tab" aria-controls="colony-plots" aria-selected="false">
                <i class="fas fa-th-large me-2"></i> Plots
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pipeline-tab" data-bs-toggle="tab" data-bs-target="#colony-pipeline" type="button" role="tab" aria-controls="colony-pipeline" aria-selected="false">
                <i class="fas fa-sitemap me-2"></i> Pipeline
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="finance-tab" data-bs-toggle="tab" data-bs-target="#colony-finance" type="button" role="tab" aria-controls="colony-finance" aria-selected="false">
                <i class="fas fa-rupee-sign me-2"></i> Finance
            </button>
        </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content pt-3" id="colony360TabContent">

        <!-- Tab 1: Colony Overview -->
        <div class="tab-pane fade show active" id="colony-overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h6 class="mb-0">Colony Information</h6></div>
                        <div class="card-body aps-cp-card-body">
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($colony['description'] ?? 'No description')) ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars(($colony['district_name'] ?? '') . ', ' . ($colony['state_name'] ?? '')) ?></p>
                            <p><strong>Total Area:</strong> <?= htmlspecialchars($colony['total_area_acres'] ?? '') !== '' ? htmlspecialchars($colony['total_area_acres']) . ' acres' : 'Not specified' ?></p>
                            <p><strong>Total Plots:</strong> <?= (int)($colony['total_plots'] ?? 0) ?></p>
                            <p><strong>RERA:</strong> <?= htmlspecialchars($colony['rera_number'] ?? 'Pending') ?></p>
                            <p><strong>Pipeline Stage:</strong> <span class="badge bg-info"><?= htmlspecialchars(ucfirst($colony['pipeline_stage'] ?? 'planning')) ?></span></p>
                            <p><strong>Status:</strong> <span class="badge bg-<?= ($colony['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($colony['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h6 class="mb-0">Land & Pricing</h6></div>
                        <div class="card-body aps-cp-card-body">
                            <p><strong>Land Owner:</strong> <?= htmlspecialchars($colony['land_owner_name'] ?? 'Not specified') ?></p>
                            <p><strong>Land Cost:</strong> ₹<?= number_format((float)($colony['land_cost'] ?? 0)) ?></p>
                            <p><strong>Min Price/sqft:</strong> ₹<?= number_format((float)($colony['min_price_per_sqft'] ?? 0), 2) ?></p>
                            <p><strong>Phase:</strong> <?= htmlspecialchars($colony['phase'] ?? 'Phase 1') ?></p>
                            <p><strong>Blocks:</strong> <?= (int)($colony['block_count'] ?? 0) ?></p>
                            <p><strong>Starting Price:</strong> ₹<?= number_format((float)($colony['starting_price'] ?? 0)) ?></p>
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
                            <h3 class="fw-bold text-success"><?= (int)($colony['available_plots'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-dark"><h6 class="mb-0">Booked</h6></div>
                        <div class="card-body text-center">
                            <h3 class="fw-bold text-warning"><?= (int)($colony['booked_plots'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-info text-white"><h6 class="mb-0">Registered</h6></div>
                        <div class="card-body text-center">
                            <h3 class="fw-bold text-info"><?= (int)($colony['registered_plots'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <small class="text-muted">
                        <a href="<?= BASE_URL ?>/admin/plots?colony_id=<?= $cid ?>" class="text-primary">
                            <i class="fas fa-filter me-1"></i> Filter Plots by Status
                        </a>
                    </small>
                </div>
            </div>
        </div>

        <!-- Tab 3: Plots -->
        <div class="tab-pane fade" id="colony-plots" role="tabpanel" aria-labelledby="plots-tab">
            <div class="row g-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-th-large me-2"></i> Plots (<?= count($plots) ?>)</h5>
                    <a href="<?= BASE_URL ?>/admin/colonies/<?= $cid ?>/plots" class="btn btn-outline-primary btn-sm"><i class="fas fa-expand me-1"></i>Full Plot List</a>
                </div>
                <div class="col-12">
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
                                <?php foreach (array_slice($plots, 0, 25) as $p): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/plots/<?= (int)($p['id'] ?? 0) ?>" class="text-primary">
                                            <?= htmlspecialchars($p['plot_number'] ?? 'N/A') ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($p['block'] ?? '-') ?></td>
                                    <td><?= number_format((float)($p['area_sqft'] ?? 0)) ?></td>
                                    <td>₹<?= number_format((float)($p['total_price'] ?? 0)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($p['status'] ?? '') === 'available' ? 'success' : (($p['status'] ?? '') === 'booked' ? 'warning' : (($p['status'] ?? '') === 'registered' ? 'info' : 'danger')) ?> fs-6">
                                            <?= htmlspecialchars(ucfirst($p['status'] ?? '')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/plots/<?= (int)($p['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($plots) > 25): ?>
                    <div class="mt-2"><small class="text-muted">Showing 25 of <?= count($plots) ?> plots.</small></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Pipeline (deep links into ColonyPipelineController) -->
        <div class="tab-pane fade" id="colony-pipeline" role="tabpanel" aria-labelledby="pipeline-tab">
            <div class="row g-4">
                <div class="col-12">
                    <h5><i class="fas fa-sitemap me-2"></i> Development Pipeline</h5>
                    <p class="text-muted small mb-0">Layout, pricing, costs and maps are managed in the colony pipeline. Current status is summarized below.</p>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white"><h6 class="mb-0">Current Layout</h6></div>
                        <div class="card-body">
                            <?php if (!empty($layout)): ?>
                                <p><strong><?= htmlspecialchars($layout['layout_name'] ?? 'Layout') ?></strong>
                                <span class="badge bg-<?= (($layout['status'] ?? '') === 'approved' ? 'success' : 'secondary') ?> ms-1"><?= htmlspecialchars(ucfirst($layout['status'] ?? '')) ?></span></p>
                                <p class="mb-1"><strong>Version:</strong> <?= htmlspecialchars($layout['version'] ?? '-') ?></p>
                                <p class="mb-1"><strong>Total Plots:</strong> <?= (int)($layout['total_plots'] ?? 0) ?></p>
                                <p class="mb-0"><strong>Total Area:</strong> <?= number_format((float)($layout['total_area_sqft'] ?? 0)) ?> sqft</p>
                            <?php else: ?>
                                <p class="text-muted mb-0">No approved layout recorded for this colony yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white"><h6 class="mb-0">Pipeline Actions</h6></div>
                        <div class="card-body d-grid gap-2">
                            <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-sitemap me-1"></i>Pipeline Dashboard</a>
                            <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/layout" class="btn btn-outline-secondary btn-sm"><i class="fas fa-drafting-compass me-1"></i>Layout & Plot Cutting</a>
                            <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/pricing" class="btn btn-outline-secondary btn-sm"><i class="fas fa-tag me-1"></i>Pricing Dashboard</a>
                            <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/costs" class="btn btn-outline-secondary btn-sm"><i class="fas fa-coins me-1"></i>Development Costs</a>
                            <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/map" class="btn btn-outline-secondary btn-sm"><i class="fas fa-map me-1"></i>Plot Map</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 5: Financial Summary -->
        <div class="tab-pane fade" id="colony-finance" role="tabpanel" aria-labelledby="finance-tab">
            <div class="row g-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-rupee-sign me-2"></i> Financial Summary</h5>
                    <a href="<?= BASE_URL ?>/admin/colonies/<?= $cid ?>/financials" class="btn btn-outline-primary btn-sm"><i class="fas fa-expand me-1"></i>Full Summary</a>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white"><h6 class="mb-0">Project Valuation</h6></div>
                        <div class="card-body text-center">
                            <h3 class="fw-bold">₹<?= number_format(array_sum(array_map(function ($p) { return (float)($p['total_price'] ?? 0); }, $plots)), 2) ?></h3>
                            <p class="text-muted">Total Value of All Plots</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white"><h6 class="mb-0">Realized Revenue (est.)</h6></div>
                        <div class="card-body text-center">
                            <h3 class="fw-bold text-success">₹<?= number_format(array_sum(array_map(function ($p) { return (float)($p['total_price'] ?? 0); }, $plots)) * 0.7, 2) ?></h3>
                            <p class="text-muted">Estimated 70% realized (based on booking rate)</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <small class="text-muted">
                        <a href="<?= BASE_URL ?>/admin/bookings?colony_filter=<?= $cid ?>" class="text-primary">
                            <i class="fas fa-list me-1"></i> View All Bookings for This Colony
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
