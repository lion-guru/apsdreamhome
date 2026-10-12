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
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-book-open me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/property-tax" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Assessments</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Generate Demand Register</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end" id="drForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-5">
                    <label class="form-label">Colony <span class="text-danger">*</span></label>
                    <select name="colony_id" class="form-select" required>
                        <option value="">Select Colony</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $colony_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assessment Year <span class="text-danger">*</span></label>
                    <select name="year" class="form-select" required>
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> View Register</button>
                    <?php if ($colony_id): ?>
                        <button type="submit" name="generate" value="1" class="btn btn-success" onclick="return confirm('Generate demand register for selected colony and year?')"><i class="fas fa-cog me-1"></i> Generate/Refresh</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if ($colony_id && $register): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card primary h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Properties</div>
                        <div class="h2 mb-0"><?= number_format($register['total_properties']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card info h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Demand</div>
                        <div class="h2 mb-0">₹<?= number_format($register['total_demand'], 0, '.', ',') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card success h-100">
                    <div class="card-body">
                        <div class="text-muted small">Collected</div>
                        <div class="h2 mb-0">₹<?= number_format($register['total_collected'], 0, '.', ',') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card danger h-100">
                    <div class="card-body">
                        <div class="text-muted small">Pending</div>
                        <div class="h2 mb-0">₹<?= number_format($register['total_pending'], 0, '.', ',') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($register['total_penalty_collected'] > 0): ?>
            <div class="alert alert-info d-inline-flex align-items-center mb-4">
                <i class="fas fa-coins me-2"></i> Penalty Collected: <strong>₹<?= number_format($register['total_penalty_collected'], 2) ?></strong>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($colony_id && !empty($masters)): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Assessments for <?= $colonies[array_search($colony_id, array_column($colonies, 'id'))]['name'] ?? 'Selected Colony' ?> - <?= $year ?></h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
                    <a href="<?= $base ?>/admin/property-tax/demand-register?colony_id=<?= $colony_id ?>&year=<?= $year ?>&export=1" class="btn btn-outline-success"><i class="fas fa-file-excel me-1"></i> Export</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plot</th>
                                <th>Tax Type</th>
                                <th>Category</th>
                                <th>Net Tax</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Notice #</th>
                                <th>Paid</th>
                                <th>Pending</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($masters as $m):
                                $paid = 0;
                                $payments = $this->taxService->getPaymentHistory($m['id']);
                                foreach ($payments as $p) if ($p['status'] === 'verified') $paid += (float)$p['total_paid'];
                                $pending = (float)$m['net_tax_amount'] - $paid;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['plot_number'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $m['tax_type'])) ?></span></td>
                                    <td><?= ucfirst($m['property_category']) ?></td>
                                    <td class="fw-bold text-primary">₹<?= number_format($m['net_tax_amount'], 2) ?></td>
                                    <td><?= $m['due_date'] ? date('d M Y', strtotime($m['due_date'])) : '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $m['status']==='demand_generated'?'warning':($m['status']==='closed'?'success':'secondary') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $m['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $m['demand_notice_number'] ? htmlspecialchars($m['demand_notice_number']) : '-' ?></td>
                                    <td class="text-success">₹<?= number_format($paid, 2) ?></td>
                                    <td class="text-<?= $pending > 0 ? 'danger' : 'success' ?> fw-bold">₹<?= number_format(max(0, $pending), 2) ?></td>
                                    <td>
                                        <a href="<?= $base ?>/admin/property-tax/payments/<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-money-bill-wave"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($colony_id): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-search fa-3x mb-3"></i>
            <p>No assessments found for this colony and year.</p>
            <a href="<?= $base ?>/admin/property-tax/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Assessment</a>
        </div>
    <?php endif; ?>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>