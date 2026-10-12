<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
    <style>
        .stat-card { border-left: 4px solid; transition: transform .2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
        .stat-card.primary { border-color: #6B4EE6; }
        .stat-card.success { border-color: #28a745; }
        .stat-card.warning { border-color: #ffc107; }
        .stat-card.danger { border-color: #dc3545; }
        .stat-card.info { border-color: #17a2b8; }
        .table th { white-space: nowrap; }
        .badge-status { font-size: .75rem; padding: .35em .65em; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-landmark me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/property-tax/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Assessment</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Total Assessments</div>
                            <div class="h2 mb-0"><?= number_format($stats['total']) ?></div>
                        </div>
                        <i class="fas fa-file-invoice fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card secondary h-100" style="border-left-color:#6c757d">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Draft</div>
                            <div class="h2 mb-0"><?= number_format($stats['draft']) ?></div>
                        </div>
                        <i class="fas fa-edit fa-2x text-secondary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Demand Generated</div>
                            <div class="h2 mb-0"><?= number_format($stats['demand_generated']) ?></div>
                        </div>
                        <i class="fas fa-bell fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Closed</div>
                            <div class="h2 mb-0"><?= number_format($stats['closed']) ?></div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Total Demand</div>
                            <div class="h2 mb-0">₹<?= number_format($stats['total_demand'], 0, '.', ',') ?></div>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card h-100" style="border-left-color:#6f42c1">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small"><a href="<?= $base ?>/admin/property-tax/demand-register" class="text-decoration-none text-dark">Demand Register</a></div>
                            <div class="h2 mb-0"><i class="fas fa-book-open"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3" id="filterForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-3">
                    <label class="form-label small">Colony</label>
                    <select name="colony_id" class="form-select form-select-sm">
                        <option value="">All Colonies</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filters['colony_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Assessment Year</label>
                    <select name="assessment_year" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= ($filters['assessment_year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tax Type</label>
                    <select name="tax_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <?php foreach ($taxTypes as $t): ?>
                            <option value="<?= $t ?>" <?= ($filters['tax_type'] ?? '') == $t ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($filters['status'] ?? '') == 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="demand_generated" <?= ($filters['status'] ?? '') == 'demand_generated' ? 'selected' : '' ?>>Demand Generated</option>
                        <option value="closed" <?= ($filters['status'] ?? '') == 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
                    <a href="<?= $base ?>/admin/property-tax" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Assessment Year</th>
                            <th>Tax Type</th>
                            <th>Authority</th>
                            <th>Colony / Plot</th>
                            <th>Category</th>
                            <th>Net Tax</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Notice #</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($masters)): ?>
                            <tr><td colspan="10" class="text-center py-5 text-muted">No tax assessments found</td></tr>
                        <?php else: ?>
                            <?php foreach ($masters as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['assessment_year']) ?></td>
                                    <td><span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $m['tax_type'])) ?></span></td>
                                    <td><?= htmlspecialchars($m['tax_authority']) ?><?= $m['ward_zone'] ? ' - ' . htmlspecialchars($m['ward_zone']) : '' ?></td>
                                    <td>
                                        <?php if ($m['colony_name']): ?>
                                            <strong><?= htmlspecialchars($m['colony_name']) ?></strong>
                                            <?php if ($m['plot_number']): ?>
                                                <br><small class="text-muted">Plot: <?= htmlspecialchars($m['plot_number']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= ucfirst($m['property_category']) ?></td>
                                    <td class="fw-bold text-primary">₹<?= number_format($m['net_tax_amount'], 2) ?></td>
                                    <td><?= $m['due_date'] ? date('d M Y', strtotime($m['due_date'])) : '-' ?></td>
                                    <td>
                                        <span class="badge-status
                                            <?php
                                            switch ($m['status']) {
                                                case 'draft': echo 'bg-secondary'; break;
                                                case 'published': echo 'bg-info'; break;
                                                case 'demand_generated': echo 'bg-warning text-dark'; break;
                                                case 'closed': echo 'bg-success'; break;
                                                default: echo 'bg-light text-dark';
                                            }
                                            ?>">
                                            <?= ucfirst(str_replace('_', ' ', $m['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $m['demand_notice_number'] ? htmlspecialchars($m['demand_notice_number']) : '<span class="text-muted">-</span>' ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $base ?>/admin/property-tax/payments/<?= $m['id'] ?>" class="btn btn-outline-primary" title="Payments"><i class="fas fa-money-bill-wave"></i></a>
                                            <?php if ($m['status'] !== 'demand_generated' && $m['status'] !== 'closed'): ?>
                                                <a href="<?= $base ?>/admin/property-tax/generate-demand/<?= $m['id'] ?>" class="btn btn-outline-warning" onclick="return confirm('Generate demand notice?')" title="Generate Demand"><i class="fas fa-bell"></i></a>
                                            <?php endif; ?>
                                            <a href="<?= $base ?>/admin/property-tax/edit/<?= $m['id'] ?>" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>