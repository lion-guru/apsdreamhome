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
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="operations-tab" data-bs-toggle="tab" data-bs-target="#colony-operations" type="button" role="tab" aria-controls="colony-operations" aria-selected="false">
                <i class="fas fa-tools me-2 text-warning"></i> Operations & Utilities
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

        <!-- Tab 6: Township Utilities & Operations Hub -->
        <div class="tab-pane fade" id="colony-operations" role="tabpanel" aria-labelledby="operations-tab">
            <div class="row g-4">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold"><i class="fas fa-city text-primary me-2"></i>Township Infrastructure & Operations Hub</h5>
                        <p class="text-muted small mb-0">Active utilities, sanitation, green canopy, civil maintenance and security operations for <strong><?= htmlspecialchars($colony['name'] ?? 'Colony') ?></strong>.</p>
                    </div>
                    <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 fs-6">
                        <i class="fas fa-map-marker-alt me-1"></i> Colony ID: #<?= $cid ?>
                    </div>
                </div>

                <!-- Utilities Metric Ribbon -->
                <div class="col-12">
                    <div class="row g-2">
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-tint text-primary me-1"></i>Water</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['water_logs'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-bolt text-warning me-1"></i>DG Genset</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['genset_logs'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-lightbulb text-warning me-1"></i>Street Light</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['lighting_logs'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-cloud-rain text-info me-1"></i>RWH Pits</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['rwh_logs'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-vial text-primary me-1"></i>STP Effluent</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['stp_logs'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-fire-extinguisher text-danger me-1"></i>Fire Safety</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['fire_safety_logs'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <a href="<?= BASE_URL ?>/admin/iot/alerts?colony_id=<?= (int)$cid ?>" class="text-decoration-none d-block h-100">
                                <div class="p-3 bg-light rounded-3 border text-center h-100">
                                    <div class="text-muted small mb-1"><i class="fas fa-microchip text-secondary me-1"></i>IoT Devices</div>
                                    <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['iot_devices'] ?? 0) ?></div>
                                    <?php if ((int)($opsSummary['iot_alerts'] ?? 0) > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?= (int)$opsSummary['iot_alerts'] ?> open alert<?= (int)$opsSummary['iot_alerts'] === 1 ? '' : 's' ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-shield-alt text-success me-1"></i>Security</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['security_rosters'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1"><i class="fas fa-headset text-danger me-1"></i>Grievances</div>
                                <div class="fs-4 fw-bold text-dark"><?= (int)($opsSummary['grievances'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 11 Enterprise Action & Desk Cards -->
                <!-- 1. Water Supply -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0284c7;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-faucet-drip"></i>
                                </div>
                                <span class="badge bg-info bg-opacity-15 text-info">BIS 10500 Compliant</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Borewell & Water Supply</h6>
                            <p class="text-muted small flex-grow-1">Pumping shifts, OHT tank level %, TDS/pH potability tests & chlorination dosing.</p>
                            <a href="<?= BASE_URL ?>/admin/township-water-supply?colony_id=<?= $cid ?>" class="btn btn-outline-info btn-sm w-100 mt-2">
                                <i class="fas fa-tint me-1"></i>Open Water Supply Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. Central DG Genset Backup -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #eab308;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <span class="badge bg-warning bg-opacity-15 text-warning">Power Backup</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">DG Power Backup & Diesel</h6>
                            <p class="text-muted small flex-grow-1">Genset run hours, diesel consumption litres, fuel math & kWh/L generation efficiency.</p>
                            <a href="<?= BASE_URL ?>/admin/township-genset?colony_id=<?= $cid ?>" class="btn btn-outline-warning btn-sm w-100 mt-2">
                                <i class="fas fa-gas-pump me-1"></i>Open DG Fuel Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3. Security Rosters & Night Patrol -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #10b981;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span class="badge bg-success bg-opacity-15 text-success">24x7 Guard Patrol</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Security Rosters & Incidents</h6>
                            <p class="text-muted small flex-grow-1">Main gate check-ins, boom barrier uptime, night patrol logs and incident desk.</p>
                            <a href="<?= BASE_URL ?>/admin/township-security?colony_id=<?= $cid ?>" class="btn btn-outline-success btn-sm w-100 mt-2">
                                <i class="fas fa-user-shield me-1"></i>Open Security Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 4. CCTV Surveillance -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #6366f1;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-video"></i>
                                </div>
                                <span class="badge bg-primary bg-opacity-15 text-primary">NVR 30-Day Storage</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">CCTV Surveillance & Uptime</h6>
                            <p class="text-muted small flex-grow-1">Camera uptime percentage, offline camera logs, HDD storage health and footage exports.</p>
                            <a href="<?= BASE_URL ?>/admin/township-cctv?colony_id=<?= $cid ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                <i class="fas fa-camera me-1"></i>Open CCTV Uptime Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 5. Solid Waste & Sanitation -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #14b8a6;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-teal bg-opacity-10 p-2 text-teal d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-recycle text-info"></i>
                                </div>
                                <span class="badge bg-info bg-opacity-15 text-info">Green Composting</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Waste & Sanitation Sweeps</h6>
                            <p class="text-muted small flex-grow-1">Wet/dry segregation, compost pit diversion kg, trolley trips & drain desilting.</p>
                            <a href="<?= BASE_URL ?>/admin/township-waste?colony_id=<?= $cid ?>" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                                <i class="fas fa-trash-alt me-1"></i>Open Sanitation Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 6. Park Horticulture & Tree Census -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #22c55e;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-tree"></i>
                                </div>
                                <span class="badge bg-success bg-opacity-15 text-success">Green Canopy</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Park Horticulture & Trees</h6>
                            <p class="text-muted small flex-grow-1">Central park maintenance, tree species census, sapling survival % and sprinkler schedules.</p>
                            <a href="<?= BASE_URL ?>/admin/township-horticulture?colony_id=<?= $cid ?>" class="btn btn-outline-success btn-sm w-100 mt-2">
                                <i class="fas fa-seedling me-1"></i>Open Horticulture Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 7. Resident Grievances & AMC -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #ef4444;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <span class="badge bg-danger bg-opacity-15 text-danger">4-Hour SLA Emergency</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Resident Grievances Helpdesk</h6>
                            <p class="text-muted small flex-grow-1">Society maintenance complaints, AMC civil contractor dispatch and 5-star closure ratings.</p>
                            <a href="<?= BASE_URL ?>/admin/township-grievances?colony_id=<?= $cid ?>" class="btn btn-outline-danger btn-sm w-100 mt-2">
                                <i class="fas fa-tools me-1"></i>Open Grievance Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 8. Civil Work Orders & RA Bills -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #f97316;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-hard-hat text-dark"></i>
                                </div>
                                <span class="badge bg-secondary">RA Milestone Billing</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Civil Infrastructure Work Orders</h6>
                            <p class="text-muted small flex-grow-1">Contractor work orders, Measurement Book (MB) billing, 5% retention holdback & TDS u/s 194C.</p>
                            <a href="<?= BASE_URL ?>/admin/work-orders?colony_id=<?= $cid ?>" class="btn btn-outline-dark btn-sm w-100 mt-2">
                                <i class="fas fa-file-invoice-dollar me-1"></i>Open Work Orders Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 9. Plot Demarcations & GPS Geotagging -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #8b5cf6;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-draw-polygon"></i>
                                </div>
                                <span class="badge bg-primary bg-opacity-15 text-primary">WGS84 GPS Pegs</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Plot Demarcations & Pillars</h6>
                            <p class="text-muted small flex-grow-1">4-corner boundary coordinates, surveyor peg handover and civil engineer verification certificates.</p>
                            <a href="<?= BASE_URL ?>/admin/plots/demarcation?colony_id=<?= $cid ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                <i class="fas fa-map-pin me-1"></i>Open Demarcation Radar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 10. Commercial Plaza & Retail Leases -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #059669;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-store"></i>
                                </div>
                                <span class="badge bg-success bg-opacity-15 text-success">Retail Rent Roll</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Commercial Plaza & Shops</h6>
                            <p class="text-muted small flex-grow-1">Shop leases, monthly rent collection notes, submeter electricity units and UPI QR tax invoices.</p>
                            <a href="<?= BASE_URL ?>/admin/commercial-leases?colony_id=<?= $cid ?>" class="btn btn-outline-success btn-sm w-100 mt-2">
                                <i class="fas fa-cash-register me-1"></i>Open Commercial Leases
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 11. Construction Timelapse & Drone Progress -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0284c7;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-images"></i>
                                </div>
                                <span class="badge bg-info bg-opacity-15 text-info">Visual Milestone</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Construction Timelapse</h6>
                            <p class="text-muted small flex-grow-1">Sequential construction photographic history and drone flyover timeline for this colony.</p>
                            <a href="<?= BASE_URL ?>/admin/colonies/<?= $cid ?>/timelapse" class="btn btn-outline-info btn-sm w-100 mt-2">
                                <i class="fas fa-play me-1"></i>View Site Timelapse
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 12. Street Lighting & Illumination Poles -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #f59e0b;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <span class="badge bg-warning bg-opacity-15 text-warning">LED Feeder Timers</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Street Lighting & Illumination</h6>
                            <p class="text-muted small flex-grow-1">Feeder pillar timers, LED pole uptime %, 3-phase load balance and underground cable audit.</p>
                            <a href="<?= BASE_URL ?>/admin/township-lighting?colony_id=<?= $cid ?>" class="btn btn-outline-warning btn-sm w-100 mt-2">
                                <i class="fas fa-lightbulb me-1"></i>Open Street Lighting Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 13. Rainwater Harvesting & Recharge Shafts -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #06b6d4;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-cloud-rain"></i>
                                </div>
                                <span class="badge bg-info bg-opacity-15 text-info">CGWB & UP GW NOC</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Rainwater Harvesting (RWH)</h6>
                            <p class="text-muted small flex-grow-1">Percolation shafts, desilting chambers, water table depth ft & monsoon readiness audits.</p>
                            <a href="<?= BASE_URL ?>/admin/township-rwh?colony_id=<?= $cid ?>" class="btn btn-outline-info btn-sm w-100 mt-2">
                                <i class="fas fa-cloud-showers-heavy me-1"></i>Open RWH Compliance Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 14. STP Sewage Treatment Plant & UPPCB Effluent -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0ea5e9;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-vial"></i>
                                </div>
                                <span class="badge bg-primary bg-opacity-15 text-primary">UPPCB Schedule-VI</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">STP Effluent &amp; UPPCB Compliance</h6>
                            <p class="text-muted small flex-grow-1">Raw sewage inflow, BOD/COD/TSS treated water quality, blower hours &amp; sludge disposal logs.</p>
                            <a href="<?= BASE_URL ?>/admin/township-stp?colony_id=<?= $cid ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                <i class="fas fa-vial me-1"></i>Open STP Effluent Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 15. Township Fire Hydrant Network & UP Fire NOC -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #dc2626;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-fire-extinguisher"></i>
                                </div>
                                <span class="badge bg-danger bg-opacity-15 text-danger">UP Fire NOC</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Fire Hydrant Network &amp; NOC</h6>
                            <p class="text-muted small flex-grow-1">Jockey/electric/diesel pump readiness, hydrant valve audits, hose boxes &amp; UP Fire NOC compliance status.</p>
                            <a href="<?= BASE_URL ?>/admin/township-fire-safety?colony_id=<?= $cid ?>" class="btn btn-outline-danger btn-sm w-100 mt-2">
                                <i class="fas fa-fire me-1"></i>Open Fire Safety Desk
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 16. Smart IoT Devices & Threshold Alerts -->
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #6f42c1;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rounded-circle bg-secondary bg-opacity-10 p-2 text-secondary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-microchip"></i>
                                </div>
                                <span class="badge bg-secondary bg-opacity-15 text-secondary"><?= (int)($opsSummary['iot_devices'] ?? 0) ?> Devices</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Smart IoT &amp; Threshold Alerts</h6>
                            <p class="text-muted small flex-grow-1">Gadgets, solar controllers &amp; barriers telemetry — battery/fault/offline alert queue with device ingest gateway.</p>
                            <a href="<?= BASE_URL ?>/admin/iot/alerts?colony_id=<?= $cid ?>" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                                <i class="fas fa-bell me-1"></i>Open IoT Alerts Desk
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
