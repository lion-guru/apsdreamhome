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
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/gst/invoices/create" class="btn btn-success"><i class="fas fa-plus me-1"></i> New Invoice</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3" id="filterForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-2">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['from_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['to_date'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <?php foreach ($invoiceTypes as $t): ?>
                            <option value="<?= $t ?>" <?= ($filters['type'] ?? '') == $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="validated" <?= ($filters['status'] ?? '') == 'validated' ? 'selected' : '' ?>>Validated</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">IRN Status</label>
                    <select name="irn_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($irnStatuses as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['irn_status'] ?? '') == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">GSTIN</label>
                    <input type="text" name="gstin" class="form-control form-control-sm" placeholder="Customer GSTIN" value="<?= htmlspecialchars($filters['gstin'] ?? '') ?>">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                    <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear</a>
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
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>GSTIN</th>
                            <th>Place of Supply</th>
                            <th>Taxable</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th>IGST</th>
                            <th>Total</th>
                            <th>IRN Status</th>
                            <th>GSTR-1</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr><td colspan="14" class="text-center py-5 text-muted">No invoices found</td></tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($inv['invoice_type']) ?></span></td>
                                    <td><?= htmlspecialchars($inv['customer_name'] ?? 'N/A') ?></td>
                                    <td><code><?= htmlspecialchars($inv['customer_gstin'] ?? '-') ?></code></td>
                                    <td><?= htmlspecialchars($inv['place_of_supply'] ?? '-') ?></td>
                                    <td>₹<?= number_format($inv['taxable_value'], 2) ?></td>
                                    <td>₹<?= number_format($inv['cgst_amount'], 2) ?></td>
                                    <td>₹<?= number_format($inv['sgst_amount'], 2) ?></td>
                                    <td>₹<?= number_format($inv['igst_amount'], 2) ?></td>
                                    <td class="fw-bold">₹<?= number_format($inv['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $inv['einvoice_status']==='generated'?'success':
                                            ($inv['einvoice_status']==='failed'?'danger':
                                            ($inv['einvoice_status']==='pending'?'warning':'secondary')) ?>">
                                            <?= ucfirst($inv['einvoice_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $inv['gstr1_status']==='filed'?'success':
                                            ($inv['gstr1_status']==='error'?'danger':
                                            ($inv['gstr1_status']==='ready'?'info':'secondary')) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $inv['gstr1_status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $base ?>/admin/gst/invoices/view/<?= $inv['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                            <?php if ($inv['einvoice_status'] === 'pending' && $inv['invoice_type'] === 'B2B'): ?>
                                                <a href="<?= $base ?>/admin/gst/invoices/einvoice/<?= $inv['id'] ?>" class="btn btn-outline-success" onclick="return confirm('Generate IRN for this invoice?')" title="Generate IRN"><i class="fas fa-qrcode"></i></a>
                                            <?php endif; ?>
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