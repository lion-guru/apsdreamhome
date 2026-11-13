<?php
/**
 * Land Broker Engagement, Due Diligence & Indemnity Contract View
 * Print-ready legal document conforming to UP/Purvanchal land acquisition practice.
 */
$contract = $contract ?? [];
$broker   = $contract['broker'] ?? ($broker ?? []);
$company  = $contract['company'] ?? [];
$clauses  = $contract['clauses'] ?? [];
$deal     = $contract['deal'] ?? ($deal ?? null);

$refNo    = $contract['agreement_ref'] ?? ($broker['agreement_ref_number'] ?? 'LBA-' . date('Y') . '-' . str_pad((string)($broker['id'] ?? 1), 4, '0', STR_PAD_LEFT));
$rateDesc = $contract['rate_description'] ?? 'As per agreed terms';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broker Agreement — <?php echo htmlspecialchars($broker['name'] ?? 'Contract'); ?> (<?php echo htmlspecialchars($refNo); ?>)</title>
    <link href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
        }
        .contract-paper {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 22mm 20mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 65px;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.035);
            white-space: nowrap;
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .contract-header {
            border-bottom: 3px double #0f766e;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .contract-title {
            color: #0f766e;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .stamp-box {
            border: 2px dashed #94a3b8;
            background: #f8fafc;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .clause-item {
            margin-bottom: 14px;
            text-align: justify;
            line-height: 1.65;
        }
        .clause-num {
            font-weight: 700;
            color: #0f766e;
        }
        .table-custom-bordered {
            border: 1px solid #cbd5e1;
        }
        .table-custom-bordered td, .table-custom-bordered th {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            font-size: 13px;
        }
        .signature-box {
            border-top: 1px solid #334155;
            padding-top: 6px;
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
        .thumb-box {
            border: 1px dashed #94a3b8;
            height: 70px;
            width: 90px;
            margin: 0 auto 6px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #94a3b8;
        }
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .contract-paper {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                width: 100% !important;
                padding: 10mm 15mm !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <!-- TOP ACTION TOOLBAR -->
    <div class="container-fluid no-print py-3 bg-white border-bottom shadow-sm sticky-top">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <a href="<?php echo BASE_URL; ?>/admin/land-inventory/brokers" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i> Broker Directory
                </a>
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="fas fa-file-contract me-1"></i> <?php echo htmlspecialchars($refNo); ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print / Save as PDF
                </button>

                <!-- WhatsApp Web Dispatch -->
                <?php
                    $cleanPhone = preg_replace('/\D/', '', $broker['phone'] ?? '');
                    if (strlen($cleanPhone) === 10) {
                        $cleanPhone = '91' . $cleanPhone;
                    }
                    $waSummary = "नमस्ते " . ($broker['name'] ?? '') . " जी, APS Dream Homes Gorakhpur से आपका भूमि दलाली अनुबंध पत्र (Ref: " . $refNo . ") तैयार है। अनुबंधित दर: " . $rateDesc . "।";
                    $waLink = "https://api.whatsapp.com/send?phone=" . $cleanPhone . "&text=" . rawurlencode($waSummary);
                ?>
                <a href="<?php echo $waLink; ?>" target="_blank" class="btn btn-success btn-sm">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                </a>

                <!-- Email Dispatch Form -->
                <form action="<?php echo BASE_URL; ?>/admin/land-inventory/brokers/<?php echo (int)($broker['id'] ?? 0); ?>/send-agreement" method="POST" class="d-inline">
                    <?php echo \App\Helpers\SecurityHelper::csrfField(); ?>
                    <input type="hidden" name="channel" value="email">
                    <?php if ($deal): ?>
                        <input type="hidden" name="deal_id" value="<?php echo (int)$deal['id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-outline-primary btn-sm" <?php echo empty($broker['email']) ? 'disabled title="Email not available"' : ''; ?>>
                        <i class="fas fa-envelope me-1"></i> Email to Broker
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PRINTABLE CONTRACT DOCUMENT -->
    <div class="contract-paper">
        <div class="watermark">APS DREAM HOMES • LEGAL</div>

        <!-- STAMP DUTY / NOTARY ADHESION PLACEHOLDER -->
        <div class="row align-items-center mb-3">
            <div class="col-8">
                <span class="text-uppercase small fw-bold text-muted">Legal Document Identifier: </span>
                <code class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($refNo); ?></code>
            </div>
            <div class="col-4">
                <div class="stamp-box">
                    <span>Non-Judicial / Legal Notary Stamp</span>
                </div>
            </div>
        </div>

        <!-- HEADER -->
        <div class="contract-header text-center">
            <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                <i class="fas fa-building fa-2x text-teal" style="color: #0f766e;"></i>
                <h3 class="mb-0 fw-bold" style="color: #0f766e;"><?php echo htmlspecialchars($company['name'] ?? 'APS DREAM HOMES PVT. LTD.'); ?></h3>
            </div>
            <p class="mb-1 text-muted small">
                CIN: U70109UP2020PTC123456 | Registered Office: <?php echo htmlspecialchars($company['address'] ?? 'Singhariya, Gorakhpur, UP'); ?>
            </p>
            <p class="mb-0 text-muted small">
                Phone: <?php echo htmlspecialchars($company['phone'] ?? '+91-9450000000'); ?> | Email: legal@apsdreamhome.com | Jurisdiction: Gorakhpur (U.P.)
            </p>
            <hr class="my-2" style="border-color: #0f766e; opacity: 0.25;">
            <h5 class="contract-title mt-2 mb-1">LAND BROKER ENGAGEMENT, DUE DILIGENCE & INDEMNITY CONTRACT</h5>
            <h2  class="fs-6 fw-semibold fw-bold text-secondary mb-0">(भूमि दलाली, विधिक दायित्व एवं क्षतिपूर्ति अनुबंध पत्र)</h2>
        </div>

        <!-- PREAMBLE & PARTIES -->
        <div class="mb-3">
            <p class="mb-2">
                This Land Brokerage & Indemnity Contract is executed on this <strong><?php echo date('d'); ?>th</strong> day of 
                <strong><?php echo date('F, Y'); ?></strong> at Gorakhpur, Uttar Pradesh, by and between:
            </p>
            <p class="mb-2">
                <strong>1. FIRST PARTY (THE COMPANY):</strong><br>
                <strong>M/s APS Dream Homes Pvt. Ltd.</strong>, having its Head Office at Singhariya, AIIMS Road, Gorakhpur, Uttar Pradesh - 273008, 
                acting through its Authorized Director/Officer (hereinafter called the <strong>"Company / Purchaser"</strong>).
            </p>
            <p class="mb-2">
                <strong>2. SECOND PARTY (LAND BROKER / DALAL / MEDIATOR):</strong><br>
                <strong>Mr./Ms. <?php echo htmlspecialchars($broker['name'] ?? '—'); ?></strong>, 
                Mobile: <strong><?php echo htmlspecialchars($broker['phone'] ?? '—'); ?></strong>, 
                Email: <strong><?php echo htmlspecialchars($broker['email'] ?: 'N/A'); ?></strong>, 
                PAN: <strong><?php echo htmlspecialchars($broker['pan'] ?: 'Not Provided'); ?></strong>, 
                Aadhaar: <strong><?php echo htmlspecialchars($broker['aadhaar'] ?: 'Not Provided'); ?></strong>, 
                Address: <strong><?php echo htmlspecialchars($broker['address'] ?: 'Gorakhpur Region, UP'); ?></strong> 
                (hereinafter called the <strong>"Broker / Second Party"</strong>).
            </p>
        </div>

        <!-- SCHEDULE OF COMMERCIAL BROKERAGE TERMS -->
        <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">
            <i class="fas fa-hand-holding-usd text-teal me-1" style="color: #0f766e;"></i> SCHEDULE A: COMMERCIAL TERMS & BROKERAGE RATE (दलाली दर)
        </h6>
        <table class="table table-sm table-custom-bordered mb-3">
            <thead class="table-light">
                <tr>
                    <th width="30%">Parameter</th>
                    <th width="70%">Agreed Specifics</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold">Brokerage Model</td>
                    <td>
                        <span class="badge bg-dark"><?php echo strtoupper($broker['brokerage_model'] ?? 'PERCENTAGE'); ?></span>
                        (<?php echo ucfirst($broker['brokerage_model'] ?? 'percentage'); ?> basis)
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold">Agreed Remuneration Rate</td>
                    <td class="text-primary fw-bold fs-6">
                        <?php echo htmlspecialchars($rateDesc); ?>
                    </td>
                </tr>
                <?php if ($deal): ?>
                <tr>
                    <td class="fw-bold">Subject Land Deal</td>
                    <td>
                        Deal Ref: #<?php echo (int)$deal['id']; ?> | Colony: <strong><?php echo htmlspecialchars($deal['colony_name'] ?? 'Township Project'); ?></strong><br>
                        Village: <?php echo htmlspecialchars($deal['village'] ?? '—'); ?> | Farmer: <?php echo htmlspecialchars($deal['land_owner_name'] ?? '—'); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="fw-bold">Specialization / Operational Belt</td>
                    <td><?php echo htmlspecialchars($broker['specialization'] ?: 'Purvanchal Land Acquisition (Gorakhpur / Deoria / Maharajganj / Kushinagar)'); ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Payment Schedule (भुगतान किश्तें)</td>
                    <td>
                        <strong>50%</strong> upon Registered Agreement to Sell (इकरारनामा/बयाना) with the farmer.<br>
                        <strong>50%</strong> upon Final Registry (बैनामा/Sale Deed) & Mutation clearance (दाखिल-खारिज).
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- TERMS, CONDITIONS & FRAUD INDEMNITY CLAUSES -->
        <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">
            <i class="fas fa-balance-scale text-teal me-1" style="color: #0f766e;"></i> SCHEDULE B: LEGAL LIABILITIES & INDEMNITY CLAUSES (विधिक दायित्व एवं क्षतिपूर्ति)
        </h6>
        <div class="clause-box">
            <?php foreach ($clauses as $clause): ?>
                <div class="clause-item">
                    <span class="clause-num"><?php echo htmlspecialchars($clause['clause']); ?>:</span>
                    <?php echo htmlspecialchars($clause['text']); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- SPECIAL FRAUD RECOVERY & CRIMINAL LIABILITY CLAUSE -->
        <div class="alert alert-danger py-2 px-3 my-3" style="font-size: 12.5px; border-left: 4px solid #dc2626;">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> व्यक्तिगत वसूली एवं आपराधिक उत्तरदायित्व खंड (INDEMNITY UNDER CONTRACT ACT):</strong><br>
            द्वितीय पक्ष (दलाल) स्पष्ट रूप से स्वीकार करता है कि यदि उसके द्वारा प्रस्तुत भूमि पर कोई विवाद (जैसे बंधक, बैंक लोन, सीलिंग, वरासत/सगे-संबंधियों का विवाद, नाबालिग का हक, अनुसूचित जाति 143/80 अनापत्ति का अभाव या भूमि का पूर्व-विक्रय) छिपाया जाता है, तो कंपनी द्वारा किसान/खातेदार को दिए गए बयाने अथवा अग्रिम धन (Bayana Amount) की <strong>100% वसूली दलाल से 18% वार्षिक ब्याज</strong> एवं समस्त विधिक खर्चों सहित की जाएगी। इसके अतिरिक्त दलाल पर बी.एन.एस. / भारतीय दंड संहिता की धोखाधड़ी (Fraud & Forgery) धाराओं के अंतर्गत आपराधिक वाद दर्ज किया जाएगा।
        </div>

        <!-- EXECUTION SIGNATURES -->
        <div class="row pt-2">
            <div class="col-4">
                <div class="signature-box">
                    <p class="mb-0 fw-bold">For APS DREAM HOMES PVT. LTD.</p>
                    <p class="text-muted small mb-0">(प्रथम पक्ष - अधिकृत हस्ताक्षरकर्ता)</p>
                    <small class="text-muted">Company Seal & Stamp</small>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="thumb-box">LTI (बायाँ अंगूठा)</div>
                <div class="signature-box" style="margin-top: 10px;">
                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($broker['name'] ?? 'BROKER'); ?></p>
                    <p class="text-muted small mb-0">(द्वितीय पक्ष - दलाल / मध्यस्थ)</p>
                    <small class="text-muted">Aadhaar/PAN Verified</small>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-box">
                    <p class="mb-0 fw-bold">गवाह 1 (Witness 1):</p>
                    <p class="text-muted small mb-0">नाम व पता: ....................................</p>
                    <p class="text-muted small mb-0">मोबा. नं.: ........................................</p>
                </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-4 offset-8">
                <div class="signature-box" style="margin-top: 20px;">
                    <p class="mb-0 fw-bold">गवाह 2 (Witness 2):</p>
                    <p class="text-muted small mb-0">नाम व पता: ....................................</p>
                    <p class="text-muted small mb-0">मोबा. नं.: ........................................</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>

