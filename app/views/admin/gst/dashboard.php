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
        .period-badge { font-size: .85rem; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2 text-success"></i><?= htmlspecialchars($page_title) ?></h1>
        <div class="d-flex gap-2">
            <span class="badge bg-primary period-badge px-3 py-2">Period: <?= htmlspecialchars($currentPeriod) ?></span>
            <a href="<?= $base ?>/admin/gst/invoices/create" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Invoice</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Total Invoices</div>
                            <div class="h2 mb-0"><?= number_format($stats['total_invoices']) ?></div>
                        </div>
                        <i class="fas fa-file-invoice fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card primary h-100" style="border-left-color:#6B4EE6">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Taxable Value</div>
                            <div class="h2 mb-0">₹<?= number_format($stats['total_taxable'], 0, '.', ',') ?></div>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">Total Tax</div>
                            <div class="h2 mb-0">₹<?= number_format($stats['total_tax'], 0, '.', ',') ?></div>
                        </div>
                        <i class="fas fa-calculator fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">B2B / B2C</div>
                            <div class="h2 mb-0"><?= $stats['b2b_invoices'] ?> / <?= $stats['b2c_invoices'] ?></div>
                        </div>
                        <i class="fas fa-building fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card h-100" style="border-left-color:#17a2b8">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">GSTR-1 Status</div>
                            <div class="h2 mb-0">
                                <span class="badge bg-<?= $stats['gstr1_status']==='filed'?'success':($stats['gstr1_status']==='error'?'danger':'warning') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $stats['gstr1_status'])) ?>
                                </span>
                            </div>
                        </div>
                        <a href="<?= $base ?>/admin/gst/gstr1" class="btn btn-sm btn-outline-info">View</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100" style="border-left-color:#28a745">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">GSTR-3B Status</div>
                            <div class="h2 mb-0">
                                <span class="badge bg-<?= $stats['gstr3b_status']==='filed'?'success':($stats['gstr3b_status']==='error'?'danger':'warning') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $stats['gstr3b_status'])) ?>
                                </span>
                            </div>
                        </div>
                        <a href="<?= $base ?>/admin/gst/gstr3b" class="btn btn-sm btn-outline-success">View</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card h-100" style="border-left-color:#dc3545">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">E-Invoice Issues</div>
                            <div class="h2 mb-0 text-danger"><?= $stats['pending_irn'] + $stats['failed_irn'] ?></div>
                        </div>
                        <a href="<?= $base ?>/admin/gst/einvoice-log" class="btn btn-sm btn-outline-danger">View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Invoices</h5>
                    <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Customer</th>
                                    <th>Taxable</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                    <th>IRN</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentInvoices)): ?>
                                    <tr><td colspan="9" class="text-center py-4 text-muted">No invoices this period</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentInvoices as $inv): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                                            <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($inv['invoice_type']) ?></span></td>
                                            <td><?= htmlspecialchars($inv['customer_name'] ?? 'N/A') ?><br><small class="text-muted"><?= htmlspecialchars($inv['customer_gstin'] ?? '') ?></small></td>
                                            <td>₹<?= number_format($inv['taxable_value'], 2) ?></td>
                                            <td>₹<?= number_format($inv['total_tax'], 2) ?></td>
                                            <td class="fw-bold">₹<?= number_format($inv['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $inv['einvoice_status']==='generated'?'success':
                                                    ($inv['einvoice_status']==='failed'?'danger':
                                                    ($inv['einvoice_status']==='pending'?'warning':'secondary')) ?>">
                                                    <?= ucfirst($inv['einvoice_status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= $base ?>/admin/gst/invoices/view/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
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

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Quick HSN Codes</h5>
                    <a href="<?= $base ?>/admin/gst/hsn-codes" class="btn btn-sm btn-outline-primary">Manage All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Rate</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hsnCodes as $h): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($h['hsn_code']) ?></code></td>
                                        <td class="small"><?= htmlspecialchars(mb_strimwidth($h['description'], 0, 35, '...')) ?></td>
                                        <td><span class="badge bg-info"><?= $h['gst_rate'] ?>%</span></td>
                                        <td><span class="badge bg-<?= $h['is_service'] ? 'primary' : 'secondary' ?>"><?= $h['is_service'] ? 'Service' : 'Goods' ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-outline-primary"><i class="fas fa-file-invoice me-2"></i>All Invoices</a>
                        <a href="<?= $base ?>/admin/gst/hsn-codes" class="btn btn-outline-success"><i class="fas fa-tags me-2"></i>HSN Master</a>
                        <a href="<?= $base ?>/admin/gst/gstr1" class="btn btn-outline-info"><i class="fas fa-file-export me-2"></i>GSTR-1</a>
                        <a href="<?= $base ?>/admin/gst/gstr3b" class="btn btn-outline-warning text-dark"><i class="fas fa-file-import me-2"></i>GSTR-3B</a>
                        <a href="<?= $base ?>/admin/gst/returns-log" class="btn btn-outline-secondary"><i class="fas fa-history me-2"></i>Filing Log</a>
                        <a href="<?= $base ?>/admin/gst/einvoice-log" class="btn btn-outline-danger"><i class="fas fa-qrcode me-2"></i>E-Invoice Log</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>