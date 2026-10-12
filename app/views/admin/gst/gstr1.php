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
        <h1 class="h3 mb-0"><i class="fas fa-file-export me-2 text-info"></i><?= htmlspecialchars($page_title) ?></h1>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/gst/gstr3b" class="btn btn-outline-warning text-dark"><i class="fas fa-file-import me-1"></i> GSTR-3B</a>
            <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-outline-secondary"><i class="fas fa-file-invoice me-1"></i> Invoices</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-4">
                    <label class="form-label">Return Period</label>
                    <input type="month" name="period" class="form-control" value="<?= htmlspecialchars($period) ?>" required>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Load Data</button>
                    <button type="submit" name="save" value="1" class="btn btn-success" onclick="return confirm('Save GSTR-1 summary data?')"><i class="fas fa-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <?php foreach (['B2B','B2C','B2CL','EXP','SEZ','DE','CDNR','CDNUR','HSN','NIL','ADV','ADJ'] as $section): 
                $records = $data[$section] ?? [];
                if (empty($records)) continue;
            ?>
                <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2"><i class="fas fa-table me-1"></i>Section: <?= $section ?> (<?= count($records) ?> records)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($section === 'HSN'): ?>
                                        <th>HSN</th><th>UOM</th><th>Qty</th><th>Taxable</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Cess</th>
                                    <?php else: ?>
                                        <th>GSTIN</th><th>Invoice #</th><th>Date</th><th>Value</th><th>Taxable</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Cess</th><th>POS</th><th>Type</th><th>RC</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $r): ?>
                                    <tr>
                                        <?php if ($section === 'HSN'): ?>
                                            <td><code><?= htmlspecialchars($r['hsn_code'] ?? '') ?></code></td>
                                            <td><?= htmlspecialchars($r['uqc'] ?? $r['uom'] ?? '') ?></td>
                                            <td class="text-end"><?= number_format($r['total_quantity'] ?? 0, 3) ?></td>
                                            <td class="text-end">₹<?= number_format($r['taxable_value'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['cgst_amount'] ?? $r['cgst'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['sgst_amount'] ?? $r['sgst'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['igst_amount'] ?? $r['igst'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['cess_amount'] ?? $r['cess'] ?? 0, 2) ?></td>
                                        <?php else: ?>
                                            <td><code><?= htmlspecialchars($r['customer_gstin'] ?? '') ?></code></td>
                                            <td><?= htmlspecialchars($r['invoice_number'] ?? '') ?></td>
                                            <td><?= $r['invoice_date'] ? date('d/m/Y', strtotime($r['invoice_date'])) : '' ?></td>
                                            <td class="text-end">₹<?= number_format($r['invoice_value'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['taxable_value'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['cgst_amount'] ?? $r['cgst'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['sgst_amount'] ?? $r['sgst'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['igst_amount'] ?? $r['igst'] ?? 0, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($r['cess_amount'] ?? $r['cess'] ?? 0, 2) ?></td>
                                            <td><?= htmlspecialchars($r['place_of_supply'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($r['supply_type'] ?? '') ?></td>
                                            <td><?= ($r['reverse_charge'] ?? 0) ? 'Y' : 'N' ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty(array_filter($data))): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No invoice data for period <?= htmlspecialchars($period) ?></p>
                    <a href="<?= $base ?>/admin/gst/invoices/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Invoice</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>