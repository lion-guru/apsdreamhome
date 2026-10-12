<?php
/**
 * Official Printable A4 Plot Transfer & Re-Allotment NOC Certificate
 * Features verification QR code link to /verify/transfer/{noc_number}
 */
$page_title = $page_title ?? 'Ownership Transfer NOC';
$transfer = $transfer ?? [];
$verify_url = $verify_url ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
        }
        .cert-container {
            max-width: 820px;
            margin: 30px auto;
            background: #fff;
            padding: 45px 55px;
            border: 2px solid #1a365d;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: relative;
        }
        .cert-border-inner {
            border: 1px dashed #cbd5e0;
            padding: 30px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            color: rgba(26, 54, 93, 0.04);
            font-weight: 900;
            text-transform: uppercase;
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }
        .cert-header {
            text-align: center;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .cert-title {
            color: #1a365d;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .cert-subtitle {
            font-size: 13px;
            color: #4a5568;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .section-heading {
            background: #f1f5f9;
            color: #1a365d;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 4px solid #1a365d;
            margin-top: 20px;
            margin-bottom: 12px;
        }
        .data-table {
            width: 100%;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .data-table td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .data-table .label {
            width: 32%;
            font-weight: 600;
            color: #4a5568;
        }
        .data-table .value {
            color: #1a202c;
            font-weight: 500;
        }
        .declaration-text {
            font-size: 12px;
            line-height: 1.6;
            color: #334155;
            text-align: justify;
            margin-top: 15px;
            background: #f8fafc;
            padding: 12px 15px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .sig-box {
            text-align: center;
            width: 200px;
            font-size: 12px;
        }
        .sig-line {
            border-top: 1px solid #1a202c;
            margin-top: 50px;
            padding-top: 5px;
            font-weight: 600;
        }
        .qr-box {
            text-align: center;
            font-size: 11px;
            color: #64748b;
        }
        .qr-placeholder {
            width: 90px;
            height: 90px;
            margin: 0 auto 5px;
            background: #fff;
            padding: 5px;
            border: 1px solid #cbd5e0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .cert-container {
                margin: 0;
                padding: 20px;
                box-shadow: none;
                border: 2px solid #000;
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white d-flex justify-content-center gap-3 align-items-center">
    <span>Official Document Preview</span>
    <button onclick="window.print();" class="btn btn-primary btn-sm px-4">
        <i class="fas fa-print me-1"></i>Print / Save as PDF
    </button>
    <a href="javascript:history.back()" class="btn btn-outline-light btn-sm">Close</a>
</div>

<div class="cert-container">
    <div class="watermark">APS DREAM HOME</div>
    <div class="cert-border-inner">
        <!-- Header -->
        <div class="cert-header">
            <h3 class="fw-bold mb-1" style="color: #1a365d; letter-spacing: 1px;">APS DREAM HOME VENTURES PVT. LTD.</h3>
            <div class="small text-muted">CIN: U70109UP2020PTC132456 · RERA Registered Developer</div>
            <div class="small text-muted">Head Office: Corporate Tower, Civil Lines, Gorakhpur, Uttar Pradesh — 273001</div>
            
            <div class="cert-title">No-Objection Certificate (NOC)</div>
            <div class="cert-subtitle">FOR PLOT RE-ALLOTMENT & TITLE TRANSFER</div>
            
            <div class="d-flex justify-content-between mt-3 font-monospace small px-2">
                <div><strong>Ref No:</strong> <span class="text-primary fw-bold"><?= htmlspecialchars($transfer['noc_number'] ?? 'NOC-PENDING') ?></span></div>
                <div><strong>Date of Issue:</strong> <?= date('d M Y', strtotime($transfer['noc_issued_at'] ?? 'now')) ?></div>
            </div>
        </div>

        <!-- Section 1: Property Description -->
        <div class="section-heading">1. Property & Township Demarcation</div>
        <table class="data-table">
            <tr>
                <td class="label">Colony / Township:</td>
                <td class="value"><strong><?= htmlspecialchars($transfer['colony_name'] ?? '—') ?></strong></td>
                <td class="label">Plot Number:</td>
                <td class="value"><strong>Plot #<?= htmlspecialchars($transfer['plot_number'] ?? '—') ?></strong></td>
            </tr>
            <tr>
                <td class="label">Plot Area:</td>
                <td class="value"><?= (float)($transfer['area_sqft'] ?? 0) ?> Sq.Ft</td>
                <td class="label">Dimensions:</td>
                <td class="value"><?= htmlspecialchars($transfer['dimensions'] ?? 'Standard Layout') ?></td>
            </tr>
            <tr>
                <td class="label">Location / Landmark:</td>
                <td class="value" colspan="3"><?= htmlspecialchars($transfer['colony_location'] ?? 'Near Medical College Road, Gorakhpur') ?></td>
            </tr>
        </table>

        <!-- Section 2: Transfer Parties -->
        <div class="section-heading">2. Particulars of Transfer Parties</div>
        <table class="data-table">
            <tr>
                <td class="label">Transferor (Existing Allottee):</td>
                <td class="value"><strong><?= htmlspecialchars($transfer['current_customer_name'] ?? '—') ?></strong></td>
                <td class="label">Phone:</td>
                <td class="value"><?= htmlspecialchars($transfer['current_customer_phone'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="label">Transferee (New Allottee):</td>
                <td class="value"><strong><?= htmlspecialchars($transfer['new_buyer_name'] ?? '—') ?></strong></td>
                <td class="label">Phone:</td>
                <td class="value"><?= htmlspecialchars($transfer['new_buyer_phone'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="label">Transferee Aadhaar No:</td>
                <td class="value"><?= htmlspecialchars($transfer['new_buyer_aadhaar'] ?? '—') ?></td>
                <td class="label">Transferee PAN No:</td>
                <td class="value"><?= htmlspecialchars($transfer['new_buyer_pan'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="label">Nature of Transfer:</td>
                <td class="value"><?= strtoupper(str_replace('_', ' ', (string)($transfer['transfer_type'] ?? 'resale'))) ?></td>
                <td class="label">Transfer Admin Fee:</td>
                <td class="value">₹<?= number_format((float)($transfer['transfer_fee'] ?? 0), 2) ?> (<?= ucfirst((string)($transfer['transfer_fee_status'] ?? 'paid')) ?>)</td>
            </tr>
        </table>

        <!-- Section 3: Statutory Certification & Declaration -->
        <div class="section-heading">3. Company Clearance Certification</div>
        <div class="declaration-text">
            This is to certify that <strong>APS Dream Home Ventures Pvt. Ltd.</strong> has examined company records regarding the aforementioned plot. All past development charges, maintenance dues, and installments till date have been accounted for or transferred under agreement. The company confirms that:
            <ol class="mb-0 mt-1 ps-3">
                <li>The company holds <strong>NO OBJECTION</strong> to the transfer of allotment rights and possession from the Transferor to the Transferee.</li>
                <li>There is no registered mortgage, stay order, or lien exercised by the company over the said plot.</li>
                <li>Upon execution of the official re-allotment deed, the Transferee shall step into the shoes of the original allottee with all attendant rights and covenants.</li>
            </ol>
        </div>

        <!-- Section 4: Signatures & Verification QR -->
        <div class="signature-section">
            <div class="sig-box">
                <div class="sig-line">Signature of Transferor</div>
                <small class="text-muted"><?= htmlspecialchars($transfer['current_customer_name'] ?? 'Seller') ?></small>
            </div>

            <div class="qr-box">
                <div class="qr-placeholder">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= urlencode($verify_url) ?>" alt="Verify QR" width="80" height="80">
                </div>
                <div>Scan to Verify Authenticity</div>
                <div class="font-monospace" style="font-size: 10px;"><?= htmlspecialchars($transfer['noc_number'] ?? '') ?></div>
            </div>

            <div class="sig-box">
                <div class="sig-line">Authorized Signatory</div>
                <small class="text-muted">For APS Dream Home Ventures</small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
