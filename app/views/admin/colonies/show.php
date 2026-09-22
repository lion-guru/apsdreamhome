<?php $colony = $colony ?? []; $plots = $plots ?? []; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-eye text-info me-2"></i><?php echo htmlspecialchars($colony['name'] ?? 'Colony Details'); ?></h4>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $colony['id'] ?? 0; ?>/edit" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>" class="btn btn-success btn-sm" target="_blank"><i class="fas fa-external-link-alt me-1"></i>View Public Page</a>
</div>
        </div>

    </div>
</div>

<!-- Add Milestone Modal -->
<div class="modal fade" id="addMilestoneModal" tabindex="-1" aria-labelledby="addMilestoneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMilestoneModalLabel"><i class="fas fa-plus me-2"></i>Add Milestone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/colonies/<?= $colony['id'] ?>/pipeline/milestones">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <input type="number" name="level" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Land Acquisition Complete">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" name="target_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Milestone</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Colony 360° Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom" id="colony360Tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#colony-overview" type="button" role="tab" aria-controls="colony-overview" aria-selected="true">
                <i class="fas fa-eye me-2"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="development-tab" data-bs-toggle="tab" data-bs-target="#colony-development" type="button" role="tab" aria-controls="colony-development" aria-selected="false">
                <i class="fas fa-sitemap me-2"></i> Development
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#colony-pricing" type="button" role="tab" aria-controls="colony-pricing" aria-selected="false">
                <i class="fas fa-tag me-2"></i> Pricing
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="plots-tab" data-bs-toggle="tab" data-bs-target="#colony-plots" type="button" role="tab" aria-controls="colony-plots" aria-selected="false">
                <i class="fas fa-th-large me-2"></i> Plots
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="milestones-tab" data-bs-toggle="tab" data-bs-target="#colony-milestones" type="button" role="tab" aria-controls="colony-milestones" aria-selected="false">
                <i class="fas fa-flag me-2"></i> Milestones
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="finance-tab" data-bs-toggle="tab" data-bs-target="#colony-finance" type="button" role="tab" aria-controls="colony-finance" aria-selected="false">
                <i class="fas fa-rupee-sign me-2"></i> Finance
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

        <!-- Tab 2: Development -->
        <div class="tab-pane fade" id="colony-development" role="tabpanel" aria-labelledby="development-tab">
            <div class="row g-4">
                <div class="col-12">
                    <h5><i class="fas fa-sitemap me-2"></i> Development Layout</h5>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h6 class="mb-0">Layout Configuration</h6></div>
                        <div class="card-body">
                            <?php if (!empty($layoutConfig)): ?>
                                <pre class="bg-light p-3 rounded"><?= json_encode($layoutConfig, JSON_PRETTY_PRINT) ?></pre>
                            <?php else: ?>
                                <p class="text-muted">No layout configuration saved.</p>
                                <a href="<?= BASE_URL ?>/admin/colonies/<?= $colony['id'] ?>/pipeline/layout" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Configure Layout
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h6 class="mb-0">Development Costs</h6></div>
                        <div class="card-body">
                            <p><strong>Total Milestones:</strong> <?= $developmentCosts->total ?? 0 ?></p>
                            <p><strong>Completed:</strong> <?= $developmentCosts->completed ?? 0 ?></p>
                            <p><strong>Total Development Cost:</strong> ₹<?= number_format($colony['development_cost'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Pricing -->
        <div class="tab-pane fade" id="colony-pricing" role="tabpanel" aria-labelledby="pricing-tab">
            <div class="row g-4">
                <div class="col-12">
                    <h5><i class="fas fa-tag me-2"></i> Price Slabs</h5>
                </div>
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Rank-wise Pricing</h6>
                            <a href="<?= BASE_URL ?>/admin/colonies/<?= $colony['id'] ?>/pipeline/pricing" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Add Price Slab
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($priceSlabs)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Rank</th>
                                                <th>Rate</th>
                                                <th>Min Value</th>
                                                <th>Max Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($priceSlabs as $rank => $slab): ?>
                                                <tr>
                                                    <td><span class="badge bg-primary"><?= htmlspecialchars($rank) ?></span></td>
                                                    <td><?= $slab->rate ?? 0 ?>%</td>
                                                    <td><?= $slab->min_value ?? '-' ?></td>
                                                    <td><?= $slab->max_value ?? '-' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No price slabs configured.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 5: Milestones -->
        <div class="tab-pane fade" id="colony-milestones" role="tabpanel" aria-labelledby="milestones-tab">
            <div class="row g-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-flag me-2"></i> Milestones</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMilestoneModal">
                        <i class="fas fa-plus me-1"></i>Add Milestone
                    </button>
                </div>
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <?php if (!empty($milestones)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Level</th>
                                                <th>Name</th>
                                                <th>Target Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($milestones as $m): ?>
                                                <tr>
                                                    <td><?= $m['level'] ?? $m->level ?? 0 ?></td>
                                                    <td><?= htmlspecialchars($m['name'] ?? $m->name ?? '') ?></td>
                                                    <td><?= $m['target_date'] ?? $m->target_date ?? '-' ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= 
                                                            ($m['status'] ?? $m->status ?? '') === 'completed' ? 'success' : 
                                                            (($m['status'] ?? $m->status ?? '') === 'in_progress' ? 'warning' : 'secondary') ?>">
                                                            <?= ucfirst($m['status'] ?? $m->status ?? 'pending') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?= BASE_URL ?>/admin/colonies/<?= $colony['id'] ?>/pipeline/milestones/delete/<?= $m['id'] ?? $m->id ?>"
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Delete this milestone?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No milestones added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>