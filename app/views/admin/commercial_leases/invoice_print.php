<?php
/**
 * Official A4 Printable Commercial Rent Invoice & Demand Note
 */
$invoice = $invoice ?? [];
$invNo = $invoice['invoice_no'] ?? 'APS-RENT-GOR-2026-0000';
$upiLink = "upi://pay?pa=apsdreamhome@icici&pn=APS+Dream+Home+Pvt+Ltd&am=" . urlencode((string)$invoice['total_due']) . "&cu=INR&tn=" . urlencode($invNo);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($upiLink);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title ?? 'Commercial Rent Invoice') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46Zmdt9E+9a7k/10/r8g==" crossorigin="anonymous">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; color: #212529; }
        .invoice-box { max-width: 800px; margin: 30px auto; background: #fff; padding: 40px; border: 1px solid #dee2e6; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; }
        .header-brand { border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        .table-items th { background: #f8fafc; font-size: 0.85rem; text-transform: uppercase; color: #475569; }
        .table-items td { font-size: 0.95rem; }
        @media print {
            body { background: #fff; margin: 0; }
            .invoice-box { border: none; box-shadow: none; margin: 0; padding: 20px; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-print me-2"></i>Print Official Invoice (A4)
    </button>
</div>

<div class="invoice-box">
    <!-- Header -->
    <div class="header-brand">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-black text-primary mb-0">APS DREAM HOME PVT LTD</h3>
                <div class="small text-muted">CIN: U70102UP2019PTC118942 | Commercial Asset Leasing Division</div>
                <div class="small text-muted">Medical College Road, Asuran Chowk, Gorakhpur, UP — 273004</div>
            </div>
            <div class="text-end">
                <span class="badge bg-primary text-uppercase px-3 py-2 fs-6">Rent Demand Note</span>
                <div class="fw-bold font-monospace mt-2 text-dark"><?= htmlspecialchars($invNo) ?></div>
                <div class="small text-muted">Date: <?= date('d M Y', strtotime($invoice['created_at'])) ?></div>
            </div>
        </div>
    </div>

    <!-- Tenant & Unit Information -->
    <div class="row mb-4">
        <div class="col-6">
            <h6 class="fw-bold text-muted text-uppercase small mb-2">Billed To (Tenant):</h6>
            <div class="fw-bold fs-6 text-dark"><?= htmlspecialchars($invoice['tenant_name']) ?></div>
            <?php if (!empty($invoice['tenant_business_name'])): ?>
                <div class="fw-medium text-primary"><?= htmlspecialchars($invoice['tenant_business_name']) ?></div>
            <?php endif; ?>
            <div class="small text-muted">Phone: <?= htmlspecialchars($invoice['tenant_phone']) ?></div>
            <?php if (!empty($invoice['tenant_gstin'])): ?>
                <div class="small text-muted">GSTIN: <?= htmlspecialchars($invoice['tenant_gstin']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <h6 class="fw-bold text-muted text-uppercase small mb-2">Property Details:</h6>
            <div class="fw-bold text-dark">Unit No: <?= htmlspecialchars($invoice['unit_number']) ?></div>
            <div class="small text-muted"><?= htmlspecialchars($invoice['colony_name'] ?? 'APS Township') ?>, Gorakhpur</div>
            <div class="small text-muted">Billing Cycle: <strong><?= date('F Y', strtotime($invoice['billing_month'] . '-01')) ?></strong></div>
            <div class="small text-muted">Lease Token: <span class="font-monospace"><?= htmlspecialchars($invoice['lease_token']) ?></span></div>
        </div>
    </div>

    <!-- Charges Breakdown Table -->
    <table class="table table-bordered table-items mb-4">
        <thead>
            <tr>
                <th scope="col" class="ps-3">Description</th>
                <th scope="col" class="text-center">Units / Qty</th>
                <th scope="col" class="text-end">Rate (₹)</th>
                <th scope="col" class="text-end pe-3">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="ps-3">
                    <div class="fw-semibold">Monthly Commercial Shop Rent</div>
                    <div class="small text-muted">Contracted base lease for <?= htmlspecialchars($invoice['unit_number']) ?></div>
                </td>
                <td class="text-center">1 Month</td>
                <td class="text-end">₹<?= number_format((float)$invoice['base_rent'], 2) ?></td>
                <td class="text-end pe-3 fw-semibold">₹<?= number_format((float)$invoice['base_rent'], 2) ?></td>
            </tr>
            <?php if ((float)$invoice['electricity_amount'] > 0): ?>
            <tr>
                <td class="ps-3">
                    <div class="fw-semibold">Electricity Submeter Consumption</div>
                    <div class="small text-muted"><?= (float)$invoice['electricity_units'] ?> units billed</div>
                </td>
                <td class="text-center"><?= (float)$invoice['electricity_units'] ?> units</td>
                <td class="text-end">₹<?= number_format((float)$invoice['electricity_rate'], 2) ?></td>
                <td class="text-end pe-3 fw-semibold">₹<?= number_format((float)$invoice['electricity_amount'], 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ((float)$invoice['maintenance_fee'] > 0): ?>
            <tr>
                <td class="ps-3">
                    <div class="fw-semibold">Common Area Maintenance (CAM)</div>
                    <div class="small text-muted">Corridor lighting, security &amp; plaza cleaning</div>
                </td>
                <td class="text-center">Fixed</td>
                <td class="text-end">₹<?= number_format((float)$invoice['maintenance_fee'], 2) ?></td>
                <td class="text-end pe-3 fw-semibold">₹<?= number_format((float)$invoice['maintenance_fee'], 2) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="table-light">
            <tr>
                <th colspan="3" class="text-end fw-bold">Total Demand Payable:</th>
                <th class="text-end pe-3 fw-bold fs-6 text-primary">₹<?= number_format((float)$invoice['total_due'], 2) ?></th>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Amount Paid to Date:</th>
                <th class="text-end pe-3 text-success">₹<?= number_format((float)$invoice['paid_amount'], 2) ?></th>
            </tr>
            <tr>
                <th colspan="3" class="text-end fw-bold">Balance Due:</th>
                <th class="text-end pe-3 fw-bold text-danger">₹<?= number_format((float)($invoice['total_due'] - $invoice['paid_amount']), 2) ?></th>
            </tr>
        </tfoot>
    </table>

    <!-- Payment & QR Block -->
    <div class="p-3 bg-light rounded border mb-4">
        <div class="row align-items-center">
            <div class="col-8">
                <h6 class="fw-bold mb-1"><i class="fas fa-university me-2 text-primary"></i>Direct Bank RTGS / NEFT Details:</h6>
                <div class="small">Account Name: <strong>APS Dream Home Private Limited</strong></div>
                <div class="small">Bank: <strong>ICICI Bank</strong> | Branch: <strong>Bank Road, Gorakhpur</strong></div>
                <div class="small">Account No: <strong>005805012345</strong> | IFSC: <strong>ICIC0000058</strong></div>
                <div class="small mt-1 text-muted">UPI VPA: <strong>apsdreamhome@icici</strong></div>
            </div>
            <div class="col-4 text-end">
                <div class="small text-muted mb-1">Scan &amp; Pay via UPI</div>
                <img src="<?= htmlspecialchars($qrUrl) ?>" alt="UPI QR Code" width="85" height="85" class="border p-1 bg-white">
            </div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="row pt-4 text-center">
        <div class="col-6">
            <div class="small text-muted mb-4">[ Tenant Acceptance ]</div>
            <div class="small fw-bold"><?= htmlspecialchars($invoice['tenant_name']) ?></div>
            <div class="small text-muted">Authorized Signatory / Tenant</div>
        </div>
        <div class="col-6">
            <div class="small text-muted mb-4">[ For APS Dream Home Pvt Ltd ]</div>
            <div class="small fw-bold">Estate &amp; Commercial Leasing Desk</div>
            <div class="small text-muted">Finance Department</div>
        </div>
    </div>
</div>

</body>
</html>
