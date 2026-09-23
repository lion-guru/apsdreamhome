<?php
$project    = $project ?? [];
$amenities  = $amenities ?? [];
$images     = $images ?? [];
$milestones = $milestones ?? [];
$pName      = htmlspecialchars($project['name'] ?? 'Project');
$pId        = (int)($project['id'] ?? 0);
$progress   = (int)($project['progress_pct'] ?? 0);
$statusMap  = ['planning' => 'info', 'under_construction' => 'warning', 'completed' => 'success', 'delayed' => 'danger', 'cancelled' => 'secondary'];
$stColor    = $statusMap[$project['status'] ?? 'planning'] ?? 'secondary';
$stLabel    = ucwords(str_replace('_', ' ', $project['status'] ?? 'planning'));
$budget     = (float)($project['project_budget'] ?? 0);
$spent      = (float)($project['amount_spent'] ?? 0);
$remaining  = max(0, $budget - $spent);
$budgetPct  = $budget > 0 ? min(100, round(($spent / $budget) * 100)) : 0;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold">
            <i class="fas fa-building text-primary me-2"></i><?= $pName ?>
            <span class="badge bg-<?= $stColor ?> ms-2 fs-6"><?= $stLabel ?></span>
            <?php if (!empty($project['rera_number'])): ?>
                <span class="badge bg-success ms-1 fs-6"><i class="fas fa-certificate me-1"></i>RERA: <?= htmlspecialchars($project['rera_number']) ?></span>
            <?php endif; ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/projects">Projects</a></li>
                <li class="breadcrumb-item active"><?= $pName ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (!empty($project['colony_id'])): ?>
        <a href="<?= BASE_URL ?>/admin/colonies/<?= (int)$project['colony_id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-city me-1"></i>View Colony
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/projects/progress/<?= $pId ?>" class="btn btn-outline-info btn-sm">
            <i class="fas fa-tasks me-1"></i>Progress Tracker
        </a>
        <a href="<?= BASE_URL ?>/admin/plots?colony_id=<?= (int)($project['colony_id'] ?? 0) ?>" class="btn btn-outline-success btn-sm">
            <i class="fas fa-map me-1"></i>View Plots
        </a>
        <a href="<?= BASE_URL ?>/admin/projects/edit/<?= $pId ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="<?= BASE_URL ?>/projects/<?= htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project['name'] ?? ''))) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-external-link-alt me-1"></i>Public View
        </a>
        <a href="<?= BASE_URL ?>/admin/projects" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Progress Banner -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px; border-top: 4px solid #0d6efd;">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold text-dark">Construction Progress</span>
            <span class="fw-bold text-primary fs-5"><?= $progress ?>% Complete</span>
        </div>
        <div class="progress" style="height:12px; border-radius:8px;">
            <div class="progress-bar bg-<?= $progress >= 100 ? 'success' : ($progress >= 50 ? 'info' : 'warning') ?>"
                 role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <div class="row mt-3 g-2 text-center">
            <div class="col"><small class="text-muted d-block">Launch</small><strong><?= $project['launch_date'] ? date('d M Y', strtotime($project['launch_date'])) : '—' ?></strong></div>
            <div class="col"><small class="text-muted d-block">Completion</small><strong><?= $project['completion_date'] ? date('d M Y', strtotime($project['completion_date'])) : '—' ?></strong></div>
            <div class="col"><small class="text-muted d-block">Possession</small><strong><?= $project['possession_date'] ? date('d M Y', strtotime($project['possession_date'])) : '—' ?></strong></div>
            <div class="col"><small class="text-muted d-block">Manager</small><strong><?= htmlspecialchars($project['project_manager'] ?? '—') ?></strong></div>
            <div class="col"><small class="text-muted d-block">Supervisor</small><strong><?= htmlspecialchars($project['site_supervisor'] ?? '—') ?></strong></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Main details -->
    <div class="col-lg-8">
        <!-- Plot Inventory Stats -->
        <div class="row g-3 mb-4">
            <?php
            $invCards = [
                ['label' => 'Total Units',     'value' => (int)($project['total_units'] ?? 0),     'color' => 'primary',   'icon' => 'fa-map'],
                ['label' => 'Available',        'value' => (int)($project['available_units'] ?? 0),  'color' => 'success',   'icon' => 'fa-check-circle'],
                ['label' => 'Sold',             'value' => (int)($project['sold_units'] ?? 0),       'color' => 'danger',    'icon' => 'fa-receipt'],
                ['label' => 'Booked',           'value' => (int)($project['booked_units'] ?? 0),     'color' => 'warning',   'icon' => 'fa-bookmark'],
            ];
            foreach ($invCards as $card): ?>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius:10px; border-top:3px solid var(--bs-<?= $card['color'] ?>);">
                    <div class="card-body text-center py-3">
                        <div class="rounded-circle bg-<?= $card['color'] ?> bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px;">
                            <i class="fas <?= $card['icon'] ?> text-<?= $card['color'] ?>"></i>
                        </div>
                        <div class="fw-bold fs-4 text-dark"><?= number_format($card['value']) ?></div>
                        <div class="text-muted small"><?= $card['label'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Project Details Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Project Details</h6>
            </div>
            <div class="card-body px-4">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted d-block">Project Type</small><strong><?= ucfirst($project['project_type'] ?? '') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Developer</small><strong><?= htmlspecialchars($project['developer_name'] ?? '—') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Developer Phone</small><strong><?= htmlspecialchars($project['developer_phone'] ?? '—') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Colony</small>
                        <?php if (!empty($project['colony_name'])): ?>
                            <a href="<?= BASE_URL ?>/admin/colonies/<?= (int)($project['colony_id'] ?? 0) ?>" class="fw-bold text-primary text-decoration-none">
                                <i class="fas fa-city me-1"></i><?= htmlspecialchars($project['colony_name']) ?>
                            </a>
                        <?php else: ?><strong>—</strong><?php endif; ?>
                    </div>
                    <div class="col-md-4"><small class="text-muted d-block">District</small><strong><?= htmlspecialchars($project['district_name'] ?? '—') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">State</small><strong><?= htmlspecialchars($project['state_name'] ?? '—') ?></strong></div>
                    <div class="col-12"><small class="text-muted d-block">Address</small><strong><?= nl2br(htmlspecialchars($project['address'] ?? '—')) ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Total Area</small><strong><?= number_format((float)($project['total_area'] ?? 0)) ?> sq ft</strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Price Range</small><strong>₹<?= number_format((float)($project['price_range_min'] ?? 0) / 100000, 1) ?>L – ₹<?= number_format((float)($project['price_range_max'] ?? 0) / 100000, 1) ?>L</strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Avg ₹/sqft</small><strong>₹<?= number_format((float)($project['avg_price_per_sqft'] ?? 0)) ?></strong></div>
                    <?php if (!empty($project['contractor_name'])): ?>
                    <div class="col-md-6"><small class="text-muted d-block">Contractor</small><strong><?= htmlspecialchars($project['contractor_name']) ?></strong></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($project['description'])): ?>
                <hr class="my-3">
                <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($project['marketing_description'])): ?>
                <hr class="my-3">
                <p class="mb-0"><?= nl2br(htmlspecialchars($project['marketing_description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Milestones -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-flag text-warning me-2"></i>Milestones</h6>
                <a href="<?= BASE_URL ?>/admin/projects/progress/<?= $pId ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Manage
                </a>
            </div>
            <div class="card-body px-4">
                <?php if (empty($milestones)): ?>
                    <p class="text-muted text-center py-3 mb-0">No milestones added yet. <a href="<?= BASE_URL ?>/admin/projects/progress/<?= $pId ?>">Add milestones →</a></p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($milestones as $ms): $msColor = ($ms['status'] ?? '') === 'completed' ? 'success' : (($ms['status'] ?? '') === 'in_progress' ? 'warning' : 'secondary'); ?>
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 border-bottom">
                            <span><i class="fas fa-circle text-<?= $msColor ?> me-2" style="font-size:8px;vertical-align:middle;"></i><?= htmlspecialchars($ms['title'] ?? '') ?></span>
                            <div>
                                <span class="badge bg-<?= $msColor ?>"><?= ucfirst(str_replace('_', ' ', $ms['status'] ?? 'pending')) ?></span>
                                <?php if (!empty($ms['date'])): ?><small class="text-muted ms-2"><?= date('d M Y', strtotime($ms['date'])) ?></small><?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Amenities -->
        <?php if (!empty($amenities)): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-star text-success me-2"></i>Amenities</h6>
            </div>
            <div class="card-body px-4">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($amenities as $a): ?>
                    <span class="badge bg-light text-dark border px-3 py-2"><?= htmlspecialchars($a) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Sidebar -->
    <div class="col-lg-4">
        <!-- Budget Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-rupee-sign text-warning me-2"></i>Budget Tracking</h6>
            </div>
            <div class="card-body px-4">
                <?php if ($budget > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><small>Spent</small><small><?= $budgetPct ?>%</small></div>
                    <div class="progress" style="height:8px;border-radius:6px;">
                        <div class="progress-bar bg-<?= $budgetPct >= 90 ? 'danger' : ($budgetPct >= 70 ? 'warning' : 'success') ?>" style="width:<?= $budgetPct ?>%;"></div>
                    </div>
                </div>
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted fw-normal">Budget</th><td class="text-end fw-bold">₹<?= number_format($budget, 0) ?></td></tr>
                    <tr><th class="text-muted fw-normal">Spent</th><td class="text-end fw-bold text-<?= $spent > $budget ? 'danger' : 'dark' ?>">₹<?= number_format($spent, 0) ?></td></tr>
                    <tr><th class="text-muted fw-normal">Remaining</th><td class="text-end fw-bold text-success">₹<?= number_format($remaining, 0) ?></td></tr>
                </table>
                <?php else: ?>
                    <p class="text-muted text-center py-2 mb-0 small">Budget not set. <a href="<?= BASE_URL ?>/admin/projects/progress/<?= $pId ?>">Set budget →</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Risk Flags -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Risk Flags</h6>
            </div>
            <div class="card-body px-4">
                <?php $riskFlags = trim($project['risk_flags'] ?? ''); ?>
                <?php if (empty($riskFlags)): ?>
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>No Risk Flags</span>
                <?php else: ?>
                    <div class="alert alert-danger py-2 mb-0"><?= nl2br(htmlspecialchars($riskFlags)) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-bolt text-primary me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body px-4 d-grid gap-2">
                <a href="<?= BASE_URL ?>/admin/plots?colony_id=<?= (int)($project['colony_id'] ?? 0) ?>" class="btn btn-outline-success btn-sm text-start">
                    <i class="fas fa-map me-2"></i>View All Plots (<?= number_format((int)($project['total_units'] ?? 0)) ?>)
                </a>
                <a href="<?= BASE_URL ?>/admin/bookings?colony_id=<?= (int)($project['colony_id'] ?? 0) ?>" class="btn btn-outline-primary btn-sm text-start">
                    <i class="fas fa-file-signature me-2"></i>Bookings in This Colony
                </a>
                <a href="<?= BASE_URL ?>/admin/projects/progress/<?= $pId ?>" class="btn btn-outline-info btn-sm text-start">
                    <i class="fas fa-chart-line me-2"></i>Update Progress & Budget
                </a>
                <?php if (!empty($project['colony_id'])): ?>
                <a href="<?= BASE_URL ?>/admin/colonies/<?= (int)$project['colony_id'] ?>" class="btn btn-outline-secondary btn-sm text-start">
                    <i class="fas fa-city me-2"></i>Go to Colony Details
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/admin/projects/edit/<?= $pId ?>" class="btn btn-warning btn-sm text-start">
                    <i class="fas fa-edit me-2"></i>Edit Project
                </a>
            </div>
        </div>

        <!-- Tags -->
        <?php if (!empty($project['tags'])): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="fas fa-tags text-secondary me-2"></i>Tags</h6>
            </div>
            <div class="card-body px-4 d-flex flex-wrap gap-2">
                <?php foreach (explode(',', $project['tags']) as $tag): $tag = trim($tag); if ($tag): ?>
                <span class="badge bg-light text-dark border"><?= htmlspecialchars($tag) ?></span>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
