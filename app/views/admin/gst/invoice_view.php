<?php
$base = rtrim(BASE_URL, '/');
$invoice = $invoice ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($invoice['invoice_number'] ?? 'Invoice') ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Invoice #<?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></h1>
        <div class="d-flex gap-2">
            <?php if ($invoice['einvoice_status'] === 'pending' && $invoice['invoice_type'] === 'B2B'): ?>
                <a href="<?= $base ?>/admin/gst/invoices/einvoice/<?= $invoice['id'] ?>" class="btn btn-success" onclick="return confirm('Generate IRN?')"><i class="fas fa-qrcode me-1"></i> Generate IRN</a>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
            <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-secondary"><?= htmlspecialchars($invoice['invoice_type']) ?></span>
                <span class="badge bg-<?= $invoice['supply_type'] === 'inter_state' ? 'warning' : 'info' ?>"><?= ucwords(str_replace('_', '-', $invoice['supply_type'])) ?></span>
                <span class="badge bg-<?= $invoice['status'] === 'cancelled' ? 'danger' : 'success' ?>"><?= ucfirst($invoice['status']) ?></span>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-<?= 
                    $invoice['einvoice_status']==='generated'?'success':
                    ($invoice['einvoice_status']==='failed'?'danger':
                    ($invoice['einvoice_status']==='pending'?'warning':'secondary')) ?>">
                    IRN: <?= ucfirst($invoice['einvoice_status']) ?>
                </span>
                <span class="badge bg-<?= 
                    $invoice['gstr1_status']==='filed'?'success':
                    ($invoice['gstr1_status']==='error'?'danger':'warning') ?>">
                    GSTR-1: <?= ucfirst(str_replace('_', ' ', $invoice['gstr1_status'])) ?>
                </span>
                <span class="badge bg-<?= 
                    $invoice['gstr3b_status']==='filed'?'success':
                    ($invoice['gstr3b_status']==='error'?'danger':'warning') ?>">
                    GSTR-3B: <?= ucfirst(str_replace('_', ' ', $invoice['gstr3b_status'])) ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3"><strong>Invoice #</strong><br><?= htmlspecialchars($invoice['invoice_number']) ?></div>
                <div class="col-md-3"><strong>Date</strong><br><?= date('d M Y', strtotime($invoice['invoice_date'])) ?></div>
                <div class="col-md-3"><strong>IRN</strong><br><?= htmlspecialchars($invoice['irn'] ?? 'Not generated') ?></div>
                <div class="col-md-3"><strong>IRN Date</strong><br><?= $invoice['irn_date'] ? date('d M Y H:i', strtotime($invoice['irn_date'])) : '-' ?></div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <strong>Customer Details</strong><br>
                    <strong><?= htmlspecialchars($invoice['customer_name'] ?? 'N/A') ?></strong><br>
                    GSTIN: <code><?= htmlspecialchars($invoice['customer_gstin'] ?? '-') ?></code><br>
                    <?= htmlspecialchars($invoice['customer_address'] ?? '-') ?><br>
                    State: <?= htmlspecialchars($invoice['customer_state_code'] ?? '-') ?> | Pincode: <?= htmlspecialchars($invoice['customer_pincode'] ?? '-') ?>
                </div>
                <div class="col-md-6">
                    <strong>Place of Supply</strong>: <?= htmlspecialchars($invoice['place_of_supply'] ?? '-') ?><br>
                    <strong>Reverse Charge</strong>: <?= $invoice['reverse_charge'] ? 'Yes' : 'No' ?><br>
                    <?php if ($invoice['booking_id']): ?>
                        <strong>Booking</strong>: #<?= $invoice['booking_id'] ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['plot_id']): ?>
                        <strong>Plot</strong>: #<?= $invoice['plot_id'] ?><br>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>HSN</th>
                            <th>Description</th>
                            <th>UOM</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Disc</th>
                            <th class="text-end">Taxable</th>
                            <th>GST%</th>
                            <th class="text-end">CGST</th>
                            <th class="text-end">SGST</th>
                            <th class="text-end">IGST</th>
                            <th class="text-end">Cess</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoice['items'] as $item): 
                            $taxable = ($item['rate'] * $item['quantity']) - $item['discount_amount'];
                        ?>
                            <tr>
                                <td><?= $item['line_number'] ?></td>
                                <td><code><?= htmlspecialchars($item['hsn_code']) ?></code></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td><?= htmlspecialchars($item['uom']) ?></td>
                                <td class="text-end"><?= number_format($item['quantity'], 3) ?></td>
                                <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                <td class="text-end">₹<?= number_format($item['discount_amount'], 2) ?></td>
                                <td class="text-end">₹<?= number_format($taxable, 2) ?></td>
                                <td><?= $item['gst_rate'] ?>%</td>
                                <td class="text-end">₹<?= number_format($item['cgst_amount'], 2) ?></td>
                                <td class="text-end">₹<?= number_format($item['sgst_amount'], 2) ?></td>
                                <td class="text-end">₹<?= number_format($item['igst_amount'], 2) ?></td>
                                <td class="text-end">₹<?= number_format($item['cess_amount'], 2) ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($item['total_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end">Totals</th>
                            <th class="text-end">₹<?= number_format($invoice['taxable_value'], 2) ?></th>
                            <th></th>
                            <th class="text-end">₹<?= number_format($invoice['cgst_amount'], 2) ?></th>
                            <th class="text-end">₹<?= number_format($invoice['sgst_amount'], 2) ?></th>
                            <th class="text-end">₹<?= number_format($invoice['igst_amount'], 2) ?></th>
                            <th class="text-end">₹<?= number_format($invoice['cess_amount'], 2) ?></th>
                            <th class="text-end">₹<?= number_format($invoice['total_amount'], 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <?php if ($invoice['notes']): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light"><strong>Notes</strong></div>
            <div class="card-body"><?= nl2br(htmlspecialchars($invoice['notes'])) ?></div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>