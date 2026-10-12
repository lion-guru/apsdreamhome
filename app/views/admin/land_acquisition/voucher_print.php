<?php
/**
 * Official A4 Printable Land Acquisition Compensation Payout Voucher
 */
$voucher = $voucher ?? [];
$voucherToken = $voucher['voucher_token'] ?? ('APS-VOUCH-GOR-2026-' . $voucher['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title ?? 'Land Compensation Payout Voucher') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46Zmdt9E+9a7k/10/r8g==" crossorigin="anonymous">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; color: #212529; }
        .voucher-box { max-width: 800px; margin: 30px auto; background: #fff; padding: 40px 45px; border: 1px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.08); position: relative; }
        .header-brand { border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 25px; }
        .meta-table th { background: #f8fafc; width: 35%; font-size: 0.9rem; color: #475569; }
        .meta-table td { font-size: 0.95rem; font-weight: 600; }
        .seal-box { border: 1px dashed #94a3b8; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #64748b; }
        @media print {
            body { background: #fff; margin: 0; }
            .voucher-box { border: none; box-shadow: none; margin: 0; padding: 20px; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-print me-2"></i>Print Official Voucher (A4)
    </button>
</div>

<div class="voucher-box">
    <!-- Header -->
    <div class="header-brand">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-black text-primary mb-0">APS DREAM HOME PVT LTD</h3>
                <div class="small text-muted">CIN: U70102UP2019PTC118942 | Land Aggregation &amp; Rural Development Cell</div>
                <div class="small text-muted">Medical College Road, Asuran Chowk, Gorakhpur, UP — 273004</div>
            </div>
            <div class="text-end">
                <span class="badge bg-success text-uppercase px-3 py-2 fs-6">Payment Voucher</span>
                <div class="fw-bold font-monospace mt-2 text-dark"><?= htmlspecialchars($voucherToken) ?></div>
                <div class="small text-muted">Date: <?= date('d M Y', strtotime($voucher['disbursed_at'] ?? 'now')) ?></div>
            </div>
        </div>
    </div>

    <div class="bg-light p-3 rounded mb-4 border">
        <div class="row align-items-center">
            <div class="col-8">
                <div class="small text-muted text-uppercase fw-bold">Payment Description:</div>
                <div class="fs-5 fw-bold text-dark"><?= htmlspecialchars($voucher['milestone_name']) ?></div>
                <div class="small text-muted">Land Deal Reference: <span class="font-monospace fw-semibold"><?= htmlspecialchars($voucher['deal_token']) ?></span></div>
            </div>
            <div class="col-4 text-end">
                <div class="small text-muted text-uppercase fw-bold">Disbursed Amount:</div>
                <div class="fs-3 fw-bold text-success">₹<?= number_format((float)$voucher['amount'], 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Farmer & Parcel Details Table -->
    <h6 class="fw-bold text-dark text-uppercase mb-2"><i class="fas fa-user-check me-2 text-primary"></i>Landholder &amp; Revenue Parcel Details</h6>
    <table class="table table-bordered meta-table mb-4">
        <tbody>
            <tr>
                <th scope="row">Landholder / Farmer Name</th>
                <td><?= htmlspecialchars($voucher['farmer_name']) ?> (Phone: <?= htmlspecialchars($voucher['farmer_phone']) ?>)</td>
            </tr>
            <tr>
                <th scope="row">Aadhaar / PAN Card</th>
                <td>Aadhaar: <?= htmlspecialchars($voucher['farmer_aadhaar'] ?? 'N/A') ?> &nbsp;|&nbsp; PAN: <?= htmlspecialchars($voucher['farmer_pan'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th scope="row">Revenue Mauza &amp; Tehsil</th>
                <td>Mauza <?= htmlspecialchars($voucher['mauza_village']) ?>, Tehsil <?= htmlspecialchars($voucher['tehsil']) ?>, District <?= htmlspecialchars($voucher['district']) ?></td>
            </tr>
            <tr>
                <th scope="row">Gata / Khasra No &amp; Area</th>
                <td>Gata #<?= htmlspecialchars($voucher['gata_number']) ?> &nbsp;|&nbsp; <?= (float)$voucher['area_bigha'] ?> Pucca Bigha (<?= number_format((float)$voucher['area_sqft'], 0) ?> Sq.Ft)</td>
            </tr>
        </tbody>
    </table>

    <!-- Electronic Disbursal & Bank RTGS Details -->
    <h6 class="fw-bold text-dark text-uppercase mb-2"><i class="fas fa-university me-2 text-primary"></i>Banking &amp; Electronic Disbursal Reference</h6>
    <table class="table table-bordered meta-table mb-4">
        <tbody>
            <tr>
                <th scope="row">Beneficiary Bank Account</th>
                <td><?= htmlspecialchars($voucher['bank_name'] ?? 'Bank') ?> &bull; A/C: <?= htmlspecialchars($voucher['bank_account_no'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th scope="row">Bank IFSC Code</th>
                <td class="font-monospace"><?= htmlspecialchars($voucher['bank_ifsc'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th scope="row">Payment Method &amp; UTR</th>
                <td class="text-uppercase"><?= htmlspecialchars($voucher['payment_mode'] ?? 'RTGS') ?> &bull; Ref / UTR: <span class="font-monospace text-primary"><?= htmlspecialchars($voucher['payment_ref'] ?? 'N/A') ?></span></td>
            </tr>
            <tr>
                <th scope="row">Gross Amount &amp; TDS Deducted</th>
                <td>Gross: ₹<?= number_format((float)$voucher['amount'], 2) ?> &nbsp;|&nbsp; TDS u/s 194LA: ₹<?= number_format((float)($voucher['tds_deducted'] ?? 0), 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="small text-muted mb-4 border-start border-3 border-success ps-3 py-1">
        This payment voucher confirms electronic consideration disbursed against staged land acquisition under agreement token <strong><?= htmlspecialchars($voucher['deal_token']) ?></strong> for APS Township expansion.
    </div>

    <!-- Sign-off Block -->
    <div class="row pt-4 text-center mt-3">
        <div class="col-4">
            <div class="seal-box mb-2">
                <span>[ Farmer / Payee Signature ]</span>
            </div>
            <div class="small fw-bold"><?= htmlspecialchars($voucher['farmer_name']) ?></div>
            <div class="small text-muted">Beneficiary Landholder</div>
        </div>
        <div class="col-4">
            <div class="seal-box mb-2">
                <span>[ Land Acquisition Officer ]</span>
            </div>
            <div class="small fw-bold">Estate Land Manager</div>
            <div class="small text-muted">Legal Verification</div>
        </div>
        <div class="col-4">
            <div class="seal-box mb-2">
                <span class="text-primary fw-bold">[ Authorized Signatory ]</span>
            </div>
            <div class="small fw-bold">Finance &amp; Accounts</div>
            <div class="small text-muted">APS Dream Home Pvt Ltd</div>
        </div>
    </div>
</div>

</body>
</html>
